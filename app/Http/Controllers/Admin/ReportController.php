<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\CrudHelpers;
use App\Http\Controllers\Controller;
use App\Services\Reports\ReportService;
use App\Support\BranchAccess;
use App\Support\WebsiteIdentity;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    use CrudHelpers;

    public function __construct(private ReportService $reports)
    {
        parent::__construct();
    }

    public function index(Request $request, string $type = 'summary')
    {
        [$report, $filters] = $this->make($request, $type);

        return view($request->boolean('print') ? 'admin.reports.print' : 'admin.reports.index', $filters + [
            'type' => $type,
            'report' => $report,
            'types' => collect(ReportService::TYPES)->filter(fn ($t) => $this->can($t[2])),
            'site' => WebsiteIdentity::get(),
        ]);
    }

    /** Excel opens this directly; the BOM keeps Bangla text readable. */
    public function export(Request $request, string $type = 'summary'): StreamedResponse
    {
        [$report, $f] = $this->make($request, $type);
        $name = str($report['title'])->slug().'-'.$f['from']->format('Ymd').'-'.$f['to']->format('Ymd').'.csv';

        return response()->streamDownload(function () use ($report, $f) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, [$report['title'], $f['from']->format('d M Y').' – '.$f['to']->format('d M Y'), $f['branchName']]);
            foreach ($report['cards'] as $card) {
                fputcsv($out, [$card[0], str_starts_with($card[1], '৳') ? (float) str_replace(['৳', ' ', ','], '', $card[1]) : $card[1], $card[2] ?? '']);
            }
            fputcsv($out, []);
            fputcsv($out, array_map(fn ($c) => $c[0], $report['columns']));
            foreach ($report['rows'] as $row) {
                fputcsv($out, array_map(fn ($key) => $row[$key] ?? '', array_keys($report['columns'])));
            }
            if ($report['totals']) {
                fputcsv($out, array_map(fn ($key) => $report['totals'][$key] ?? '', array_keys($report['columns'])));
            }
            fclose($out);
        }, $name, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function make(Request $request, string $type): array
    {
        abort_unless(isset(ReportService::TYPES[$type]), 404);
        $this->allow(ReportService::TYPES[$type][2]);

        $admin = auth('admin')->user();
        $from = Carbon::parse($request->input('from', now()->startOfMonth()->toDateString()));
        $to = Carbon::parse($request->input('to', now()->toDateString()));
        if ($from->gt($to)) {
            [$from, $to] = [$to, $from];
        }
        $branches = BranchAccess::branches($admin);
        $branchId = BranchAccess::isRestricted($admin) ? $admin->branch_id : ($request->integer('branch_id') ?: null);

        return [
            $this->reports->for($from, $to, $branchId)->build($type),
            ['from' => $from, 'to' => $to, 'branchId' => $branchId, 'branches' => $branches,
                'branchName' => $branchId ? ($branches->firstWhere('id', $branchId)->name_en ?? '') : 'All branches'],
        ];
    }
}
