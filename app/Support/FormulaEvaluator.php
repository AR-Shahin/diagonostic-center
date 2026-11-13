<?php

namespace App\Support;

use InvalidArgumentException;

/**
 * Evaluates result formulas such as "TC - HDL - TG / 5" without eval().
 * Supports numbers, parameter codes, + - * / and parentheses (shunting-yard).
 */
class FormulaEvaluator
{
    /**
     * @param  array<string, float|int|string|null>  $values  code => value
     * @return float|null null when a needed value is missing or the formula divides by zero
     */
    public static function evaluate(string $formula, array $values): ?float
    {
        $values = array_change_key_case($values, CASE_UPPER);
        $output = [];
        $ops = [];
        $prec = ['+' => 1, '-' => 1, '*' => 2, '/' => 2];
        $prevWasOperand = false;

        preg_match_all('/\d+(?:\.\d+)?|[A-Za-z_][A-Za-z0-9_]*|[+\-*\/()]/', $formula, $m);
        $joined = implode('', $m[0]);
        if ($joined !== preg_replace('/\s+/', '', $formula)) {
            throw new InvalidArgumentException("Formula has characters that are not allowed: {$formula}");
        }

        foreach ($m[0] as $token) {
            if (is_numeric($token)) {
                $output[] = (float) $token;
                $prevWasOperand = true;
            } elseif (preg_match('/^[A-Za-z_]/', $token)) {
                $v = $values[strtoupper($token)] ?? null;
                if ($v === null || $v === '' || ! is_numeric($v)) {
                    return null;
                }
                $output[] = (float) $v;
                $prevWasOperand = true;
            } elseif ($token === '(') {
                $ops[] = $token;
                $prevWasOperand = false;
            } elseif ($token === ')') {
                while ($ops && end($ops) !== '(') {
                    $output[] = array_pop($ops);
                }
                array_pop($ops);
                $prevWasOperand = true;
            } else {
                if (! $prevWasOperand && $token === '-') {
                    $output[] = 0.0; // unary minus: 0 - x
                }
                while ($ops && end($ops) !== '(' && $prec[end($ops)] >= $prec[$token]) {
                    $output[] = array_pop($ops);
                }
                $ops[] = $token;
                $prevWasOperand = false;
            }
        }
        while ($ops) {
            $output[] = array_pop($ops);
        }

        $stack = [];
        foreach ($output as $item) {
            if (is_float($item)) {
                $stack[] = $item;

                continue;
            }
            $b = array_pop($stack);
            $a = array_pop($stack);
            if ($a === null || $b === null) {
                return null;
            }
            if ($item === '/' && $b == 0.0) {
                return null;
            }
            $stack[] = match ($item) {
                '+' => $a + $b, '-' => $a - $b, '*' => $a * $b, '/' => $a / $b,
            };
        }

        return count($stack) === 1 ? $stack[0] : null;
    }
}
