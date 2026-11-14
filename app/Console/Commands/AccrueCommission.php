<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Services\Accounts\CommissionService;
use Illuminate\Console\Command;

/**
 * Writes commission for bills that should have earned it but have no entries yet
 * (e.g. bills made before rates were set up). Safe to run again.
 */
class AccrueCommission extends Command
{
    protected $signature = 'commission:accrue {--from= : Only bills on or after this date (Y-m-d)}';

    protected $description = 'Calculate referral commission for eligible bills that have none yet';

    public function handle(CommissionService $commission): int
    {
        $count = 0;
        Invoice::query()->whereNotNull('referrer_id')->where('status', '!=', 'cancelled')
            ->when($this->option('from'), fn ($q, $from) => $q->whereDate('created_at', '>=', $from))
            ->whereDoesntHave('commissionEntries')
            ->chunkById(200, function ($invoices) use ($commission, &$count) {
                foreach ($invoices as $invoice) {
                    $count += $commission->accrueIfDue($invoice) > 0 ? 1 : 0;
                }
            });

        $this->info("Commission written for {$count} bill(s).");

        return self::SUCCESS;
    }
}
