<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductOrderStoreValidation;
use App\Models\Product;
use App\Models\ProductOrder;
use App\Models\ProductOrderItems;
use App\Models\Products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductOrderController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'access' => 'required',
            'individuals_id' => 'required'
        ]);
        $inviduals_id = DB::connection('second_database')->select('select * from individuals where id = ' . $request['individuals_id']);
        if ($inviduals_id) {
            $search = ($request['search'] != '' || $request['search'] != null) ? $request['search'] : null;

            $data = ProductOrder::when($search, function ($q) use ($search) {
                $q->where('transaction_number', 'ilike', '%' . $search . '%');
            })->when($request->individuals_id, function ($q) use ($request) {
                $q->where('individuals_id', $request->individuals_id);
            })
                ->with('productOrderItems.product.productImages', 'productOrderItems.productPrice')
                ->where(function ($q) {
                    $q->where('status', 'pending_payment')
                        ->orWhere('status', 'completed');
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
        } else {
            return response(['message' => 'Individual not found'], 404);
        }
    }
    public function store(ProductOrderStoreValidation $request)
    {
        $request->validated();
        $inviduals_id = DB::connection('second_database')->select('select * from individuals where id = ' . $request->individuals_id);
        if ($inviduals_id) {
            $product_order = ProductOrder::create([
                'individuals_id'    => $request['individuals_id'],
                'payment_method'    => 'LandBank'
            ]);
            $request['transaction_number']  = str_pad($product_order->id, 10, '0', STR_PAD_LEFT);
            $product_order->update(['transaction_number' => $request['transaction_number'], 'hash' =>hash('sha256',$request['transaction_number'].$request['individuals_id'])]);
            $product_order->productOrderItems()->createMany($request['order_items']);

            try {
                // $payload = [
                //     'transaction_num' => $request['transaction_number'],
                //     'amount' => $request->amount,
                //     'email' => $inviduals_id[0]->email,
                //     'proc_id' =>  $request->payment_method,
                //     'type' => 'merchandise'
                // ];
                $payload = [
                    'amount' => $request->amount,
                    'transaction_number' => $request['transaction_number'],
                    'name' => $inviduals_id[0]->first_name.' '.$inviduals_id[0]->last_name,
                    'email' =>  $inviduals_id[0]->email ?? 'mendozajaymar28@gmail.com',
                    'type' => 'Merchandise Fee'
                ];
                // $url = (new \App\Http\Controllers\PaymentMethodController)->paymentApi($payload);
                $url = (new \App\Http\Controllers\PaymentMethodController)->payment($payload);
            } catch (\Throwable $th) {
                return response([
                    'message' => 'Error! please contact administrator'
                ]);
            }
            return response([
                'data' => ['url' => $url, 'id' => $request['transaction_number']],
                'message' => 'Order has been created'
            ]);
        } else {
            return response(['message' => 'Individual not found'], 404);
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
        

        ProductOrder::where('transaction_number', $request->transaction_number)
            ->where('hash', hash('sha256', $request->transaction_number))
            ->update([
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
            'auth'  => 'string|required'
        ]);

        $auth = (new ActionAuthorizationController)->authChecker($request);
        $check_auth = (new ActionAuthorizationController)->checkAuth($auth);

        if ($check_auth) {
            $search = ($request['search'] != '' || $request['search'] != null) ? $request['search'] : null;

            $data = ProductOrder::when($search, function ($q) use ($search) {
                $q->where('transaction_number', 'ilike', '%' . $search . '%');
            })
                ->with('productOrderItems.product.productImages', 'productOrderItems.productPrice', 'cqrcode')
                ->where(function ($q) {
                    $q->where('status', 'pending_payment')
                        ->orWhere('status', 'completed');
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
        } else {
            return response(['message' => 'Unauthorized Access'], 401);
        }
    }
}
