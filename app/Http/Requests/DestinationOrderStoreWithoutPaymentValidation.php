<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DestinationOrderStoreWithoutPaymentValidation extends FormRequest
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
            'qr_code'                                                                        => 'required',
            'payment_method'                                                                 => 'required',
            'departure_time'                                                                 => 'required|date',
            'destinations'                                                                   => 'required|array',
            'destinations.*.destination_id'                                                  => 'required|exists:destinations,id',
            'destinations.*.destination_tour_type_id'                                        => 'required|exists:destination_tour_types,id',
            'destinations.*.tour_type_prices'                                                => 'required|array',
            'destinations.*.tour_type_prices.*.destination_tour_type_price_id'               => 'required|exists:destination_tour_type_prices,id',
            'destinations.*.tour_type_prices.*.quantity'                                     => 'required|integer',
            'order_type'                                                                     => 'required',
            'kiosk_id'                                                                       => 'exists:kiosks,id'
        ]; 
    }

    public function messages()
    {
        return [
            'qr_code.required'                                                                      => 'Individual field is required.',
            'payment_method.required'                                                               => 'Payment method field is required.',
            'departure_time.required'                                                               => 'Departure time field is required.',
            'departure_time.date'                                                                   => 'Departure time field must be a date.',
            'destinations.required'                                                                 => 'Please specify destination',
            'destinations.array'                                                                    => 'Please put atleast one destination',
            'destinations.*.destination_id.required'                                                => `The destination is required.`,
            'destinations.*.destination_id.exists'                                                  => `The destination you've selected is invalid.`,
            'destinations.*.required'                                                               => `Please select what type of tour you want.`,
            'destinations.*.destination_tour_type_id.required'                                      => `Please select what type of tour you want.`,
            'destinations.*.destination_tour_type_id.exists'                                        => `The tour type you've selected is invalid.`,
            'destinations.*.tour_type_prices.required'                                              => 'Please specify what you want to book.',
            'destinations.*.tour_type_prices.array'                                                 => 'Please select atleast one type of booking.',
            'destinations.*.tour_type_prices.*.destination_tour_type_price_id.required'             => 'Please select atleast one type of booking.',
            'destinations.*.tour_type_prices.*.destination_tour_type_price_id.exists'               => `The type of booking you've selected is invalid.`,
            'destinations.*.tour_type_prices.*.quantity.required'                                   => 'Please input the number of quantity you want to book.',
            'destinations.*.tour_type_prices.*.quantity.integer'                                    => 'Quantity must be type of number.',
            'order_type.required'                                                                   => 'Order type field is required.',
            'kiosk_id.required'                                                                     => 'Kiosk field is required.',
            'kiosk_id.exists'                                                                       => 'Kiosk field is invalid.'
        ];
    }
}