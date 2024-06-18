<?php

namespace App\Http\Controllers;

use App\Models\Rentals;
use Illuminate\Http\Request;

class RentalController extends Controller
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

            $data = Rentals::when($search, function ($q) use ($search) {
                $q->where('name', 'ilike', '%' . $search . '%');
            })
                ->with('destinationRentals');

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
            'name' => 'required',
            'price' => 'required',
            'limit' => 'required',
            'auth'  => 'required|string'
        ]);

        $auth = (new ActionAuthorizationController)->authChecker($request);
        $check_auth = (new ActionAuthorizationController)->checkAuth($auth);

        if ($check_auth) {
            $rental = Rentals::create($request->toArray());
            return response(['message' => 'Rental Created', 'id' => $rental->id]);
        } else {
            return response(['message' => 'Unauthorized Access'], 401);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'price' => 'required',
            'limit' => 'required',
            'auth'  => 'required|string'
        ]);

        $auth = (new ActionAuthorizationController)->authChecker($request);
        $check_auth = (new ActionAuthorizationController)->checkAuth($auth);

        if ($check_auth) {
            $rental = Rentals::find($id);
            if (!$rental) {
                return response(['message' => 'Rental not found'], 404);
            }

            $rental->update($request->all());

            return response(['message' => 'Rental Updated']);
        } else {
            return response(['message' => 'Unauthorized Access'], 401);
        }
    }

    public function delete($id)
    {

        $rental = Rentals::find($id);

        if (!$rental) {
            return response(['message' => 'Rental not found'], 404);
        }

        $rental->delete();

        return response(['message' => 'Rental deleted'], 200);
    }

    public function get($id)
    {
        $rental = Rentals::where('id', $id)->with(
            'destinationRentals',
            'destinationRentals.destination'
        )->first();

        if (!$rental) {
            return response(['message' => 'Rental not found'], 404);
        }

        return response(['data' => $rental]);
    }
}
