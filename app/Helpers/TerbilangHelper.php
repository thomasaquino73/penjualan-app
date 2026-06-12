<?php

if (! function_exists('terbilang')) {

    function penyebut($nilai)
    {
        $nilai = abs((int) $nilai); // Dipaksa jadi integer
        $huruf = [
            '', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam',
            'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas',
        ];

        if ($nilai < 12) {
            return ' '.$huruf[$nilai];
        }
        if ($nilai < 20) {
            return penyebut($nilai - 10).' Belas';
        }
        if ($nilai < 100) {
            return penyebut($nilai / 10).' Puluh'.penyebut($nilai % 10);
        }
        if ($nilai < 200) {
            return ' Seratus'.penyebut($nilai - 100);
        }
        if ($nilai < 1000) {
            return penyebut($nilai / 100).' Ratus'.penyebut($nilai % 100);
        }
        if ($nilai < 2000) {
            return ' Seribu'.penyebut($nilai - 1000);
        }
        if ($nilai < 1000000) {
            return penyebut($nilai / 1000).' Ribu'.penyebut($nilai % 1000);
        }
        if ($nilai < 1000000000) {
            return penyebut($nilai / 1000000).' Juta'.penyebut($nilai % 1000000);
        }

        return '';
    }

    /**
     * @param  float  $nilai  Nilai total
     * @param  string  $mata_uang  "IDR" atau "USD"
     */
    function terbilang($nilai, $mata_uang = 'IDR')
    {
        $nilai = (float) $nilai;
        $bulat = floor($nilai);
        $sen = round(($nilai - $bulat) * 100);

        $hasil = trim(penyebut($bulat));

        // Menentukan label mata uang
        if ($mata_uang == 'USD') {
            $hasil .= ' Dollar';
            if ($sen > 0) {
                $hasil .= ' '.trim(penyebut($sen)).' Cent';
            }
        } else {
            $hasil .= ' Rupiah';
        }

        return $hasil;
    }
}
