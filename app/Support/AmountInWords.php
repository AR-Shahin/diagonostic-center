<?php

namespace App\Support;

/**
 * "4500.50" → "Four Thousand Five Hundred Taka and Fifty Paisa Only" (lakh / crore system, as used in Bangladesh).
 */
class AmountInWords
{
    private const ONES = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve',
        'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];

    private const TENS = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

    public static function taka(float $amount): string
    {
        $amount = round(abs($amount), 2);
        $taka = (int) floor($amount);
        $paisa = (int) round(($amount - $taka) * 100);

        $words = $taka === 0 ? 'Zero' : self::number($taka);
        $text = $words.' Taka';
        if ($paisa > 0) {
            $text .= ' and '.self::number($paisa).' Paisa';
        }

        return $text.' Only';
    }

    private static function number(int $n): string
    {
        $parts = [];
        foreach ([[10000000, 'Crore'], [100000, 'Lakh'], [1000, 'Thousand'], [100, 'Hundred']] as [$size, $label]) {
            if ($n >= $size) {
                $parts[] = self::number(intdiv($n, $size)).' '.$label;
                $n %= $size;
            }
        }
        if ($n > 0) {
            $parts[] = $n < 20 ? self::ONES[$n] : trim(self::TENS[intdiv($n, 10)].' '.self::ONES[$n % 10]);
        }

        return implode(' ', $parts);
    }
}
