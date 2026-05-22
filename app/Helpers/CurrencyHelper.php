<?php

use App\Models\General\Company;
use App\Models\General\Currency;
use App\Models\General\ExchangeRate;

if (! function_exists('format_uang')) {
    function format_uang($amount, $decimal = null)
    {
        $currency = session('currency_id')
            ? Currency::find(session('currency_id'))
            : null;

        $symbol = $currency->symbol ?? 'Rp';

        // default decimal per currency
        if ($decimal === null) {
            $decimal = $currency && $currency->code != 'IDR' ? 2 : 0;
        }

        return $symbol.' '.number_format($amount, $decimal, ',', '.');
    }
}
if (! function_exists('format_rate')) {
    function format_rate($rate)
    {
        return number_format($rate, 2, ',', '.');
    }
}

if (! function_exists('convert_currency')) {
    function convert_currency($amount, $fromCurrencyId)
    {
        // 1. Ambil mata uang default perusahaan dari database (Misal: USD)
        $defaultCompanyCurrencyId = Company::first()->default_currency_id ?? 1;

        // 2. Tentukan Mata Uang Target (Pilihan user di navbar / Session)
        $toCurrencyId = session('currency_id') ?? $defaultCompanyCurrencyId;

        // 3. 🔥 UBAH BAGI KODE INI: Paksa asal barang selalu mengikuti mata uang default perusahaan
        // Kita bypass/abaikan $fromCurrencyId yang dikirim oleh database barang
        $fromCurrencyId = $defaultCompanyCurrencyId;

        // Jika mata uang asal sama dengan target mata uang saat ini, tidak perlu konversi
        if ($fromCurrencyId == $toCurrencyId) {
            return $amount;
        }

        // 4. Cek apakah ada rate langsung (Direct Rate)
        $rate = ExchangeRate::where('from_currency_id', $fromCurrencyId)
            ->where('to_currency_id', $toCurrencyId)
            ->orderBy('rate_date', 'desc')
            ->value('rate');

        if ($rate) {
            return $amount / $rate;
        }

        // 5. Cek kebalikannya (Reverse Rate)
        $reverseRate = ExchangeRate::where('from_currency_id', $toCurrencyId)
            ->where('to_currency_id', $fromCurrencyId)
            ->orderBy('rate_date', 'desc')
            ->value('rate');

        if ($reverseRate) {
            return $amount * $reverseRate;
        }

        // 🔥 JIKA SAMPAI DI SINI, ARTINYA RATE DIRECT MAUPUN REVERSE TIDAK DITEMUKAN
        $fromCode = Currency::find($fromCurrencyId)->code ?? 'ID: '.$fromCurrencyId;
        $toCode = Currency::find($toCurrencyId)->code ?? 'ID: '.$toCurrencyId;

        throw new Exception("Exchange Rate untuk konversi dari {$fromCode} ke {$toCode} belum di-input di sistem!");
    }
}
