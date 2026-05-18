<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseOrderRequest extends FormRequest
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

        return [
            'supplier_id' => 'required',
            'code' => 'required|unique:purchase_order,code',
            'date' => 'required|date',
            'expected_date' => 'nullable|date',
            'fob_id' => 'required',
            'term' => 'required',
            'description' => 'nullable|string',
            'items_detail' => 'required',
        ];
    }

    public function message(): array
    {
        return [
            'supplier_id.required' => 'Customer is required',
            'code.required' => 'Code is required',
            'code.unique' => 'Code has already been taken',
            'date.required' => 'Date is required',
            'date.date' => 'Date must be a valid date',
            'expected_date.date' => 'Expected date must be a valid date',
            'fob_id.required' => 'FOB is required',
            'term.required' => 'Term is required',
            'items_detail.required' => 'Items detail is required',
        ];
    }
}
