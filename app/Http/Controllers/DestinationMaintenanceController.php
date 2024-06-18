<?php

namespace App\Http\Controllers;

use App\Models\DestinationMaintenance;
use Illuminate\Http\Request;

class DestinationMaintenanceController extends Controller
{
    //

    public function index(Request $request)
    {
        $request->validate([
            'destination_id'    => 'required'
        ]);
        $search = $search = ($request['search'] != '' || $request['search'] != null) ? $request['search'] : null;

        $data = DestinationMaintenance::when($search,function ($q) use ($search) {
                                        $q->where('name', 'ilike', '%' . $search . '%');
                                            }
                                        )
                                        ->where('destination_id', $request->destination_id)
                                        ->with('destination');

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
            'destination_id' => 'required',
            'maintenance_date' => 'required|date',
            'name'          => 'required',
            'auth'              => 'required|string'
        ]);

        $auth = (new ActionAuthorizationController)->authChecker($request);
        $check_auth = (new ActionAuthorizationController)->checkAuth($auth);

        if ($check_auth) {
            destinationMaintenance::create([
                'destination_id' => $request->destination_id,
                'maintenance_date' => $request->maintenance_date,
                'name'             => $request->name
            ]);

            return response(['message' => 'Maintenance Date Created']);
        } else {
            return response(['message' => 'Unauthorized Access'], 401);
        }
    }

    public function delete($id)
    {

        $destinationMaintenance = DestinationMaintenance::find($id);

        if (!$destinationMaintenance) {
            return response(['message' => 'Invalid ID'], 400);
        } else {
            $destinationMaintenance->delete();
        }


        return response(['message' => 'Deleted Successfully']);
    }
}
