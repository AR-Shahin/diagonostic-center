<?php

namespace App\Services\Billing;

use App\Models\Branch;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

/**
 * Running numbers that never repeat: INV-MAIN-2611-00001, MR-MAIN-2611-00001, P-000001.
 * Must be called inside a database transaction (the sequence row is locked).
 */
class NumberGenerator
{
    public static function next(string $key): int
    {
        DB::table('number_sequences')->insertOrIgnore(['key' => $key, 'last_number' => 0, 'created_at' => now(), 'updated_at' => now()]);
        $row = DB::table('number_sequences')->where('key', $key)->lockForUpdate()->first();
        $next = $row->last_number + 1;
        DB::table('number_sequences')->where('id', $row->id)->update(['last_number' => $next, 'updated_at' => now()]);

        return $next;
    }

    public static function invoiceNo(Branch $branch): string
    {
        $prefix = strtoupper(Setting::get('invoice_prefix', 'INV')).'-'.$branch->code.'-'.now()->format('ym');

        return $prefix.'-'.str_pad((string) self::next($prefix), 5, '0', STR_PAD_LEFT);
    }

    public static function receiptNo(Branch $branch): string
    {
        $prefix = strtoupper(Setting::get('receipt_prefix', 'MR')).'-'.$branch->code.'-'.now()->format('ym');

        return $prefix.'-'.str_pad((string) self::next($prefix), 5, '0', STR_PAD_LEFT);
    }

    public static function patientCode(): string
    {
        return 'P-'.str_pad((string) self::next('PATIENT'), 6, '0', STR_PAD_LEFT);
    }

    public static function referrerCode(): string
    {
        return 'R-'.str_pad((string) self::next('REFERRER'), 4, '0', STR_PAD_LEFT);
    }
}
