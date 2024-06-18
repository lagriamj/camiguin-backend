<?php

namespace App\Http\Controllers;

use App\Models\Rules;
use Illuminate\Http\Request;

class RulesController extends Controller
{
    public function index(Request $request)
    {
        $search = ($request['search'] != '' || $request['search'] != null) ? $request['search'] : null;

        $data = Rules::when($search, function ($q) use ($search) {
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
        ]);

        $rule = Rules::create($request->toArray());
        return response(['message' => 'Rule Created', 'id' => $rule->id]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
        ]);

        $rule = Rules::find($id);
        if (!$rule) {
            return response(['message' => 'Rule not found'], 404);
        }

        $rule->update($request->toArray());
        return response(['message' => 'Rule Updated']);
    }

    public function delete($id)
    {
        $rules = Rules::find($id);
        if (!$rules) {
            return response(['message' => 'Rule not found'], 404);
        }

        $rules->delete();

        return response(['message' => 'Rule Deleted']);
    }

    public function get($id)
    {
        $rule = Rules::find($id);
        if (!$rule) {
            return response(['message' => 'Rule not found'], 404);
        }

        return response(['data' => $rule]);
    }
}