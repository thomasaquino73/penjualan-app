<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExchangeRateRequest extends FormRequest
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
        $id = $this->input('id');

        return [
            'from_currency_id' => [
                'required',
                'exists:currencies,id',
            ],
            'to_currency_id' => [
                'required',
                'exists:currencies,id',
                'different:from_currency_id',
            ],
            'rate' => 'required|numeric|min:0',
            'rate_date' => [
                'required',
                'date',
                Rule::unique('exchange_rates')
                    ->where(function ($query) {
                        return $query->where('from_currency_id', $this->from_currency_id)
                            ->where('to_currency_id', $this->to_currency_id)
                            ->where('rate_date', $this->rate_date);
                    })
                    ->ignore($id),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'from_currency_id.required' => 'From currency is required',
            'from_currency_id.exists' => 'From currency not valid',

            'to_currency_id.required' => 'To currency is required',
            'to_currency_id.exists' => 'To currency not valid',
            'to_currency_id.different' => 'From and To currency cannot be the same',

            'rate.required' => 'Rate is required',
            'rate.numeric' => 'Rate must be a number',
            'rate.min' => 'Rate must be greater than 0',

            'rate_date.required' => 'Rate date is required',
            'rate_date.date' => 'Invalid date format',

            'rate_date.unique' => 'Exchange rate already exists for this date and currency pair',
        ];
    }
}
