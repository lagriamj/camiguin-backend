<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DestinationStoreValidation extends FormRequest
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
            'destination_category_id'               => 'required|exists:destination_categories,id',
            'name'                                  => 'required',
            'description'                           => 'required',
            'address'                               => 'required',
            'limit'                                 => 'required|integer',
            'tour_types'                            => 'required|array',
            'tour_types.*.tour_type_id'             => 'required|exists:tour_types,id',
            'tour_types.*.limit'                    => 'required|integer',
            'tour_types.*.time_in'                  => 'required',
            'tour_types.*.time_out'                 => 'required',
            'tour_types.*.price'                    => 'required|array',
            'tour_types.*.price.*.tourist_type_id'  => 'required|exists:tourist_types,id',
            'tour_types.*.price.*.price'            => 'required',
            'rules'                                 => 'required|array',
            'rules.*.rule_id'                       => 'required|exists:rules,id'
        ];
    }
    public function messages()
    {
        return [
            'destination_category_id.exists'    => 'Please select a specific category.',
            'destination_category_id.required'  => 'Product category field is required.',
            'name.required'                     => 'Name field is required.',
            'description.required'              => 'Description field is required.',
            'limit.required'                    => 'Please specify the number of tourist allowed.',
            'limit.integer'                     => 'Please input a number only.',
            'address.required'                  => 'Address field is required.',
            'rules.required'                    => 'Rules field is required.',
            'rules.array'                      => 'Rules field is must be atleast one.',
            'rules.*.rule_id.required'          => 'Please specify the type of selection.',
            'rules.*.rule_id.exists'            => 'Please select a specific rule.'
        ];
    }
}
