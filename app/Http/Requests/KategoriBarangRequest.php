<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KategoriBarangRequest extends FormRequest
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
        $id = $this->input('id');

        return [
            'detail' => [
                'required',
                Rule::unique('basic_code_detail', 'detail')->ignore($id, 'id'),
            ],
            'description' => [
                'nullable',
            ],

        ];
    }

    public function message(): array
    {
        return [
            'detail.required' => 'Categories Name is required',
            'detail.unique' => 'Categories Name already exists',
        ];
    }
}
