<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductOrderStoreValidation extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function rules()
    {
        return [
            'individuals_id'                    => 'required',
            'order_items'                       => 'required|array',
            'order_items.*.product_id'          => 'required|exists:products,id',
            'order_items.*.product_price_id'    => 'required|exists:product_prices,id',
            'order_items.*.quantity'            => 'required',
            'access'                            => 'required',
            'amount'                            => 'required|numeric'
        ];
    }
    public function messages()
    {
        return [
            'individuals_id.required'           => 'Please specify the person who want to buy.',
            'order_items.required'              => 'Order items must be atleast one.',
            'order_items.required'              => 'Order items must be atleast one.',
            'amount.required'                   => 'Amount is required.',
            'amount.numeric'                    => 'Format must be like this (X.XX)'
        ];
    }
}