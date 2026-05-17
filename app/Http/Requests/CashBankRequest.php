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
            'name' => [
                'required',
                Rule::unique('cash_bank', 'name')->ignore($id),
            ],
            'currency_id' => 'required',

        ];
    }

    public function message(): array
    {
        return [
            'name.required' => 'Name is required',
            'name.unique' => 'Name has already been taken',
            'currency_id.required' => 'Currency is required',
        ];
    }
}
