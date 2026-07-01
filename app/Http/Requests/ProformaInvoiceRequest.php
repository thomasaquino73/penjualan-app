<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProformaInvoiceRequest extends FormRequest
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
        $id = $this->route('proforma_invoice');

        return [
            'proforma_invoice_code' => [
                'required',
                Rule::unique('proforma_invoice_'.date('Y'), 'proforma_invoice_code')->ignore($id),
            ],
            'customer_id' => 'required',
            'proforma_invoice_date' => 'required|date',
            'payment_term_id' => 'required',
            // 'customer_contact_id' => 'required',
            'address' => 'required',
            'description' => 'nullable|string',
            'items_detail' => 'required',
        ];
    }

    public function message(): array
    {
        return [
            'customer_id.required' => 'Customer is required',
            'proforma_invoice_code.required' => 'Code is required',
            'proforma_invoice_code.unique' => 'Code has already been taken',
            'proforma_invoice_date.required' => 'Date is required',
            'proforma_invoice_date.date' => 'Date must be a valid date',
            'payment_term_id.required' => 'Payment term is required',
            // 'customer_contact_id.required' => 'Customer contact is required',
            'address.required' => 'Address is required',
            'items_detail.required' => 'Items detail is required',
        ];
    }
}
