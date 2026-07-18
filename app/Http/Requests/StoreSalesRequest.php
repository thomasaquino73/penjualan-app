<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSalesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules.
     */
    public function rules(): array
    {
        $id = $this->route('penjualan_toko');

        $rules = [
            'store_sales_date' => 'required|date',
            'customer_name' => 'required|string|max:255',
            'sub_total' => 'nullable|numeric',
            'disc_nominal' => 'nullable|numeric',
            'tax_id' => 'nullable|integer',
            'tax_percent' => 'nullable|numeric',
            'tax_amount' => 'nullable|numeric',
            'grand_total' => 'nullable|numeric',
            'amount_receive' => 'nullable|numeric',
            'change' => 'nullable|numeric',
            'bank_list_id' => 'nullable|integer',
            'payment_method' => 'required|in:Cash,Transfer,Qris',
            'shipping_method' => 'required|in:Pick Up,Delivery',
            'notes' => 'nullable|string',
            'items_detail' => 'required',

        ];

       if ($this->isMethod('POST')) {
        
        }

        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {

            $id = $this->route('penjualan_toko');

            $rules['store_sales_code'] = [
                'required',
                Rule::unique('store_sales_'.date('Y'), 'store_sales_code')->ignore($id),
            ];
        }

        return $rules;
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [

            'store_sales_code.required' => 'Kode transaksi wajib diisi.',

            'store_sales_date.required' => 'Tanggal transaksi wajib diisi.',

            'customer_name.required' => 'Nama customer wajib diisi.',

            'payment_method.required' => 'Metode pembayaran wajib dipilih.',

            'shipping_method.required' => 'Metode pengiriman wajib dipilih.',

            'items_detail.required' => 'Minimal harus ada satu item.',

        ];
    }
}
