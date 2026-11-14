<?php

namespace App\Services\Accounts;

use App\Models\Admin;
use App\Models\Branch;
use App\Models\CashClosing;
use App\Models\CommissionPayout;
use App\Models\Expense;
use App\Models\Payment;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

/**
 * End-of-day cash for one staff member:
 *   expected cash = opening + cash received − cash refunded − cash expenses − commission paid in cash
 * Money received by bKash, Nagad, card… is listed separately (it is not in the drawer).
 */
class CashClosingService
{
    public function summary(Admin $admin, Carbon $date): array
    {
        $day = [$date->copy()->startOfDay(), $date->copy()->endOfDay()];
        $payments = Payment::query()->where('received_by', $admin->id)->whereBetween('created_at', $day)->get(['type', 'method', 'amount']);

        $cashIn = (float) $payments->where('method', 'cash')->where('type', 'payment')->sum('amount');
        $cashOut = (float) $payments->where('method', 'cash')->where('type', 'refund')->sum('amount');
        $expenses = (float) Expense::query()->where('created_by', $admin->id)->where('method', 'cash')->whereDate('expense_date', $date)->sum('amount');
        $commission = (float) CommissionPayout::query()->where('paid_by', $admin->id)->where('method', 'cash')->whereBetween('created_at', $day)->sum('amount');

        $nonCash = $payments->where('method', '!=', 'cash')->groupBy('method')
            ->map(fn ($rows) => round((float) $rows->where('type', 'payment')->sum('amount') - (float) $rows->where('type', 'refund')->sum('amount'), 2))
            ->filter()->all();

        return [
            'cash_received' => round($cashIn, 2),
            'cash_refunded' => round($cashOut, 2),
            'cash_expenses' => round($expenses, 2),
            'commission_paid_cash' => round($commission, 2),
            'non_cash' => $nonCash,
            'transactions' => $payments->count(),
        ];
    }

    public function close(Admin $admin, Carbon $date, float $opening, float $counted, ?string $note): CashClosing
    {
        if (CashClosing::where('admin_id', $admin->id)->whereDate('closing_date', $date)->exists()) {
            throw ValidationException::withMessages(['counted_cash' => 'This day is already closed for you.']);
        }
        if ($date->isFuture()) {
            throw ValidationException::withMessages(['closing_date' => 'You cannot close a future day.']);
        }

        $s = $this->summary($admin, $date);
        $expected = round($opening + $s['cash_received'] - $s['cash_refunded'] - $s['cash_expenses'] - $s['commission_paid_cash'], 2);

        return CashClosing::create([
            'branch_id' => $admin->branch_id ?? Branch::orderByDesc('is_main')->value('id'),
            'admin_id' => $admin->id,
            'closing_date' => $date->toDateString(),
            'opening_cash' => $opening,
            'cash_received' => $s['cash_received'],
            'cash_refunded' => $s['cash_refunded'],
            'cash_expenses' => $s['cash_expenses'],
            'commission_paid_cash' => $s['commission_paid_cash'],
            'expected_cash' => $expected,
            'counted_cash' => $counted,
            'difference' => round($counted - $expected, 2),
            'non_cash' => $s['non_cash'],
            'note' => $note,
        ]);
    }

    public function verify(CashClosing $closing, Admin $by): void
    {
        if ($closing->admin_id === $by->id) {
            throw ValidationException::withMessages(['closing' => 'Someone else must check your own closing.']);
        }
        $closing->update(['status' => 'verified', 'verified_by' => $by->id, 'verified_at' => now()]);
    }
}
