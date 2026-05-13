<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompanyInfoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

   public function rules(): array
{
    return [
        'nama_perusahaan' => 'required|string|max:150',
        'alamat' => 'nullable|string|max:255',
        'kodepos' => 'nullable|string|max:10',
        'nomor_telepon' => 'nullable|string|max:20',
        'negara' => 'nullable|string|max:100',
        'email' => 'nullable|email|max:100',
        'website' => 'nullable|url|max:150',
        'mata_uang_id' => 'nullable',

        'logo' => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
    ];
}

    public function messages(): array
{
    return [
        'nama_perusahaan.required' => 'Company name is required.',
        'nama_perusahaan.max' => 'Company name may not be greater than 150 characters.',

        'kodepos.max' => 'Postal code may not be greater than 10 characters.',
        'nomor_telepon.max' => 'Phone number may not be greater than 20 characters.',
        'negara.max' => 'Country may not be greater than 100 characters.',

        'email.email' => 'The email format is invalid.',
        'email.max' => 'Email may not be greater than 100 characters.',

        'website.url' => 'The website must be a valid URL.',
        'website.max' => 'Website may not be greater than 150 characters.',

        'mata_uang_id.exists' => 'The selected currency is invalid.',

        'logo.image' => 'The logo must be an image.',
        'logo.mimes' => 'The logo must be a file of type: png, jpg, jpeg, or svg.',
        'logo.max' => 'The logo size may not be greater than 2MB.',
    ];
}
}
