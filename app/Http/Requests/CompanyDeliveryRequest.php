<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompanyDeliveryRequest extends FormRequest
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
            'address' => 'required',

        ];
    }

    public function message(): array
    {
        return [
            'required.required' => 'Company Delivery Name is required',
            'bank_name.unique' => 'Company Delivery Name has already been taken',
            'address.required' => 'Company Delivery Address is required',
        ];
    }
}
