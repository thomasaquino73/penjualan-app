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

        $rules = [
            // 'receive_item_code' => [
            //     'required',
            //     Rule::unique('receive_item_'.date('Y'), 'receive_item_code')->ignore($id),
            // ],
            'supplier_id' => 'required',
            'receive_item_date' => 'required|date',
            'no_dokumen' => 'required|string',
            'tanggal_kirim' => 'required|date',
            // 'shiping_id' => 'required',
            'description' => 'nullable|string',
            'items_detail' => 'required',
        ];

        if ($this->isMethod('POST')) {
            // Store
            // code tidak divalidasi karena dibuat otomatis
        }

        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {

            $id = $this->route('receive_item');

            $rules['receive_item_code'] = [
                'required',
                Rule::unique('receive_item_'.date('Y'), 'receive_item_code')->ignore($id),
            ];
        }

        return $rules;
    }

    public function message(): array
    {
        return [
            'supplier_id.required' => 'Supplier is required',
            'receive_item_code.required' => 'RI Number is required',
            'receive_item_code.unique' => 'RI Number has already been taken',
            'receive_item_date.required' => 'Date is required',
            'receive_item_date.date' => 'Date must be a valid date',
            'tanggal_kirim.required' => 'Shipping date is required',
            'tanggal_kirim.date' => 'Shipping date must be a valid date',
            // 'shiping_id.required' => 'Shipping is required',
            'items_detail.required' => 'Items detail is required',
            'no_dokumen.required' => 'Document Number is required',
        ];
    }
}
