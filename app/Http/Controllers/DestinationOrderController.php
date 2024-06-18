<?php

namespace App\Http\Controllers;

use App\Http\Requests\DestinationOrderStoreValidation;
use App\Http\Requests\DestinationOrderStoreWithoutPaymentValidation;
use App\Models\Destination;
use App\Models\DestinationImages;
use App\Models\DestinationOrder;
use App\Models\DestinationOrderItems;
use App\Models\DestinationOrderRentals;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DestinationOrderController extends Controller
{
    //
    public function index(Request $request)
    {
        $request->validate([
            'access' => 'required',
            'individual_id' => 'required'
        ]);
        $inviduals_id = DB::connection('second_database')->select('select * from individuals where id = ' . $request['individual_id']);
        if ($inviduals_id) {
            $search = ($request['search'] != '' || $request['search'] != null) ? $request['search'] : null;

            $data = DestinationOrder::where('individuals_id', $request['individual_id'])
                ->when($search, function ($q) use ($search) {
                    $q->where('reference_number', 'ilike', '%' . $search . '%');
                });
                // ->with(['destinationOrderItems' => function ($q) {
                //     $q->select('*', DB::raw('(SELECT price * 1 FROM destination_tour_type_prices WHERE destination_tour_type_prices.id = destination_order_items.destination_tour_type_price_id) as total_price'));
                // }]);

            $destinations = $data->skip($request->skip)
                ->take($request->take)
                ->orderBy('id', 'desc')
                ->get();

            $destinations->each(function ($destination) {
                $destinations = $destination->destinationOrderItems()
                    ->get()
                    ->groupBy('destination_id')
                    ->map(function ($group) {
                        $destinationId = $group->pluck('destination_id')->first();
                        $destinationModel = Destination::with('destinationImages')->find($destinationId);
                        $group->each(function ($item) {
                            $item->modified_price = $item->destinationTourTypePrice->price ?? 0;
                        });
                        $totalModifiedPrice = $group->sum('modified_price');

                        return [
                            'destination_id' => $destinationId,
                            'destination' => $destinationModel,
                            'destinationOrderItemsCount' => $group->count(),
                            'totalModifiedPrice' => $totalModifiedPrice,
                        ];
                    })
                    ->values()
                    ->all();
                $destination->destinations = $destinations;
            });

            $message = ($destinations->count() == 0) ? "No Results Found" : "Results Found";
            return response([
                'data'      => $destinations,
                'details' => [
                    'from' => $request->skip + 1,
                    'to' => min(($request->skip + $request->take), $destinations->count()),
                    'total' => $destinations->count()
                ],
                'message'   => $message
            ]);
        } else {
            return response(['message' => 'Individual not found'], 404);
        }
    }

    public function store(DestinationOrderStoreValidation $request)
    {
        $request->validated();
        $inviduals_id = DB::connection('second_database')->select('select * from individuals where id = ?', [$request->individual_id]);

        if (!$inviduals_id) {
            return response(['message' => 'Individual not found'], 404);
        } else {
            $order = DestinationOrder::create([
                'individuals_id' => $request->individual_id,
                'departure_time' => $request->departure_time,
                'payment_method' => 'LandBank',
                'order_type'     => $request->order_type,
                'status' => 'pending'
            ]);
            if($request->rentals){
                $order->destinationOrderRentals()->createMany($request->rentals);
            }
            $transaction_number = str_pad($order->id, 10, '0', STR_PAD_LEFT);
            $order->update(['transaction_number' => $transaction_number]);
            $data = [];
            foreach ($request->destinations as $key) {
                foreach ($key['tour_type_prices'] as $value) {
                    for ($i = 0; $i < $value['quantity']; $i++) {
                        $payload = [
                            'orders_id' => $order->id,
                            'destination_id' => $key['destination_id'],
                            'destination_tour_type_id' => $key['destination_tour_type_id'],
                            'destination_tour_type_price_id' => $value['destination_tour_type_price_id'],
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ];
                        $data[] = $payload;
                    }
                }
            }

            DestinationOrderItems::insert($data);
            try {
                $payload = [
                    'amount' => $request->amount,
                    'transaction_number' => $transaction_number,
                    'name' => $inviduals_id[0]->first_name.' '.$inviduals_id[0]->last_name,
                    'email' =>  $inviduals_id[0]->email ?? 'mendozajaymar28@gmail.com',
                    'type' => 'Tourist Site Fee'
                ];
                $url = (new \App\Http\Controllers\PaymentMethodController)->payment($payload);
            } catch (\Throwable $th) {
                return response([
                    'message' => 'Error! please contact administrator'
                ]);
            }
            return response([
                'data' => ['url' => $url, 'id' => $transaction_number],
                'message' => 'Order has been created'
            ]);
        }
    }
    public function update(Request $request)
    {
        $request->validate([
            'transaction_number' => 'required',
            'reference_number'  => 'required',
            'status'            => 'required',
            'access'            => 'required'
        ]);

        DestinationOrder::where('transaction_number', $request->transaction_number)->update([
            'reference_number' => $request->reference_number,
            'status' => $request->status
        ]);
        return response([
            'message' => 'Order success.'
        ]);
    }
    public function adminIndex(Request $request)
    {
        $request->validate([
            'auth' => 'string|required'
        ]);

        $auth = (new ActionAuthorizationController)->authChecker($request);
        $check_auth = (new ActionAuthorizationController)->checkAuth($auth);

        if ($check_auth) {
            $search = ($request['search'] != '' || $request['search'] != null) ? $request['search'] : null;

            $data = DestinationOrder::with('destinationOrderItems.destination.destinationImages',  'destinationOrderItems.destinationTourTypePrice.touristType', 'destinationOrderItems.destinationTourType.tourType', 'cqrcode');

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
    public function findOrder(Request $request)
    {
        $request->validate([
            'search' => 'required',
            'destination_id' => 'required|exists:destinations,id'
        ]);
        $individual_id = DB::connection('second_database')->select('select * from individuals where qr_code = ?', [$request->search]);
        if ($individual_id) {
            $dates = DestinationOrder::where('individuals_id', $individual_id[0]->id)
                ->withCount(['destinationOrderItems' => function ($q) use ($request) {
                    $q->where('used', false)
                        ->where('destination_id', $request->destination_id);
                }])
                ->whereHas('destinationOrderItems', function ($q) use ($request) {
                    $q->where('destination_id', $request->destination_id)
                        ->where('used', false);
                })
                ->where('check-in', false)
                ->orderBy('id', 'desc')
                ->get(['created_at', 'id']);
            return response(['dates' => $dates, 'individual' => $individual_id ? $individual_id[0] : null], 200);
        } else {
            return response(['message' => 'No results found.'], 404);
        }
    }
    public function getTicketDetails(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:destination_orders,id',
            'destination_id' => 'required|exists:destinations,id'
        ]);

        $data = Destination::whereHas('destinationOrderItems', function ($q) use ($request) {
            $q->where('orders_id', $request->order_id);
        })
            ->where('id', $request->destination_id)
            ->with(['destinationTourType.destinationTourTypePrice' => function ($q) use ($request) {
                $q->whereHas('destinationTourTypePrice', function ($q) use ($request) {
                    $q->where('orders_id', $request->order_id)
                        ->where('used', false);
                })
                    ->withCount(['destinationTourTypePrice' => function ($q) use ($request) {
                        $q->where('orders_id', $request->order_id)
                            ->where('used', false);
                    }])
                    ->with('touristType');
            }])
            ->first();
        return response([
            'data' => $data
        ]);
    }
    public function getUserDetailsQr(Request $request)
    {
        $request->validate([
            'qr_code' => 'required',
            'order_id' => 'required|exists:destination_orders,id',
            'destination_id' => 'required|exists:destinations,id'
        ]);

        $individual_id = DB::connection('second_database')->select('select * from individuals where qr_code = ?', [$request->qr_code]);
        if ($individual_id) {
            $destination_order_items = DestinationOrderItems::where('orders_id', $request->order_id)
                ->where('destination_id', $request->destination_id)
                ->where('qr_code', $request->qr_code)
                ->first();
            if ($destination_order_items) {
                return response(['message' => 'This qr code is already served.'], 422);
            } else {
                $payload = [
                    'first_name' => $individual_id[0]->first_name,
                    'last_name' => $individual_id[0]->last_name
                ];
                return response(['data' => $payload], 200);
            }
        } else {
            return response(['message' => 'Error! No results found'], 404);
        }
    }

    public function storeWithoutPayment(DestinationOrderStoreWithoutPaymentValidation $request)
    {

        $request->validated();
        $inviduals_id = DB::connection('second_database')->select('select * from individuals where qr_code = ?', [$request->qr_code]);


        if (!$inviduals_id) {
            return response(['message' => 'Individual not found'], 404);
        } else {
            $order = DestinationOrder::create([
                'individuals_id' => $inviduals_id[0]->id,
                'payment_method' => $request->payment_method,
                'departure_time' => $request->departure_time,
                'amount' => $request->amount,
                'order_type' => $request->order_type,
                'kiosk_id' => $request->kiosk_id,
                'or_number' => ($request->or_number) ? $request->or_number : null,
            ]);
            if($request->rentals){
                $order->destinationOrderRentals()->createMany($request->rentals);
            }
            $transaction_number = str_pad($order->id, 10, '0', STR_PAD_LEFT);
            $order->update(['transaction_number' => $transaction_number]);
            $data = [];
            foreach ($request->destinations as $key) {
                foreach ($key['tour_type_prices'] as $value) {
                    for ($i = 0; $i < $value['quantity']; $i++) {
                        $payload = [
                            'orders_id' => $order->id,
                            'destination_id' => $key['destination_id'],
                            'destination_tour_type_id' => $key['destination_tour_type_id'],
                            'destination_tour_type_price_id' => $value['destination_tour_type_price_id'],
                        ];
                        $data[] = $payload;
                    }
                }
            }

            DestinationOrderItems::insert($data);
        }
        return response(['message' => 'Order Success', 'name' => $inviduals_id[0]], 200);
    }
}
