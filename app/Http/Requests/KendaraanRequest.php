<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KendaraanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
            'merk' => 'required|string|max:255',
            'tipe' => 'required|string|max:255',
            'warna' => 'required|string|max:255',
            'pemilik' => 'required|string|max:255',
            'plat_depan' => 'required|string|max:2',
            'plat_tengah' => 'required|string|max:4',
            'plat_belakang' => 'required|string|max:3',
            // validasi unik gabungan plat
            'plat_nomor' => [
                'required',
                Rule::unique('daftar_kendaraan', 'plat_nomor')
                    ->ignore($this->id)
                    ->where(fn ($query) => $query->where('status', 1)),
            ],
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->filled('plat_depan') && $this->filled('plat_tengah') && $this->filled('plat_belakang')) {
            $this->merge([
                'plat_nomor' => strtoupper(
                    trim($this->plat_depan).' '.
                    trim($this->plat_tengah).' '.
                    trim($this->plat_belakang)
                ),
            ]);
        }
    }
}
