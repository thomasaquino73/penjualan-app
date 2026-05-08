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
            'nama_instansi' => 'required|string|max:150',
            'alamat' => 'nullable|string|max:255',
            'telepon' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'website' => 'nullable|url|max:150',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
            'favicon' => 'nullable|image|mimes:png,ico|max:1024',
            'deskripsi' => 'nullable|string',
            'tahun_berdiri' => 'nullable|digits:4|integer',
        ];
    }

    public function messages(): array
    {
        return [
            'nama_aplikasi.required' => 'Nama aplikasi wajib diisi.',
            'nama_aplikasi.max' => 'Nama aplikasi maksimal 100 karakter.',

            'nama_instansi.required' => 'Nama instansi wajib diisi.',
            'nama_instansi.max' => 'Nama instansi maksimal 150 karakter.',

            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal 100 karakter.',

            'website.url' => 'Format website harus berupa URL yang valid.',
            'website.max' => 'Website maksimal 150 karakter.',

            'logo.image' => 'Logo harus berupa gambar.',
            'logo.mimes' => 'Logo harus berformat png, jpg, jpeg, atau svg.',
            'logo.max' => 'Ukuran logo maksimal 2MB.',

            'favicon.image' => 'Favicon harus berupa gambar.',
            'favicon.mimes' => 'Favicon harus berformat png atau ico.',
            'favicon.max' => 'Ukuran favicon maksimal 1MB.',

            'tahun_berdiri.digits' => 'Tahun berdiri harus 4 digit.',
            'tahun_berdiri.integer' => 'Tahun berdiri harus berupa angka.',
        ];
    }
}
