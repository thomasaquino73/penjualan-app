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
            'name' => [
                'required',
                Rule::unique('currency', 'name')->ignore($id),
            ],
            'rate' => 'required|numeric|min:0',
            'country' => 'required|string|max:255',
            'symbol' => 'required|string|max:255',

        ];
    }

    public function message(): array
    {
        return [
            'name.required' => 'Name is required',
            'name.unique' => 'Name has already been taken',
            'rate.required' => 'Rate is required',
            'rate.numeric' => 'Rate must be a number',
            'rate.min' => 'Rate must be a positive number',
            'country.required' => 'Country is required',
            'country.string' => 'Country must be a string',
            'country.max' => 'Country must not exceed 255 characters',
            'symbol.required' => 'Symbol is required',
            'symbol.string' => 'Symbol must be a string',
            'symbol.max' => 'Symbol must not exceed 255 characters',
        ];
    }
}
