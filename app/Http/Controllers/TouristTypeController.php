<?php

namespace App\Http\Controllers;

use App\Models\TouristType;
use Illuminate\Http\Request;

class TouristTypeController extends Controller
{
    public function index(Request $request)
    {
        $search = ($request['search'] != '' || $request['search'] != null) ? $request['search'] : null;

        $data = TouristType::when($search, function ($q) use ($search) {
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
            'name' => 'required',
            'auth' => 'string|required'
        ]);

        $auth = (new ActionAuthorizationController)->authChecker($request);
        $check_auth = (new ActionAuthorizationController)->checkAuth($auth);

        if ($check_auth) {
            TouristType::create($request);
            return response([
                'message' => 'Data saved.'
            ]);
        } else {
            return response(['message' => 'Aunthurozied Access'], 401);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
        ]);


        $touristType = TouristType::find($id);

        if (!$touristType) {
            return response([
                'message' => 'Data not found.'
            ], 404);
        }

        $touristType->update([
            'name' => $request['name']
        ]);

        return response([
            'message' => 'Data updated.'
        ]);
    }

    public function delete($id)
    {

        $touristType = TouristType::find($id);

        if (!$touristType) {
            return response([
                'message' => 'Data not found.'
            ], 404);
        }

        return response([
            'message' => 'Data deleted.'
        ]);
    }

    public function get($id)
    {
        $touristType = TouristType::find($id);

        if (!$touristType) {
            return response([
                'message' => 'Data not found.'
            ], 404);
        }
        return response([
            'data' => $touristType
        ]);
    }

    public function showTouristTypes()
    {
        return response([
            'data' => TouristType::all()
        ]);
    }
}