<?php

namespace App\Http\Controllers;

use App\Http\Requests\DestinationStoreValidation;
use App\Http\Requests\DestinationUpdateValidation;
use App\Http\Requests\SalesInformationStoreValidation;
use App\Models\Destination;
use App\Models\DestinationCategory;
use App\Models\DestinationImages;
use App\Models\DestinationOrderItems;
use App\Models\DestinationRentals;
use App\Models\DestinationTourType;
use App\Models\DestinationTourTypePrices;
use App\Models\Rules;
use App\Models\TouristType;
use App\Models\TourTypes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Whoops\Run;

class DestinationController extends Controller
{
    public function index(Request $request)
    {
        $search = ($request['search'] != '' || $request['search'] != null) ? $request['search'] : null;
        $status = ($request['status'] != '' || $request['status'] != null) ? $request['status'] : null;
        $date =   ($request['date'] != '' || $request['date'] != null) ? $request['date'] : null;

        $data = Destination::when(
            $search,
            function ($q) use ($search) {
                $q->where('name', 'ilike', '%' . $search . '%');
            }
        )->when($status, function ($q) use ($status) {
            $q->where('status', $status);
        })
            ->where('draft', true)
            ->with('destinationPrice', 'destinationCategory', 'destinationImages', 'destinationRules', 'destinationTourType.destinationTourTypePrice.touristType', 'destinationTourType.tourType', 'kiosk', 'destinationRentals');


        $details = [
            'from' =>   $request->skip + 1,
            'to'   =>   min(($request->skip + $request->take), $data->count()),
            'total' =>   $data->count()
        ];
        $message = ($data->count() == 0) ? "No Results Found" : "Results Found";
        return response([
            'data'      => $data->skip($request->skip)
                ->take($request->take)
                ->orderBy('id', 'desc')
                ->get(),
            'details'   => $details,
            'message'   => $message
        ]);
    }
    public function store(Request $request)
    {
        $request->validate([
            'auth' => 'string|required'
        ]);

        $auth = (new ActionAuthorizationController)->authChecker($request);
        $check_auth = (new ActionAuthorizationController)->checkAuth($auth);

        if ($check_auth) {
            //$request->validate();
            $destination = Destination::create();

            return response([
                'id' => $destination->id,
            ]);
        } else {
            return response(['message' => 'Unauthorized Access'], 401);
        }
    }

    public function update(DestinationUpdateValidation $request, $id)
    {
        $request->validated();

        $auth = (new ActionAuthorizationController)->authChecker($request);
        $check_auth = (new ActionAuthorizationController)->checkAuth($auth);

        if ($check_auth) {
            if (Destination::find($id)) {

                $destination = Destination::updateOrCreate(
                    ['id' => $id],
                    [
                        'destination_category_id'       => $request['destination_category_id'],
                        'name'                          => $request['name'],
                        'description'                   => $request['description'],
                        'address'                       => $request['address'],
                        'draft'                         => true,
                        'status'                        => $request['status']
                    ]
                );

                $newRuleIds = [];
                foreach ($request['rules'] as $rule) {
                    $destination->destinationRules()->updateOrCreate(
                        ['rule_id' => $rule['rule_id'], 'destination_id' => $destination->id],
                        ['rule_id' => $rule['rule_id']]
                    );
                    $newRuleIds[] = $rule['rule_id'];
                }
                $destination->destinationRules()->whereNotIn('rule_id', $newRuleIds)->delete();

                $newRentalIds = [];
                foreach ($request['rentals'] as $rental) {
                    $destination->destinationRentals()->updateOrCreate(
                        ['rental_id' => $rental['rental_id'], 'destination_id' => $destination->id],
                        [
                            'rental_id' => $rental['rental_id'],
                            'destination_id' => $rental['destination_id']
                        ]
                    );
                    $newRentalIds[] = $rental['rental_id'];
                }
                $destination->destinationRentals()->whereNotIn('rental_id', $newRentalIds)->delete();

                return response([
                    'message' => 'Destination Updated Successfully'
                ]);
            } else {
                return response([
                    'message' => 'Invalid Destination Id'
                ]);
            }
        } else {
            return response(['message' => 'Unauthorized Access'], 401);
        }
    }

    public function delete($id)
    {


        $destination = Destination::find($id);
        if (!$destination) {
            return response([
                'message' => 'Invalid destination id'
            ], 400);
        } else {
            $destination->destinationPrice()->delete();
            $destination->destinationRules()->delete();
            $destination->destinationImages()->delete();

            $destination->delete();
            return response([
                'message' => 'Data deleted.'
            ]);
        }
    }

    public function get($id)
    {

        $destination = Destination::where('id', $id)->with(
            'destinationTourType.destinationTourTypePrice',
            'destinationCategory',
            'destinationImages',
            'destinationRules',
            'destinationTourType.destinationTourTypePrice.touristType',
            'destinationTourType.tourType',
            'kiosk',
            'destinationRentals.rentals'
        )
            ->first();
        if (!$destination) {
            return response([
                'message' => 'Invalid destination id'
            ], 400);
        } else {
            return response([
                'data' => $destination
            ], 200);
        }
    }
    public function getTourTypes(Request $request)
    {
        $request->validate([
            'departure_time' => 'required|date',
            'destination_id' => 'required|exists:destinations,id'
        ]);

        $data = DestinationTourType::where('destination_id', $request->destination_id)
            ->with(['destinationTourTypePrice' => function ($q) use ($request) {
                $q->withCount(['destinationTourTypePrice as current_counting' => function ($q) use ($request) {
                    $q->whereHas('order', function ($q) use ($request) {
                        $q->whereDate('departure_time', $request->departure_time);
                    });
                }])
                    ->with('touristType');
            }])
            ->get();
        return response([
            'data' => $data
        ]);
    }

    public function getRentals(Request $request)
    {
        $request->validate([
            'departure_time' => 'required|date',
            'destination_id' => 'required|exists:destinations,id'
        ]);

        $data = DestinationRentals::where('destination_id', $request->destination_id)
            ->with(['rentals' => function ($q) use ($request) {
                $q->withCount(['order as current_counting' => function ($q) use ($request) {
                    $q->whereHas('order', function ($q) use ($request) {
                        $q->whereDate('departure_time', $request->departure_time);
                    });
                }]);
            }])->get();

        if (!$data) {
            return response([
                'message' => 'Invalid destination id'
            ], 400);
        } else if ($data->count() == 0) {
            return response([
                'message' => 'No Rentals Found'
            ], 400);
        }

        return response([
            'data' => $data
        ]);
    }


    public function destinationImages(Request $request, $id)
    {
        $payload = $request->validate([
            'file' => 'required|image|mimes:jpeg,png',
        ]);


        if (isset($id)) {

            $file = $payload['file'];

            $news = Destination::find($id);
            if ($news != null) {
                $filename = uniqid() . '.' . $file->getClientOriginalExtension();
                $handle = fopen($file->getPathname(), 'rb');
                $path = 'destination/' . $id . '/' . $filename;
                $path_exist = 'destination/' . $id . '/';
                if (!File::exists(storage_path('app/public/' . $path_exist))) {
                    File::makeDirectory(storage_path('app/public/' . $path_exist), 0755, true);
                }
                $storageFile = fopen(storage_path('app/public/' . $path), 'w');
                while (!feof($handle)) {
                    $chunk = fread($handle, 4096);
                    if (connection_aborted()) {
                        fclose($handle);
                        fclose($storageFile);
                        Storage::disk('local')->delete($path);
                        return response()->json(['message' => 'File upload cancelled']);
                    }
                    fwrite($storageFile, $chunk);
                }
                fclose($handle);
                fclose($storageFile);
                $payload = [
                    'destination_id'    => $id,
                    'image_name'     => $file->getClientOriginalName(),
                    'image_path'     => $path
                ];
                DestinationImages::create($payload);
                return response()->json(['message' => 'File uploaded successfully']);
            } else {
                return response(['message' => 'Invalid id'], 400);
            }
        } else {
            return response(['message' => 'Invalid id'], 400);
        }
    }

    public function destinationImagesDelete($id)
    {


        $destination_images = DestinationImages::find($id);
        if (!$destination_images) {
            return response([
                'message' => 'Invalid destination images id'
            ], 400);
        } else {

            $destination_images->delete();

            return response([
                'message' => 'Data and image deleted.'
            ]);
        }
    }


    public function showDestinationCategories()
    {
        return response([
            'data' => DestinationCategory::all()
        ]);
    }

    public function storeSalesInformation(SalesInformationStoreValidation $request, $id)
    {
        $request->validated();

        $auth = (new ActionAuthorizationController)->authChecker($request);
        $check_auth = (new ActionAuthorizationController)->checkAuth($auth);

        if ($check_auth) {
            $destination = Destination::find($id);
            if ($destination != null) {
                $tourTypePrice = $destination->destinationTourType->updateOrCreate([
                    ['tour_type_id' => $request['tour_type_id']],
                    [
                        'tour_type_id' => $request['tour_type_id'],
                        'limit' => $request['limit'],
                        'time_in' => $request['time_in'],
                        'time_out' => $request['time_out']
                    ]
                ]);


                foreach ($request['price'] as $price) {
                    $tourTypePrice->destinationTourTypePrice()->updateOrCreate([
                        ['tourist_type_id' => $price['tourist_type_id']],
                        [
                            'tourist_type_id' => $price['tourist_type_id'],
                            'price' => $price['price']
                        ]
                    ]);
                }
                return response([
                    'message' => 'Sales Information Saved'
                ]);
            } else {
                return response(['message' => 'Invalid id'], 400);
            }
        } else {
            return response(['message' => 'Unauthorized Access'], 401);
        }
    }
    public function destinationTourType(Request $request)
    {
        $request->validate([
            'destination_id'    => 'required|exists:destinations,id',
            'auth'              => 'string|required'
        ]);

        $auth = (new ActionAuthorizationController)->authChecker($request);
        $check_auth = (new ActionAuthorizationController)->checkAuth($auth);

        if ($check_auth) {
            $data = DestinationTourType::where('destination_id', $request->destination_id)
                                        ->with('tourType','destinationTourTypePrice.touristType')
                                        ->has('destinationTourTypePrice');

            $details = [
                'from' =>   $request->skip + 1,
                'to'   =>   min(($request->skip + $request->take), $data->count()),
                'total' =>   $data->count()
            ];
            $message = ($data->count() == 0) ? "No Results Found" : "Results Found";
            return response([
                'data'      => $data->skip($request->skip)
                    ->take($request->take)
                    ->orderBy('id', 'desc')
                    ->get(),
                'details'   => $details,
                'message'   => $message
            ]);
        } else {
            return response(['message' => 'Unauthorized Access'], 401);
        }
    }
    public function destinationTourTypeStore(Request $request)
    {
        $request->validate([
            'destination_id'    => 'required|exists:destinations,id',
            'tour_type'         => 'required|exists:tour_types,id',
            'tourist_type_id'   => 'required|exists:tourist_types,id',
            'price'             => 'required|numeric',
            'time_in'           => 'required',
            'time_out'          => 'required',
            'auth'              => 'string|required',
            'limit'             => 'required|integer'
        ]);

        $auth = (new ActionAuthorizationController)->authChecker($request);
        $check_auth = (new ActionAuthorizationController)->checkAuth($auth);

        if ($check_auth) {
            $destination_tour_type =  DestinationTourType::updateOrCreate(
                [
                    'destination_id'    => $request->destination_id,
                    'tour_type_id'      => $request->tour_type
                ],
                $request->toArray()
            );
            $destination_tour_type->destinationTourTypePrice()->updateOrCreate(
                [
                    'tourist_type_id'   => $request->tourist_type_id,
                ],
                $request->toArray()

            );

            return response([
                'message' => 'Data saved.'
            ]);
        } else {
            return response(['message' => 'Unauthorized Access'], 401);
        }
    }

    public function destinationTourTypeDelete($id)
    {


        $destination_tour_type = DestinationTourType::find($id);
        if (!$destination_tour_type) {
            return response([
                'message' => 'Invalid destination tour type id'
            ], 400);
        } else {
            $destination_tour_type->delete();
            return response([
                'message' => 'Data deleted.'
            ]);
        }
    }

    public function destinationTouristTypeIndex(Request $request)
    {
        $search = ($request['search'] != '' || $request['search'] != null) ? $request['search'] : null;

        $data = TouristType::when(
            $search,
            function ($q) use ($search) {
                $q->where('name', 'ilike', '%' . $search . '%');
            }
        );

        $details = [
            'from' =>   $request->skip + 1,
            'to'   =>   min(($request->skip + $request->take), $data->count()),
            'total' =>   $data->count()
        ];

        $message = ($data->count() == 0) ? "No Results Found" : "Results Found";

        return response([
            'data'      => $data->skip($request->skip)
                ->take($request->take)
                ->orderBy('id', 'desc')
                ->get(),
            'details'   => $details,
            'message'   => $message
        ]);
    }


    public function destinationTouristTypeStore(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'auth' => 'string|required'
        ]);

        $auth = (new ActionAuthorizationController)->authChecker($request);
        $check_auth = (new ActionAuthorizationController)->checkAuth($auth);

        if ($check_auth) {
            TouristType::create([
                'name' => $request->name
            ]);
            return response([
                'message' => 'Data saved.'
            ]);
        } else {
            return response(['message' => 'Aunthurozied Access'], 401);
        }
    }

    public function destinationTouristTypeGet(Request $request, $id)
    {


        $data = TouristType::find($id);

        if (!$data) {
            return response([
                'message' => 'Invalid destination tourist type id'
            ], 400);
        }

        return response([
            'data' => $data
        ]);
    }

    public function destinationTouristTypeUpdate(Request $request, $id)
    {
        $request->validate([
            'auth' => 'string|required',
            'name' => 'required'
        ]);

        $auth = (new ActionAuthorizationController)->authChecker($request);
        $check_auth = (new ActionAuthorizationController)->checkAuth($auth);

        if ($check_auth) {
            $tourist_type = TouristType::find($id);
            if (!$tourist_type) {
                return response([
                    'message' => 'Invalid destination tourist type id'
                ], 400);
            } else {
                $tourist_type->updateOrCreate(
                    ['id' => $id],
                    $request->toArray()
                );
                return response([
                    'message' => 'Data updated.'
                ]);
            }
        } else {
            return response(['message' => 'Unauthorized Access'], 401);
        }
    }

    public function touristTypeDelete($id)
    {

        $tourist_type = TouristType::find($id);
        if (!$tourist_type) {
            return response([
                'message' => 'Invalid destination tourist type id'
            ], 400);
        } else {
            $tourist_type->delete();
            return response([
                'message' => 'Data deleted.'
            ]);
        }
    }



    public function destinationTouristTypeDelete(Request $request, $id)
    {

        $destination_tourist_type = DestinationTourTypePrices::find($id);
        if (!$destination_tourist_type) {
            return response([
                'message' => 'Invalid destination tourist type id'
            ], 400);
        } else {
            $destination_tourist_type->delete();
            return response([
                'message' => 'Data deleted.'
            ]);
        }
    }

    public function showRules()
    {
        $rules = Rules::all();

        return response([
            'data' => $rules
        ]);
    }

    public function checkIn(Request $request)
    {
        $validate = $request->validate([
            'auth' => 'required',
            'checkins' => 'required|array',
            'checkins.*.id' => 'required|exists:destination_tour_type_prices,id',
            'checkins.*.order_id' => 'required|exists:destination_orders,id',
            'checkins.*.checkin' => 'required|array',
            'checkins.*.checkin.*.destination_id' => 'required|exists:destinations,id',
            'checkins.*.checkin.*.qr_code' => 'required|string'
        ]);

        $auth = (new ActionAuthorizationController)->authChecker($request);

        if ($validate['auth'] === $auth) {
            $checkIns = $request->checkins;

            foreach ($checkIns as $checkInItem) {
                $tourTypePrice_id = $checkInItem['id'];
                $destination_order_id = $checkInItem['order_id'];

                foreach ($checkInItem['checkin'] as $checkIn) {
                    $destination_id = $checkIn['destination_id'];
                    $qr_code = $checkIn['qr_code'];

                    $orderItem = DestinationOrderItems::where('destination_tour_type_price_id', $tourTypePrice_id)
                        ->where('destination_id', $destination_id)
                        ->where('orders_id', $destination_order_id)
                        ->first();

                    if ($orderItem) {
                        $orderItem->update([
                            'used' => true,
                            'qr_code' => $qr_code
                        ]);
                    } else {
                        return response(['message' => 'Insufficient Order'], 400);
                    }
                }
            }
            return response(['message' => 'Check In Successful']);
        } else {
            return response(['message' => 'Unauthorized'], 401);
        }
    }

    public function getIndividualsDetail(Request $request)
    {
        $request->validate([
            'url'  => 'required'
        ]);

        try {
            $parsedUrl = parse_url($request->url);
            //dd($parsedUrl);
            $path = $parsedUrl['path'];


            $hash = baseName($path);

            if (!$hash) {
                return response(['message' => 'Invalid URL'], 400);
            }

            $individuals = DB::connection('second_database')->select('select * from individuals where hash = ?', [$hash]);


            if (!$individuals) {
                return response(['message' => 'No Data Found'], 400);
            }
        } catch (\Exception $e) {
            return response(['message' => 'Invalid URL'], 400);
        }

        $result = [
            'name' => $individuals[0]->first_name . ' ' . $individuals[0]->last_name,
            'qr_code'  => $individuals[0]->qr_code,
        ];


        return response(['data' => $result], 200);
    }
}
