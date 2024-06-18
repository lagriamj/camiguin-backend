<?php

namespace App\Http\Controllers;

use App\Http\Requests\DestinationUserCartStoreValidation;
use App\Models\Destination;
use App\Models\DestinationUserCart;
use App\Models\DestinationUserCarts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DestinationUserCartController extends Controller
{

    public function index(Request $request)
    {
        $validate = $request->validate([
            'individuals_id' => 'required',
            'access' => 'required'
        ]);

        $individuals_id = DB::connection('second_database')->select('select * from individuals where id = ' . $validate['individuals_id']);

        if ($individuals_id) {

            $data = DestinationUserCart::where('individuals_id', $validate['individuals_id'])
                ->with('destination.destinationImages', 'destinationTourType', 'destinationTourTypePrice.touristType');

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

    public function store(DestinationUserCartStoreValidation $request)
    {
        $request->validated();
        $individuals_id = DB::connection('second_database')->select('select * from individuals where id = ' . $request['individuals_id']);

        if ($individuals_id) {
            DestinationUserCart::create($request->toArray());
            return response([
                'message' => 'Destination added to cart'
            ]);
        } else {
            return response(['message' => 'Individual not found'], 404);
        }
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'destination_user_cart_id'            => 'required|exists:destination_user_carts,id',
            'access'                => 'required',
            'individuals_id'        => 'required'
        ]);

        $individuals_id = DB::connection('second_database')->select('select * from individuals where id = ' . $request['individuals_id']);

        if (!$individuals_id) {
            return response(['message' => "Individual not found"], 404);
        } else {
            $result = DestinationUserCart::where('id', $request->destination_user_cart_id)->where('individuals_id', $request->individuals_id);

            if ($result) {
                $result->delete();
                return response(['message' => "Deleted successfully"]);
            } else {
                return response(['message' => "Not found"], 404);
            }
        }
    }
}
