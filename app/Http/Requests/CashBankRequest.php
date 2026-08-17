<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CashBankRequest extends FormRequest
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
            'account_name' => [
                'required',
                Rule::unique('cash_banks', 'account_name')->ignore($id),
            ],

        ];
    }

    public function message(): array
    {
        return [
            'account_name.required' => 'Account Name is required',
        ];
    }
}
