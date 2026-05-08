<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->input('id');

        return [

            'id_supplier' => [
                'nullable', // 🔥 boleh kosong (auto generate di controller)
                'string',
                Rule::unique('supplier', 'id_supplier')->ignore($id),
            ],

            'nama' => [
                'required',
                'string',
            ],

            'alamat' => [
                'required',
                'string',
            ],

            'alamat_pajak' => [
                'nullable',
                'string',
            ],

            'kodepos' => [
                'nullable',
                'string',
            ],

            'negara' => [
                'required',
                'string',
            ],

            'telepon' => [
                'required',
                'string',
            ],

            'personal_kontak' => [
                'nullable',
                'string',
            ],

            'email' => [
                'nullable',
                'email',
            ],

            'website' => [
                'nullable',
                'string',
            ],

            'status' => [
                'nullable',
                Rule::in(['1', '2']),
            ],
        ];
    }

    public function messages(): array
    {
        return [

            'id_supplier.unique' => 'Customer ID has already been taken',

            'nama.required' => 'Name is required',

            'alamat.required' => 'Address is required',

            'kodepos.required' => 'Postal code is required',

            'negara.required' => 'Country is required',

            'telepon.required' => 'Phone number is required',

            'personal_kontak.required' => 'Contact person is required',

            'email.email' => 'Invalid email format',
            'email.unique' => 'Email has already been taken',
        ];
    }
}
