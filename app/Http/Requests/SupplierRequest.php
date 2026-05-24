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
        // Ambil ID supplier saat ini jika prosesnya adalah UPDATE (untuk mengabaikan rule unique)
        $supplierId = $this->route('supplier');

        return [
            'id_supplier' => [
                'required',
                'string',
                'max:255',
                // Jika create harus unik, jika update abaikan keunikan untuk data diri sendiri
                $supplierId ? 'unique:supplier,id_supplier,'.$supplierId : 'unique:supplier,id_supplier',
            ],
            'nama_supplier' => 'required|string|max:255',
            'kategori_supplier_id' => 'required|string|max:255',
            'phone_1' => 'required|string|max:50',
            'phone_2' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'faximili' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255', // Validasi format URL (http/https)
            'alamat_pembayaran' => 'required|string|max:500',
            'kota' => 'required|string|max:100',
            'kodepos' => 'required|string|max:10',
            'provinsi' => 'required|string|max:100',
            'negara' => 'required|string|max:100',
            'tipe_pemasok_id' => 'nullable|string|max:255', // Sesuaikan jika ini harusnya foreign key integer
            'syarat_pembelian' => 'nullable|string|max:255',
            'default_diskon' => 'nullable|numeric|min:0|max:100', // Asumsi diskon berupa angka persen (0-100)
            'default_deskripsi' => 'nullable|string|max:500',
            'status' => 'required|in:0,1,2', // Hanya boleh diisi angka 0, 1, atau 2
        ];
    }

    public function messages(): array
    {
        return [
            'id_supplier.required' => 'Supplier ID is required.',
            'id_supplier.string' => 'Supplier ID must be a string.',
            'id_supplier.max' => 'Supplier ID may not be greater than 255 characters.',
            'id_supplier.unique' => 'Supplier ID has already been taken.',

            'nama_supplier.required' => 'Supplier name is required.',
            'nama_supplier.string' => 'Supplier name must be a string.',
            'nama_supplier.max' => 'Supplier name may not be greater than 255 characters.',

            'kategori_supplier_id.required' => 'Supplier category is required.',
            'kategori_supplier_id.string' => 'Supplier category must be a string.',
            'kategori_supplier_id.max' => 'Supplier category may not be greater than 255 characters.',

            'phone_1.required' => 'Primary phone number is required.',
            'phone_1.string' => 'Primary phone number must be a string.',
            'phone_1.max' => 'Primary phone number may not be greater than 50 characters.',

            'phone_2.string' => 'Secondary phone number must be a string.',
            'phone_2.max' => 'Secondary phone number may not be greater than 50 characters.',

            'email.email' => 'Email must be a valid email address.',
            'email.max' => 'Email may not be greater than 255 characters.',

            'faximili.string' => 'Fax number must be a string.',
            'faximili.max' => 'Fax number may not be greater than 50 characters.',

            'website.url' => 'Website must be a valid URL.',
            'website.max' => 'Website may not be greater than 255 characters.',

            'alamat_pembayaran.required' => 'Billing address is required.',
            'alamat_pembayaran.string' => 'Billing address must be a string.',
            'alamat_pembayaran.max' => 'Billing address may not be greater than 500 characters.',

            'kota.required' => 'City is required.',
            'kota.string' => 'City must be a string.',
            'kota.max' => 'City may not be greater than 100 characters.',

            'kodepos.required' => 'Postal code is required.',
            'kodepos.string' => 'Postal code must be a string.',
            'kodepos.max' => 'Postal code may not be greater than 10 characters.',

            'provinsi.required' => 'Province is required.',
            'provinsi.string' => 'Province must be a string.',
            'provinsi.max' => 'Province may not be greater than 100 characters.',

            'negara.required' => 'Country is required.',
            'negara.string' => 'Country must be a string.',
            'negara.max' => 'Country may not be greater than 100 characters.',

            'tipe_pemasok_id.string' => 'Supplier type must be a string.',
            'tipe_pemasok_id.max' => 'Supplier type may not be greater than 255 characters.',

            'syarat_pembelian.string' => 'Purchase terms must be a string.',
            'syarat_pembelian.max' => 'Purchase terms may not be greater than 255 characters.',

            'default_diskon.numeric' => 'Default discount must be a number.',
            'default_diskon.min' => 'Default discount must be at least 0.',
            'default_diskon.max' => 'Default discount may not be greater than 100.',

            'default_deskripsi.string' => 'Default description must be a string.',
            'default_deskripsi.max' => 'Default description may not be greater than 500 characters.',

            'status.required' => 'Status is required.',
            'status.in' => 'Status must be one of the following values: 0, 1, or 2.',
        ];
    }
}
