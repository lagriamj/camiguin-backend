<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SalesInformationStoreValidation extends FormRequest
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
            'tour_type_id' => 'required|exists:tour_types,id',
            'limit' => 'required|integer',
            'time_in' => 'required',
            'time_out' => 'required',
            'price' => 'required|array',
            'price.*.tourist_type_id' => 'required|exists:tourist_types,id',
            'price.*.price' => 'required',
            'auth'  => 'string|required'
        ];
    }

    public function messages()
    {
        return [
            'tour_type_id.required' => 'Tour type is required',
            'tour_type_id.exists' => 'Tour type does not exist',
            'limit.required' => 'Limit is required',
            'limit.integer' => 'Limit must be an integer',
            'time_in.required' => 'Time in is required',
            'time_out.required' => 'Time out is required',
            'price.required' => 'Price is required',
            'price.array' => 'Price must be an array',
            'price.*.tourist_type_id.required' => 'Tourist type is required',
            'price.*.tourist_type_id.exists' => 'Tourist type does not exist',
            'price.*.price.required' => 'Price is required'
        ];
    }
}
