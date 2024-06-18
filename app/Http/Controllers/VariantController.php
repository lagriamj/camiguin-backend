<?php

namespace App\Http\Controllers;

use App\Models\Variant;
use Illuminate\Http\Request;

class VariantController extends Controller
{
    public function index(Request $request)
    {
        $search = ($request['search'] != '' || $request['search'] != null) ? $request['search'] : null;

        $data = Variant::when($search, function ($q) use ($search) {
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

    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required|string',
            'auth' => 'string|required'
        ]);

        $auth = (new ActionAuthorizationController)->authChecker($request);
        $check_auth = (new ActionAuthorizationController)->checkAuth($auth);

        if ($check_auth) {
            Variant::create([
                'name'  => $request->name
            ]);
            return response([
                'message' => 'Variant created.'
            ]);
        } else {
            return response(['message' => 'Unauthorized Access'], 401);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'auth'  => 'required|string'
        ]);

        $auth = (new ActionAuthorizationController)->authChecker($request);
        $check_auth = (new ActionAuthorizationController)->checkAuth($auth);

        if ($check_auth) {
            $variant = Variant::find($id);

            if (!$variant) {
                return response(['message' => 'Variant not found']);
            }
            $variant->update([
                'name' => $request->name
            ]);

            return response([
                'message' => 'Variant updated.'
            ]);
        } else {
            return response(['message' => 'Unauthorized Access'], 401);
        }
    }

    public function delete($id)
    {
        $variant = Variant::find($id);

        if (!$variant) {
            return response(['message' => 'Variant not found']);
        }

        $variant->delete();

        return response([
            'message' => 'Variant deleted.'
        ]);
    }

    public function get($id)
    {
        $variant = Variant::find($id)->first();

        if (!$variant) {
            return response(['message' => 'Variant not found']);
        }

        return response([
            'data' => $variant
        ]);
    }
}