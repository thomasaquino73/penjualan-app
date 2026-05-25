<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SyaratPembayaranRequest extends FormRequest
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
            'nama' => [
                'required',
                Rule::unique('syarat_pembayaran', 'nama')->ignore($id, 'id'),
            ],
            'diskon' => 'nullable',
            'status' => 'required',

        ];
    }

    public function message(): array
    {
        return [
            'nama.required' => ' Name is required',
            'nama.unique' => ' Name already exists',
            'status.required' => ' status is required',
        ];
    }
    protected function prepareForValidation()
    {
        $this->merge([
            'status' => $this->status ?? 1,
        ]);
    }
}
