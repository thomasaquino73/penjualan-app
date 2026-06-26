<?php

class TaxService
{
    public function calculate($subtotal, $tax)
    {
        if (! $tax) {
            return 0;
        }

        return ($subtotal * $tax->percentage) / 100;
    }

    public function calculateInclusive($total, $tax)
    {
        if (! $tax) {
            return 0;
        }

        return ($total * $tax->percentage) / (100 + $tax->percentage);
    }
}
