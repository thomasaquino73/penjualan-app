<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SistemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_aplikasi' => 'required|string|max:100',
            
        ];
    }

    public function messages(): array
    {
        return [
            'nama_aplikasi.required' => 'Nama aplikasi wajib diisi.',
            'nama_aplikasi.max' => 'Nama aplikasi maksimal 100 karakter.',

           
        ];
    }
}
