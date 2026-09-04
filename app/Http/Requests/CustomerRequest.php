<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('customer'); // untuk update

        return [
            'id_customer' => 'required|string|max:50|unique:customer,id_customer,'.$id,
            'nama_customer' => 'required|string|max:255',
            'kategori_customer_id' => 'required|string|max:255',

            'phone_1' => 'required|string|max:50',
            'phone_2' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'faximili' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255', // Validasi format URL (http/https)
            'alamat_tagihan' => 'required|string|max:500',
            'kota_tagihan' => 'required|string|max:100',
            'kodepos_tagihan' => 'required|string|max:10',
            'negara_tagihan' => 'required|string|max:100',
            'provinsi_tagihan' => 'required|string|max:100',
            'tipe_pemasok_id' => 'nullable|string|max:255', // Sesuaikan jika ini harusnya foreign key integer
            'syarat_pembelian' => 'nullable|string|max:255',
            'default_diskon' => 'nullable|numeric|min:0|max:100', // Asumsi diskon berupa angka persen (0-100)
            'default_deskripsi' => 'nullable|string|max:500',
            'status' => 'required|in:0,1,2',
            'pr_details' => ['nullable', 'json'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_customer.required' => 'Customer ID is required.',
            'id_customer.string' => 'Customer ID must be a string.',
            'id_customer.max' => 'Customer ID may not be greater than 50 characters.',
            'id_customer.unique' => 'Customer ID has already been taken.',

            'nama_customer.required' => 'Customer name is required.',
            'nama_customer.string' => 'Customer name must be a string.',
            'nama_customer.max' => 'Customer name may not be greater than 255 characters.',

            'kategori_customer_id.required' => 'Customer category is required.',
            'kategori_customer_id.string' => 'Customer category must be a string.',
            'kategori_customer_id.max' => 'Customer category may not be greater than 255 characters.',

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

            'alamat_tagihan.required' => 'Billing address is required.',
            'alamat_tagihan.string' => 'Billing address must be a string.',
            'alamat_tagihan.max' => 'Billing address may not be greater than 500 characters.',

            'kota_tagihan.required' => 'Billing city is required.',
            'kota_tagihan.string' => 'Billing city must be a string.',
            'kota_tagihan.max' => 'Billing city may not be greater than 100 characters.',

            'kodepos_tagihan.required' => 'Billing postal code is required.',
            'kodepos_tagihan.string' => 'Billing postal code must be a string.',
            'kodepos_tagihan.max' => 'Billing postal code may not be greater than 10 characters.',

            'negara_tagihan.required' => 'Billing country is required.',
            'negara_tagihan.string' => 'Billing country must be a string.',
            'negara_tagihan.max' => 'Billing country may not be greater than 100 characters.',

            'provinsi_tagihan.required' => 'Billing province is required.',
            'provinsi_tagihan.string' => 'Billing province must be a string.',
            'provinsi_tagihan.max' => 'Billing province may not be greater than 100 characters.',

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
