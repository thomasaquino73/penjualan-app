<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SalesDownPaymentRequest extends FormRequest
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
    public function rules(): array
    {
        $rules = [
            'customer_id' => 'required|integer',
            'sales_downpayment_date' => 'required|date',
            'payment_term_id' => 'required|integer',
            'address' => 'required|string',
            'description' => 'nullable|string',
        ];

        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {

            $id = $this->route('sales-down-payment');

            $rules['sales_downpayment_code'] = [
                'required',
                Rule::unique(
                    'sales_down_payments_'.date('Y'),
                    'sales_downpayment_code'
                )->ignore($id),
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'customer_id.required' => 'Please select a customer.',

            'customer_id.integer' => 'The selected customer is invalid.',

            'sales_downpayment_date.required' => 'Please enter the down payment date.',

            'sales_downpayment_date.date' => 'The down payment date must be a valid date.',

            'payment_term_id.required' => 'Please select a payment term.',

            'payment_term_id.integer' => 'The selected payment term is invalid.',

            'address.required' => 'Please enter the customer address.',

            'address.string' => 'The customer address must be a valid text.',

            'description.string' => 'The description must be a valid text.',

            'sales_downpayment_code.required' => 'Please enter the down payment number.',

            'sales_downpayment_code.unique' => 'The down payment number has already been taken.',
        ];
    }
}
