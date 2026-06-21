<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ItemTransferRequest extends FormRequest
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
    public function rules()
    {
        $id = $this->route('item_transfer');

        return [
            'transfer_code' => [
                'required',
                Rule::unique('item_transfer', 'transfer_code')->ignore($id, 'id'),
            ],
            'transfer_date' => 'required|date',
            'from_warehouse_id' => 'required',
            'to_warehouse_id' => 'required',
            'items_detail' => 'required',

        ];
    }

    public function messages()
    {
        return [
            'transfer_code.required' => 'The Item Transfer Code is required.',
            'transfer_code.unique' => 'This Item Transfer Code has already been taken.',

            'transfer_date.required' => 'The Item Transfer Date is required.',
            'transfer_date.date' => 'The Item Transfer Date must be a valid date.',

            'from_warehouse_id.required' => 'Please select the source warehouse.',
            'to_warehouse_id.required' => 'Please select the destination warehouse.',

            'items_detail.required' => 'Please add at least one item detail to the table.',
        ];
    }
}
