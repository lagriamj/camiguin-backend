<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductUserCartStoreValidation extends FormRequest
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
            'individuals_id'        => 'required',
            'product_id'            => 'required|exists:products,id',
            'product_price_id'      => 'required|exists:product_prices,id',
            'quantity'              => 'required',
            'access'                 => 'required'
        ];
    }
    public function messages()
    {
        return [
            'individuals_id.required'           => 'Please specify the person who want to buy.',
            'product_id.required'               => 'Please specify the what product you want.',
            'product_id.exists'                 => 'This intent is invalid. Please select a product from the list.',
            'product_price_id.required'         => 'Please select a type.',
            'product_price_id.exists'           => 'This intent is invalid. Please select from the dropdown',
            'quantity.required'                 => 'Please include a quantity.'
        ];
    }
}
