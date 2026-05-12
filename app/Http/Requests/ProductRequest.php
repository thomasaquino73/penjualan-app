<?php

namespace App\Http\Requests;

use App\Models\Master_Data\Barang;
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
        $id = $this->route('data_barang');
        // atau sesuaikan dengan nama route model binding kamu

        if ($id instanceof Barang) {
            $id = $id->id;
        }

        return [
            'id_barang' => [
                'required',
                'string',
                Rule::unique('data_barang', 'id_barang')->ignore($id),
            ],
            'nama_barang' => [
                'required',
                'string',
                Rule::unique('data_barang', 'nama_barang')->ignore($id),
            ],
            'kategori_id' => ['required'],
            'gudang_id' => ['required'],
            'unit_id' => ['required'],
            'product_type' => ['required'],
        ];
    }

    public function messages(): array
    {
        return [

            'id_barang.unique' => 'Product ID has already been taken',
            'id_barang.required' => 'Product ID is required',
            'nama_barang.unique' => 'Product name has already been taken',
            'nama_barang.required' => 'Product name is required',
            'kategori_id.required' => 'Product category is required',
            'gudang_id.required' => 'Warehouse is required',
            'unit_id.required' => 'Unit is required',
            'product_type.required' => 'Product type is required',
        ];
    }
}
