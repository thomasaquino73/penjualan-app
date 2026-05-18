<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        $id = $this->route('purchase_order');

        return [
            'code' => [
                'required',
                Rule::unique('purchase_order_'.date('Y'), 'code')->ignore($id),
            ],
            'supplier_id' => 'required',
            'date' => 'required|date',
            'expected_date' => 'nullable|date',
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
            'items_detail.required' => 'Items detail is required',
        ];
    }
}
