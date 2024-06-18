<?php

namespace App\Http\Controllers;

use App\Models\ProductsCategory;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    //
    public function index(Request $request)
    {

        $request->validate([
            'auth'  => 'string|required',
        ]);

        $auth = (new ActionAuthorizationController)->authChecker($request);
        $check_auth = (new ActionAuthorizationController)->checkAuth($auth);

        if ($check_auth) {
            $search = ($request['search'] != '' || $request['search'] != null) ? $request['search'] : null;

            $data = ProductsCategory::when($search, function ($q) use ($search) {
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
        } else {
            return response(['message' => 'Unauthorized Access'], 401);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'auth'  => 'string|required'
        ]);

        $auth = (new ActionAuthorizationController)->authChecker($request);
        $check_auth = (new ActionAuthorizationController)->checkAuth($auth);

        if ($check_auth) {
            $request->validate([
                'name' => 'required'
            ]);
            ProductsCategory::create([
                'name' => $request['name']
            ]);
            return response([
                'message' => 'Data saved.'
            ]);
        } else {
            return response(['message' => 'Unauthorized Access'], 401);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'auth'  => 'string|required'
        ]);

        $auth = (new ActionAuthorizationController)->authChecker($request);
        $check_auth = (new ActionAuthorizationController)->checkAuth($auth);

        if ($check_auth) {
            $productCategory = ProductsCategory::find($id);

            if (!$productCategory) {
                return response([
                    'message' => 'Data not found.'
                ], 404);
            }

            $productCategory->update([
                'name' => $request['name']
            ]);

            return response([
                'message' => 'Data updated.'
            ]);
        } else {
            return response(['message' => 'Unauthorized Access'], 401);
        }
    }

    public function delete($id)
    {

        $productCategory = ProductsCategory::find($id);

        if (!$productCategory) {
            return response([
                'message' => 'Data not found.'
            ], 404);
        }

        $productCategory->delete();

        return response([
            'message' => 'Data deleted.'
        ]);
    }

    public function get($id)
    {
        $productCategory = ProductsCategory::find($id);

        if (!$productCategory) {
            return response([
                'message' => 'Data not found.'
            ], 404);
        }

        return response([
            'data' => $productCategory
        ]);
    }
}