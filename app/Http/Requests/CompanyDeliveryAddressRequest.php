<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompanyDeliveryAddressRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->input('id');

        return [
            'address_name' => [
                'required',
                Rule::unique('companydeliveryaddress', 'address_name')->ignore($id),
            ],
            'address' => 'required|string|max:255',

        ];
    }

    public function message(): array
    {
        return [
            'address_name.required' => 'Address Name is required',
            'address_name.unique' => 'Address Name has already been taken',
            'address.required' => 'Address is required',
            'address.string' => 'Address must be a string',
            'address.max' => 'Address must not exceed 255 characters',

        ];
    }
}
