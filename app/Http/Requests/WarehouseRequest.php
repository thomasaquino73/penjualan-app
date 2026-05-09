<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WarehouseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->input('id');

        return [

            'id_gudang' => [
                'nullable', // 🔥 boleh kosong (auto generate di controller)
                'string',
                Rule::unique('warehouse', 'id_gudang')->ignore($id),
            ],

            'nama_gudang' => [
                'required',
                'string',
            ],

            'alamat' => [
                'required',
                'string',
            ],

            'keterangan' => [
                'nullable',
                'string',
            ],

            'penanggung_jawab' => [
                'required',
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

            'id_gudang.unique' => 'Warehouse ID has already been taken',

            'nama_gudang.required' => 'Warehouse Name is required',

            'alamat.required' => 'Address is required',

            'keterangan.required' => 'Description is required',

            'penanggung_jawab.required' => 'Responsible Person is required',
        ];
    }
}
