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
            'notel_bisnis' => 'required|string|max:50',
            'no_hp' => 'nullable|string|max:50',
            'no_whatsapp' => 'required|string|max:50',
            'email' => 'nullable|email|max:255',
            'faximili' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255', // Validasi format URL (http/https)
            'alamat_pembayaran' => 'nullable|string|max:500',
            'kota' => 'nullable|string|max:100',
            'kodepos' => 'nullable|string|max:10',
            'provinsi' => 'nullable|string|max:100',
            'negara' => 'nullable|string|max:100',
            'tipe_pemasok_id' => 'nullable|string|max:255', // Sesuaikan jika ini harusnya foreign key integer
            'syarat_pembelian' => 'nullable|string|max:255',
            'default_diskon' => 'nullable|numeric|min:0|max:100', // Asumsi diskon berupa angka persen (0-100)
            'default_deskripsi' => 'nullable|string|max:500',
            'status' => 'required|in:0,1,2', // Hanya boleh diisi angka 0, 1, atau 2
        ];
    }

    /**
     * Kustomisasi pesan error (Opsional)
     */
    public function messages(): array
    {
        return [
            'id_supplier.required' => 'The supplier ID field is required.',
            'id_supplier.unique' => 'This supplier ID has already been registered in the system.',
            'nama_supplier.required' => 'The supplier name field cannot be empty.',
            'notel_bisnis.required' => 'The business phone number field is required.',
            'no_whatsapp.required' => 'The whatsapp number field is required.',
            'email.email' => 'The email address entered is invalid.',
            'website.url' => 'The website URL format is incorrect (must start with http:// or https://).',
            'status.in' => 'The selected status is invalid (must be Active, Inactive, or Deleted).',
            'status.required' => 'The status field is required.',
        ];
    }
}
