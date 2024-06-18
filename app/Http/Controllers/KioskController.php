<?php

namespace App\Http\Controllers;

use App\Http\Requests\KioskStoreValidation;
use App\Models\Kiosk;
use Illuminate\Http\Request;

class KioskController extends Controller
{
    //

    public function index(Request $request)
    { 
        $search = ($request['search'] != '' || $request['search'] != null) ? $request['search'] : null;

        $data = Kiosk::when($search, function ($q) use ($search) {
            $q->where('destination_id', 'ilike', '%' . $search . '%');
        })
            ->whereHas('destination', function($q){
                $q->where('draft', true);
            })
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

    public function store(KioskStoreValidation $request)
    {
        $request->validated();

        $kiosk = Kiosk::create($request->toArray());
        return response(['message' => 'Kiosk Created', 'id' => $kiosk->id]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'destination_id' => 'required|exists:destinations,id',
            //'qr_code' => 'required|unique:kiosks,qr_code,' . $id . ',id'
        ]);

        $kiosk = Kiosk::find($id);
        if (!$kiosk) {
            return response(['message' => 'Kiosk not found'], 404);
        }
        $kiosk->update($request->all());

        return response(['message' => 'Kiosk Updated']);
    }

    public function delete($id)
    {
        $kiosk = Kiosk::find($id);
        if (!$kiosk) {
            return response(['message' => 'Kiosk not found'], 404);
        }
        $kiosk->delete();
        return response(['message' => 'Kiosk Deleted']);
    }

    public function get($id)
    {
        $kiosk = Kiosk::where('id', $id)->with('destination')->first();
        if (!$kiosk) {
            return response(['message' => 'Kiosk not found'], 404);
        }
        return response(['data' => $kiosk]);
    }
}   
