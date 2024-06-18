<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserUpdateValidation extends FormRequest
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
        $user_id = $this->route('id');
        return [
            'first_name'              => 'required',
            'last_name'               => 'required',
            'middle_name'             => 'nullable',
            'suffix'                    => 'nullable',
            'email'             => 'required_if:role,3|email|unique:users,email, ' . $user_id . ',id',
            'qr_code'           => 'required|unique:users,qr_code, ' . $user_id . ',id',
            'password'          => 'nullable',
            'role'              => 'required|exists:roles,id',
            'kiosk_id'          => 'required_if:role,2|exists:kiosks,id',
            'auth'              => 'string|required',
            'business_name'     => 'required_if:role,3|string',
            'tax_number'        => 'required_if:role,3|string',
            'business_address'  => 'required_if:role,3|string',
            'contact_number'    => 'required_if:role,3|string',
        ];
    }

    public function messages()
    {
        return [
            'first_name.required'       => 'First name field is required.',
            'last_name.required'        => 'Last name field is required.',
            'email.email'               => 'Invalid email format.',
            'email.unique'              => 'Email already exists.',
            'email.required'            => 'Email field is required',
            'qr_code.required'          => 'QR Code field is required.',
            'role.required'             => 'Role field is required.',
            'role.exists'               => 'Role field is invalid.',
            'kiosk_id.required_if'      => 'Kiosk field is required.',
            'kiosk_id.exists'           => 'Kiosk field is invalid.',
            'business_name'             => 'Business name is required',
            'tax_number'                => 'Tax number is required',
            'business_address'          => 'Business address is required',
            'contact_number'            => 'Contact number is required',
        ];
    }
}
