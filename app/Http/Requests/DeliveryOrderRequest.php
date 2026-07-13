<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeliveryOrderRequest extends FormRequest
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

        $rules = [
            'delivery_order_date' => 'required|date',
            'customer_id' => 'required',
            'items_detail' => 'required',

        ];
        if ($this->isMethod('POST')) {
            // Store
            // code tidak divalidasi karena dibuat otomatis
        }

        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {

            $id = $this->route('delivery_order');

            $rules['delivery_order_code'] = [
                'required',
                Rule::unique('delivery_order_'.date('Y'), 'delivery_order_code')->ignore($id),
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'delivery_order_code.required' => 'Delivery order code is required.',
            'delivery_order_code.unique' => 'Delivery order code already exists.',

            'delivery_order_date.required' => 'Delivery order date is required.',
            'delivery_order_date.date' => 'Invalid delivery order date.',

            'customer_id.required' => 'Please select a customer.',

            'items_detail.required' => 'Please add at least one item.',
            'items_detail.array' => 'Invalid item details.',
            'items_detail.min' => 'Please add at least one item.',
        ];
    }
}
