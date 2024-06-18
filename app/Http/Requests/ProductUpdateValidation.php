<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductUpdateValidation extends FormRequest
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
            'product_category_id'       => 'required|exists:products_categories,id',
            'name'                      => 'required',
            'quantity'                  => 'required|integer',
            'description'               => 'required',
            'vendor'                    => 'required',
            'weight'                    => 'required',
            'storage_condition'         => 'required',
            'pre_order'                 => 'required|boolean',
            'price'                     => 'required|array',
            'price.*.price'             => 'required|numeric',
            'product_condition_id'      => 'required|exists:product_conditions,id',
            'shipping'                  => 'required',
            'shipping.weight'         => 'required',
            'shipping.shipping_fee'   => 'required|numeric',
            'auth'                    => 'string|required',
            'variants'                  => 'required|array',
            'variants.*.variant_id'     => 'required|exists:variants,id',
            'variants.*.variants_items' => 'required|array',
            'variants.*.variants_items.*.variant_item_name' => 'required|string',
            'variants.*.variants_items.*.price' => 'required|numeric',
            'variants.*.variants_items.*.stock' => 'required|integer',
        ];
    }
    public function messages()
    {
        return [
            'product_category_id.exists'        => 'Please select a specific category.',
            'product_category_id.required'      => 'Product category field is required.',
            'name.required'                     => 'Name field is required.',
            'quantity.required'                 => 'Quantity field is required.',
            'quantity.integer'                  => 'Quantity must be a number',
            'description.required'              => 'Description field is required.',
            'vendor.required'                   => 'Vendor field is required.',
            'weight.required'                   => 'Weight field is required.',
            'storage_condition.required'        => 'Storage condition field is required.',
            'pre_order.required'                => 'Pre order field is required.',
            'pre_order.boolean'                 => 'Pre order field must be a boolean.',
            'product.condition_id.required'     => 'Condition field is required.',
            'product.condition_id.exists'       => 'Please select a specific condition.',
            'shipping.required'                 => 'Shipping field is required.',
            'shipping.weight.required'          => 'Weight field is required.',
            'shipping.shipping_fee.required'    => 'Shipping fee field is required.',
            'shipping.shipping_fee.numeric'     => 'Shipping fee must be a number.',
            'price.array'                       => 'Price field is must be atleast one.',
            'price.*.price.required'            => 'Price field is required.',
            'variants.required'                 => 'Variants field is required',
            'variants.array'                    => 'Variants field must be an array',
            'variants.*.variant_id.required'    => 'Variant Id is required',
            'variants.*.variant_id.exists'      => 'Variant Id does not exist',
            'variants.*.variants_items.*.variant_item_name.requred' => 'Variant item name field is required',
            'variants.*.variants_items.*.price.required' => 'Price field is required',
            'variants.*.variants_items.*.price.numeric' => 'Price must be a number',
            'variants.*.variants_items.*.stock.required' => 'Stock field is required',
            'variants.*.variants_items.*.stock.integer' => 'Stock must be a number'

        ];
    }
}