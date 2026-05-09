<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SalesmanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->input('id');

        return [

            'id_salesman' => [
                'nullable', // 🔥 boleh kosong (auto generate di controller)
                'string',
                Rule::unique('salesman', 'id_salesman')->ignore($id),
            ],

            'nama' => [
                'required',
                'string',
            ],

            'alamat' => [
                'required',
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

            'jabatamn' => [
                'nullable',
                'string',
            ],

            'email' => [
                'nullable',
                'email',
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

            'id_salesman.unique' => 'Salesman ID has already been taken',

            'nama.required' => 'Name is required',

            'alamat.required' => 'Address is required',

            'kodepos.required' => 'Postal code is required',

            'negara.required' => 'Country is required',

            'telepon.required' => 'Phone number is required',

            'email.email' => 'Invalid email format',
            'email.unique' => 'Email has already been taken',
        ];
    }
}
