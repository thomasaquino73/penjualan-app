<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReceiveItemRequest extends FormRequest
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
        $id = $this->route('receive_item');

        return [
            'receive_item_code' => [
                'required',
                Rule::unique('receive_item_'.date('Y'), 'receive_item_code')->ignore($id),
            ],
            'supplier_id' => 'required',
            'receive_item_date' => 'required|date',
            'no_dokumen' => 'required|string',
            'tanggal_kirim' => 'nullable|date',
            'description' => 'nullable|string',
            'items_detail' => 'required',
        ];
    }

    public function message(): array
    {
        return [
            'supplier_id.required' => 'Customer is required',
            'receive_item_code.required' => 'RI Number is required',
            'receive_item_code.unique' => 'RI Number has already been taken',
            'receive_item_date.required' => 'Date is required',
            'receive_item_date.date' => 'Date must be a valid date',
            'tanggal_kirim.date' => 'Shipping date must be a valid date',
            'items_detail.required' => 'Items detail is required',
            'no_dokumen.required' => 'Document Number is required',
        ];
    }
}
