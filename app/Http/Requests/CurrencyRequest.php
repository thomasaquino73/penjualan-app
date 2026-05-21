<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CurrencyRequest extends FormRequest
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
            'code' => [
                'required',
                Rule::unique('currencies', 'code')->ignore($id),
            ],
            'name' => 'required|string|max:255',
            'symbol' => 'required|string|max:255',

        ];
    }

    public function message(): array
    {
        return [
            'code.required' => 'Code is required',
            'code.unique' => 'Code has already been taken',
            'name.required' => 'Name is required',
            'name.string' => 'Name must be a string',
            'name.max' => 'Name must not exceed 255 characters',
            'symbol.required' => 'Symbol is required',
            'symbol.string' => 'Symbol must be a string',
            'symbol.max' => 'Symbol must not exceed 255 characters',
        ];
    }
}
