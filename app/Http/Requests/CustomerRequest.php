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

            'notel_bisnis' => 'nullable|string|max:20',
            'no_hp' => 'nullable|string|max:20',
            'no_whatsapp' => 'nullable|string|max:20',

            'email' => 'nullable|email|max:255',
            'faximili' => 'nullable|string|max:20',
            'website' => 'nullable|string|max:255',

            'alamat_tagihan' => 'required|string|max:255',
            'kota_tagihan' => 'required|string|max:100',
            'kodepos_tagihan' => 'required|string|max:10',
            'provinsi_tagihan' => 'required|string|max:100',
            'negara_tagihan' => 'required|string|max:100',

            'status' => 'required|in:0,1,2',
        ];
    }

    public function messages(): array
    {
        return [
            // ID Customer
            'id_customer.required' => 'Customer ID is required.',
            'id_customer.string' => 'Customer ID must be a valid string.',
            'id_customer.max' => 'Customer ID may not be greater than 50 characters.',
            'id_customer.unique' => 'Customer ID already exists.',

            // Nama Customer
            'nama_customer.required' => 'Customer name is required.',
            'nama_customer.string' => 'Customer name must be a valid string.',
            'nama_customer.max' => 'Customer name may not be greater than 255 characters.',

            // Kontak
            'notel_bisnis.string' => 'Business phone must be a valid string.',
            'notel_bisnis.max' => 'Business phone may not be greater than 20 characters.',

            'no_hp.string' => 'Phone number must be a valid string.',
            'no_hp.max' => 'Phone number may not be greater than 20 characters.',

            'no_whatsapp.string' => 'WhatsApp number must be a valid string.',
            'no_whatsapp.max' => 'WhatsApp number may not be greater than 20 characters.',

            // Email
            'email.email' => 'Email must be a valid email address.',
            'email.max' => 'Email may not be greater than 255 characters.',

            // Fax & Website
            'faximili.string' => 'Fax must be a valid string.',
            'faximili.max' => 'Fax may not be greater than 20 characters.',

            'website.string' => 'Website must be a valid string.',
            'website.max' => 'Website may not be greater than 255 characters.',

            // Alamat Tagihan
            'alamat_tagihan.required' => 'Billing address is required.',
            'alamat_tagihan.string' => 'Billing address must be a valid string.',
            'alamat_tagihan.max' => 'Billing address may not be greater than 255 characters.',

            'kota_tagihan.required' => 'Billing city is required.',
            'kota_tagihan.string' => 'Billing city must be a valid string.',
            'kota_tagihan.max' => 'Billing city may not be greater than 100 characters.',

            'kodepos_tagihan.required' => 'Billing postal code is required.',
            'kodepos_tagihan.string' => 'Billing postal code must be a valid string.',
            'kodepos_tagihan.max' => 'Billing postal code may not be greater than 10 characters.',

            'provinsi_tagihan.required' => 'Billing province is required.',
            'provinsi_tagihan.string' => 'Billing province must be a valid string.',
            'provinsi_tagihan.max' => 'Billing province may not be greater than 100 characters.',

            'negara_tagihan.required' => 'Billing country is required.',
            'negara_tagihan.string' => 'Billing country must be a valid string.',
            'negara_tagihan.max' => 'Billing country may not be greater than 100 characters.',

            // Status
            'status.required' => 'Status is required.',
            'status.in' => 'Invalid status selected.',
        ];
    }
}
