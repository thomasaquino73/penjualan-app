<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShippingRequest extends FormRequest
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
        // Mengambil ID dari input form ATAU dari parameter Route URL jika ada
        $id = $this->input('id') ?? $this->route('shipping');

        return [
            'nama' => [
                'required',
                // Memastikan nama unik di tabel 'shipping', kolom 'nama', abaikan data milik $id ini
                Rule::unique('shipping', 'nama')->ignore($id),
            ],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array // 🛠️ Ditambahkan 's' di belakang nama method
    {
        return [
            'nama.required' => 'Company Shipping Name is required.',
            'nama.unique' => 'Company Shipping Name already exists.', // 🛠️ Diperbaiki dari 'exsis' menjadi 'exists'
        ];
    }
}
