<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->input('id');

        return [

            'id_barang' => [
                'required', // 🔥 boleh kosong (auto generate di controller)
                'string',
                Rule::unique('data_barang', 'id_barang')->ignore($id),
            ],
            'nama_barang' => [
                'required', // 🔥 boleh kosong (auto generate di controller)
            ],
            'kategori_id' => [
                'required', // 🔥 boleh kosong (auto generate di controller)
            ],
            'gudang_id' => [
                'required', // 🔥 boleh kosong (auto generate di controller)
            ],
            'unit_id' => [
                'required', // 🔥 boleh kosong (auto generate di controller)
            ],
            'product_type' => [
                'required', // 🔥 boleh kosong (auto generate di controller)
            ],

        ];
    }

    public function messages(): array
    {
        return [

            'id_barang.unique' => 'Product ID has already been taken',
            'id_barang.required' => 'Product ID is required',
            'kategori_id.required' => 'Product category is required',
            'gudang_id.required' => 'Warehouse is required',
            'unit_id.required' => 'Unit is required',
            'product_type.required' => 'Product type is required',
        ];
    }
}
