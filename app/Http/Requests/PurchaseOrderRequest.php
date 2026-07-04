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
        $rules = [
            'supplier_id' => 'required',
            'datePO' => 'required|date',
            'tanggal_kirim' => 'nullable|date',
            'description' => 'nullable|string',
            'items_detail' => 'required',
        ];

        if ($this->isMethod('POST')) {
            // Store
            // code tidak divalidasi karena dibuat otomatis
        }

        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {

            $id = $this->route('purchase_order');

            $rules['code'] = [
                'required',
                Rule::unique('purchase_order_'.date('Y'), 'code')->ignore($id),
            ];
        }

        return $rules;
    }

    public function message(): array
    {
        return [
            'supplier_id.required' => 'Customer is required',
            'code.required' => 'Code is required',
            'code.unique' => 'Code has already been taken',
            'datePO.required' => 'Date is required',
            'datePO.date' => 'Date must be a valid date',
            'tanggal_kirim.date' => 'Shipping date must be a valid date',
            'items_detail.required' => 'Items detail is required',
        ];
    }
}
