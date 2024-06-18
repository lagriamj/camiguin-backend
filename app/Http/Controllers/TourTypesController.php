<?php

namespace App\Http\Controllers;

use App\Models\TourTypes;
use Illuminate\Http\Request;

class TourTypesController extends Controller
{
    public function index(Request $request)
    {
        $search = ($request['search'] != '' || $request['search'] != null) ? $request['search'] : null;

        $data = TourTypes::when($search, function ($q) use ($search) {
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
        $validate = $request->validate([
            'name' => 'required',
            'auth' => 'required|string'
        ]);

        $auth = (new ActionAuthorizationController)->authChecker($request);
        $check_auth = (new ActionAuthorizationController)->checkAuth($auth);

        if ($check_auth) {
            TourTypes::create([
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
            'auth' => 'required|string'
        ]);

        $auth = (new ActionAuthorizationController)->authChecker($request);
        $check_auth = (new ActionAuthorizationController)->checkAuth($auth);

        if ($check_auth) {
            TourTypes::findOrFail($id)->update([
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

        $tourType = TourTypes::find($id);

        if (!$tourType) {
            return response([
                'message' => 'Data not found.'
            ], 404);
        }

        $tourType->delete();

        return response([
            'message' => 'Data deleted.'
        ]);
    }

    public function get($id)
    {
        $tourType = TourTypes::find($id);

        if (!$tourType) {
            return response([
                'message' => 'Data not found.'
            ], 404);
        }

        return response([
            'data' => $tourType
        ]);
    }
}
