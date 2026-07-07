<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BankListRequest extends FormRequest
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
            'bank_name' => [
                'required',
                Rule::unique('bank_list', 'bank_name')->ignore($id),
            ],
            'account_name' => 'required',
            'account_number' => 'required',

        ];
    }

    public function message(): array
    {
        return [
            'bank_name.required' => 'Bank Name is required',
            'bank_name.unique' => 'Bank Name has already been taken',
            'account_number.required' => 'Account Number is required',
            'account_number.unique' => 'Account Number has already been taken',
            'account_name.required' => 'Account Name is required',
        ];
    }
}
