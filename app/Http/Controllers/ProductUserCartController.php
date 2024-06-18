<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductUserCartStoreValidation;
use App\Http\Requests\ProductUserCartUpdateValidation;
use App\Models\Product;
use App\Models\Products;
use App\Models\ProductUserCart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ProductUserCartController extends Controller
{
    public function index(Request $request)
    {
        $validate = $request->validate([
            'individual_id' => 'required',
            'access' => 'required'
        ]);
        $inviduals_id = DB::connection('second_database')->select('select * from individuals where id = ' . $validate['individual_id']);
        if ($inviduals_id) {
            $search = ($request['search'] != '' || $request['search'] != null) ? $request['search'] : null;

            $data = Products::when($search, function ($q) use ($search) {
                $q->where('name', 'ilike', '%' . $search . '%');
            })
                ->withCount(['productUserCart as total_quantity' => function ($q) use ($validate) {
                    $q->where('individuals_id', $validate['individual_id'])->select(DB::raw('SUM(quantity)'));
                }])
                ->whereHas('productUserCart', function ($q) use ($validate) {
                    $q->where('individuals_id', $validate['individual_id']);
                })
                ->with('productPrice', 'productImages');

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
    public function store(ProductUserCartStoreValidation $request)
    {
        $request->validated();
        $inviduals_id = DB::connection('second_database')->select('select * from individuals where id = ' . $request['individuals_id']);
        if ($inviduals_id) {
            ProductUserCart::create([
                'individuals_id' => $request['individuals_id'],
                'product_id' => $request['product_id'],
                'product_price_id' => $request['product_price_id'],
                'quantity' => $request['quantity'],
            ]);

            return response([
                'message' => 'Data saved.'
            ]);
        } else {
            return response(['message' => 'Individual not found'], 404);
        }
    }

    public function update(ProductUserCartUpdateValidation $request)
    {

        $request->validated();
        $inviduals_id = DB::connection('second_database')->select('select * from individuals where id = ' . $request['individuals_id']);
        if ($inviduals_id) {
            $productUserCart = ProductUserCart::find($request['id']);
            if (!$productUserCart) {
                return response([
                    'message' => 'Invalid product id'
                ], 400);
            } else {
                $productUserCart->update([
                    'quantity' => $request['quantity'],
                ]);

                return response([
                    'message' => 'Data updated.'
                ]);
            }
        } else {
            return response(['message' => 'Individual not found'], 404);
        }
    }


    public function destroy(Request $request)
    {
        $request->validate([
            'product_id'            => 'required|exists:products,id',
            'access'                => 'required',
            'individuals_id'        => 'required'
        ]);
        $inviduals_id = DB::connection('second_database')->select('select * from individuals where id = ' . $request->individuals_id);
        if (!$inviduals_id) {
            return response(['message' => 'Individual not found'], 404);
        } else {
            $result = ProductUserCart::where('product_id',$request->product_id)->where('individuals_id', $request->individuals_id);

            if ($result) {
                $result->delete();
                return response(['message' => 'Deleted Successfully']);
            } else {
                return response(['message' => 'Not found'], 404);
            }
        }
    }
}
