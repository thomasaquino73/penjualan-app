<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PurchaseDownPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'supplier_id' => 'required|integer',
            'purchase_downpayment_date' => 'required|date',
            'bank_id' => 'nullable|integer',
            'address' => 'required|string',
            'description' => 'nullable|string',
        ];

        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {

            $id = $this->route('purchase_down_payment');

            $rules['purchase_downpayment_code'] = [
                'required',
                Rule::unique(
                    'purchase_down_payments_'.date('Y'),
                    'purchase_downpayment_code'
                )->ignore($id),
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'supplier_id.required' => 'Please select a supplier.',

            'supplier_id.integer' => 'The selected supplier is invalid.',

            'purchase_downpayment_date.required' => 'Please enter the down payment date.',

            'purchase_downpayment_date.date' => 'The down payment date must be a valid date.',

            'bank_id.required' => 'Please select bank account.',

            'bank_id.integer' => 'The selected bank account is invalid.',

            // 'address.required' => 'Please enter the supplier address.',

            'address.string' => 'The supplier address must be a valid text.',

            'description.string' => 'The description must be a valid text.',

            'purchase_downpayment_code.required' => 'Please enter the down payment number.',

            'purchase_downpayment_code.unique' => 'The down payment number has already been taken.',
        ];
    }
}
