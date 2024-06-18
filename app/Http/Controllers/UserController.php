<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserStoreValidation;
use App\Http\Requests\UserUpdateValidation;
use App\Models\DestinationOrder;
use App\Models\ProductOrder;
use App\Models\Role;
use App\Models\User;
use App\Models\UserMerchant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $validate = $request->validate([
            'auth' => 'string|required'
        ]);

        $auth = (new ActionAuthorizationController)->authChecker($request);
        $check_auth = (new ActionAuthorizationController)->checkAuth($auth);

        if ($check_auth) {

            $search = ($request['search'] != '' || $request['search'] != null) ? $request['search'] : null;
            $role_id = ($request['role_id'] != '' || $request['role_id'] != null) ? $request['role_id'] : null;

            $data = User::when($search, function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $terms = explode(' ', $search);

                    $q->where(function ($q) use ($terms) {
                        foreach ($terms as $term) {
                            $q->where('first_name', 'ilike', '%' . $term . '%')
                                ->orWhere('last_name', 'ilike', '%' . $term . '%');
                        }
                    })
                        ->orWhere('qr_code', 'ilike', '%' . $search . '%');
                });
            })->when($role_id, function ($q) use ($role_id) {
                $q->whereHas('userRole', function ($q) use ($role_id) {
                    $q->where('role_id', $role_id);
                });
            })->with('userRole.role', 'kioskCashier.kiosk.destination', 'products');



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

    public function showDestinationOrders(Request $request, $id)
    {
        $request->validate([
            'auth' => 'string|required'
        ]);

        $auth = (new ActionAuthorizationController)->authChecker($request);
        $check_auth = (new ActionAuthorizationController)->checkAuth($auth);

        if ($check_auth) {
            $search = ($request['search'] != '' || $request['search'] != null) ? $request['search'] : null;

            $data = DestinationOrder::where('individuals_id', $id)
                ->when($search, function ($q) use ($search) {
                    $q->where('destination_id', 'ilike', '%' . $search . '%');
                })->with('destinationOrderItems');

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

    public function showProductOrders(Request $request, $id)
    {

        $request->validate([
            'auth' => 'string|required'
        ]);

        $auth = (new ActionAuthorizationController)->authChecker($request);
        $check_auth = (new ActionAuthorizationController)->checkAuth($auth);

        if ($check_auth) {
            $search = ($request['search'] != '' || $request['search'] != null) ? $request['search'] : null;

            $data = ProductOrder::where('individuals_id', $id)
                ->when($search, function ($q) use ($search) {
                    $q->where('product_id', 'ilike', '%' . $search . '%');
                })->with('productOrderItems');

            $details = [
                'from' => $request->skip + 1,
                'to' => min($request->skip + $request->take, $data->count()),
                'total' => $data->count()
            ];

            $message = ($data->count() == 0) ? "No Results Found" : "Results Found";
            return response()->json([
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

    public function store(UserStoreValidation $request)
    {

        $request->validated();

        $auth = (new ActionAuthorizationController)->authChecker($request);
        $check_auth = (new ActionAuthorizationController)->checkAuth($auth);

        if ($check_auth) {
            $qr_code = DB::connection('second_database')->select('select * from individuals where qr_code = ?', [$request->qr_code]);

            if (!$qr_code) {
                return response(['message' => 'QR Code not found'], 404);
            }

            $user = User::create([
                'first_name' => $request['first_name'],
                'last_name' => $request['last_name'],
                'middle_name' => $request['middle_name'],
                'suffix'      => $request['suffix'],
                'email' => $request['email'],
                'qr_code' => $request['qr_code'],
                'password' => bcrypt($request['password']),
            ]);

            $user->userRole()->create([
                'role_id' => $request['role']
            ]);

            if ($request['role'] == 2) {
                $user->kioskCashier()->create([
                    'kiosk_id' => $request['kiosk_id'],
                ]);
            } elseif ($request['role'] == 3) {
                $user->merchant()->create([
                    'business_name' => $request['business_name'],
                    'tax_number' => $request['tax_number'],
                    'business_address' => $request['business_address'],
                    'contact_number' => $request['contact_number'],
                ]);
            }

            return response([
                'message' => 'User Created.',
                'access' => $user->userRole['role_id']
            ]);
        } else {
            return response(['message' => 'Unauthorized Access'], 401);
        }
    }

    public function update(UserUpdateValidation $request, $id)
    {
        $request->validated();
        $auth = (new ActionAuthorizationController)->authChecker($request);
        $check_auth = (new ActionAuthorizationController)->checkAuth($auth);

        if ($check_auth) {
            $qr_code = DB::connection('second_database')->select('select * from individuals where qr_code = ?', [$request->qr_code]);

            if (!$qr_code) {
                return response(['message' => 'QR Code not found'], 404);
            }

            if (User::find($id)) {

                $user = User::updateOrCreate(
                    ['id' => $id],
                    [
                        'first_name' => $request['first_name'],
                        'last_name'  => $request['last_name'],
                        'middle_name'  => $request['middle_name'],
                        'suffix'     => $request['suffix'],
                        'email'      => $request['email'],
                        'qr_code'   => $request['qr_code'],
                    ]
                );

                if (!empty($request['password'])) {
                    $user->update(['password' => bcrypt($request['password'])]);
                }



                $user->userRole()->updateOrCreate(
                    ['user_id' => $id],
                    ['role_id' => $request['role']]
                );


                $kiosk_id = ($request['kiosk_id'] != '' || $request['kiosk_id'] != null) ? $request['kiosk_id'] : null;
                $businesss_name = ($request['business_name'] != '' || $request['business_name'] != null) ? $request['business_name'] : null;

                if ($kiosk_id != null) {
                    $user->kioskCashier()->updateOrCreate(
                        ['user_id' => $id],
                        ['kiosk_id' => $request['kiosk_id']]
                    );
                }

                if ($businesss_name != null) {
                    $user->merchant()->updateOrCreate(
                        ['user_id' => $id],
                        [
                            'business_name' => $request['business_name'],
                            'tax_number' => $request['tax_number'],
                            'business_address' => $request['business_address'],
                            'contact_number' => $request['contact_number'],
                        ]
                    );
                }

                return response(['message' => 'User Updated']);
            } else {
                return response(['message' => 'Invalid User Id'], 404);
            }
        } else {
            return response(['message' => 'Unauthorized Access'], 401);
        }
    }

    public function merchantImage(Request $request, $id)
    {
        $payload = $request->validate([
            'file' => 'required|image|mimes:jpeg,png',
            'auth'  => 'string|required'
        ]);

        $auth = (new ActionAuthorizationController)->authChecker($request);
        $check_auth = (new ActionAuthorizationController)->checkAuth($auth);

        if ($check_auth) {
            if (isset($id)) {

                $file = $payload['file'];

                $user = User::find($id);
                if ($user != null) {
                    $filename = uniqid() . '.' . $file->getClientOriginalExtension();
                    $handle = fopen($file->getPathname(), 'rb');
                    $path = 'merchant/' . $id . '/' . $filename;
                    $path_exist = 'merchant/' . $id . '/';
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
                    $user->merchant()->update(['store_image_path' => $path]);
                    return response()->json(['message' => 'File uploaded successfully']);
                } else {
                    return response(['message' => 'Invalid id'], 400);
                }
            } else {
                return response(['message' => 'Invalid id'], 400);
            }
        } else {
            return response(['message' => 'Unauthorized Access'], 401);
        }
    }

    public function deleteMerchantImage(Request $request, $id)
    {

        if (isset($id)) {

            $user = User::find($id);
            if ($user != null) {
                $path =  $user->merchant->store_image_path;
                if ($path) {
                    Storage::disk('local')->delete($path);
                    $user->merchant()->update(['store_image_path' => null]);
                    return response()->json(['message' => 'File deleted successfully']);
                } else {
                    return response()->json(['message' => 'File not found'], 404);
                }
            } else {
                return response(['message' => 'Invalid id'], 400);
            }
        } else {
            return response(['message' => 'Invalid id'], 400);
        }
    }

    public function roleIndex(Request $request)
    {
        $search = ($request['search'] != '' || $request['search'] != null) ? $request['search'] : null;

        $data = Role::when($search, function ($q) use ($search) {
            $q->where('name', 'ilike', '%' . $search . '%');
        });

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

    public function delete($id)
    {
        $user = User::find($id);
        if ($user) {
            $user->delete();
            return response(['message' => 'User Deleted']);
        } else {
            return response(['message' => 'User not found'], 404);
        }
    }

    public function get(Request $request, $id)
    {

        $user = User::with('userRole.role', 'kioskCashier.kiosk.destination', 'products')->find($id);
        if (!$user) {
            return response(['message' => 'User not found'], 404);
        }
        return response(['data' => $user]);
    }
}
