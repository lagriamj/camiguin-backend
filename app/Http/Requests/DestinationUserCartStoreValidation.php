<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DestinationUserCartStoreValidation extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'individuals_id'                    => 'required',
            'destination_id'                    => 'required|exists:destinations,id',
            'destination_tour_type_id'          => 'required|exists:destination_tour_types,id',
            'destination_tour_type_price_id'    => 'required|exists:destination_tour_type_prices,id',
            'quantity'                          => 'required|integer',
            'access'                            => 'required',
            'departure_time'                    => 'required|date'
        ];
    }

    public function messages()
    {
        return [
            'individuals_id.required'                       => 'Please specify the person who want to buy.',
            'departure_time.required'                       => 'Please put a arrival date.',
            'departure_time.date'                           => 'Format must be a date.',
            'destination_id.required'                       => 'Please specify where you want to go.',
            'destination_tour_type_id.required'             => 'Please specify what type of tour you want.',
            'destination_tour_type_price_id.required'       => 'Please indicate the price type.',
            'quantity.required'                             => 'Please input quantity.',
            'destination_id.exists'                         => 'Destination you want to go is invalid.',
            'destination_tour_type_id.exists'               => 'Destination tour type is invalid.',
            'destination_tour_type_price_id.exists'         => 'Destination tour type price is invalid.',
        ];
    }
}
