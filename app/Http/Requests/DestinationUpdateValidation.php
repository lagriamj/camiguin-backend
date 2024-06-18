<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DestinationUpdateValidation extends FormRequest
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
            'rules'                                 => 'required|array',
            'rules.*.rule_id'                       => 'required|exists:rules,id',
            'rentals'                               => 'required|array',
            'rentals.*.rental_id'                   => 'required|exists:rentals,id',
            'auth'                                  => 'string|required'
        ];
    }
    public function messages()
    {
        return [
            'destination_category_id.exists'    => 'Please select a specific category.',
            'destination_category_id.required'  => 'Product category field is required.',
            'name.required'                     => 'Name field is required.',
            'description.required'              => 'Description field is required.',
            'address.required'                  => 'Address field is required.',
            'rules.required'                    => 'Rules field is required.',
            'rules.array'                       => 'Rules field must be an array.',
            'rules.*.rule_id.required'          => 'Rule id field is required.',
            'rules.*.rule_id.exists'            => 'Rule id does not exist.',
            'rentals.required'                  => 'Rentals field is required.',
            'rentals.array'                     => 'Rentals field must be an array.',
            'rentals.*.rental_id.required'      => 'Rental id field is required.',
            'rentals.*.rental_id.exists'        => 'Rental id does not exist.',
            'auth.required'                     => 'Authorization field is required.',
        ];
    }
}
