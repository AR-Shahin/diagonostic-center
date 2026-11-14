<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\CrudHelpers;
use App\Http\Controllers\Controller;
use App\Models\CashClosing;
use App\Models\CommissionEntry;
use App\Models\InvoiceItem;
use App\Services\Reports\ReportService;
use App\Support\BranchAccess;

class DashboardController extends Controller
{
    use CrudHelpers;

    public function index(ReportService $reports)
    {
        $admin = auth('admin')->user();
        $branchId = BranchAccess::isRestricted($admin) ? $admin->branch_id : null;
        $data = ['money' => null, 'lab' => null, 'alerts' => []];

        if ($this->can('invoice-view')) {
            $data['money'] = $reports->dashboard($branchId);
        }

        if ($this->can('lab-view')) {
            $items = InvoiceItem::query()->whereIn('item_type', ['test', 'package_test'])
                ->whereHas('invoice', fn ($q) => $q->where('status', '!=', 'cancelled')->when($branchId, fn ($w, $id) => $w->where('branch_id', $id)));
            $counts = (clone $items)->whereIn('lab_status', ['pending', 'collected', 'processing', 'ready'])
                ->selectRaw('lab_status, COUNT(*) c')->groupBy('lab_status')->pluck('c', 'lab_status');
            $late = (clone $items)->whereIn('lab_status', ['pending', 'collected', 'processing'])->where('delivery_at', '<', now());
            $data['lab'] = [
                'counts' => $counts,
                'lateCount' => (clone $late)->count(),
                'late' => $late->with('invoice:id,invoice_no,patient_name')->orderBy('delivery_at')->limit(8)->get(),
            ];
        }

        if ($this->can('cashclosing-verify')) {
            $waiting = BranchAccess::scope(CashClosing::query(), $admin)->where('status', 'submitted')->where('admin_id', '!=', $admin->id)->count();
            if ($waiting) {
                $data['alerts'][] = ['warning', 'fas fa-cash-register', $waiting.' cash closing(s) waiting for your check', route('admin.cash-closings.index')];
            }
        }
        if ($this->can('commission-view')) {
            $owed = (float) CommissionEntry::whereNull('payout_id')->sum('amount');
            if ($owed > 0) {
                $data['alerts'][] = ['info', 'fas fa-hand-holding-usd', 'Referral commission owed: ৳'.number_format($owed), route('admin.commission.index')];
            }
        }

        return view('admin.dashboard', $data + ['canReports' => $this->can('report-view')]);
    }
}
