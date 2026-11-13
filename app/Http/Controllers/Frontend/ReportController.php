<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Admin\LabController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Portal\Concerns\RendersSitePage;
use App\Models\Invoice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Public report access:
 *  - invoice number + report PIN (printed on the invoice) → printable report
 *  - QR code on the report → "is this report genuine?" page (shows no medical values)
 */
class ReportController extends Controller
{
    use RendersSitePage;

    private const SESSION_KEY = 'report_access';

    public function lookup(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'invoice' => ['required', 'string', 'max:40'],
            'pin' => ['required', 'string', 'max:10'],
        ]);

        $invoice = Invoice::where('invoice_no', strtoupper(trim($data['invoice'])))->first();
        if (! $invoice || ! hash_equals((string) $invoice->report_pin, trim($data['pin']))) {
            return back()->withInput($request->only('invoice'))->withErrors(['invoice' => __('ইনভয়েস নম্বর বা পিন সঠিক নয়।')]);
        }
        if ($invoice->isCancelled()) {
            return back()->withInput($request->only('invoice'))->withErrors(['invoice' => __('এই বিলটি বাতিল করা হয়েছে।')]);
        }

        $request->session()->put(self::SESSION_KEY, array_unique([...$request->session()->get(self::SESSION_KEY, []), $invoice->id]));

        return redirect()->route('report.show', $invoice);
    }

    public function show(Request $request, Invoice $invoice)
    {
        abort_unless(in_array($invoice->id, $request->session()->get(self::SESSION_KEY, []), true), 403);

        if (! $invoice->canReleaseReport(portal: true)) {
            return redirect()->route('site.report')->withErrors(['invoice' => __('বকেয়া পরিশোধের পর রিপোর্ট দেখা যাবে।')]);
        }
        if ($invoice->reportableItems()->doesntExist()) {
            return redirect()->route('site.report')->withErrors(['invoice' => __('রিপোর্ট এখনো প্রস্তুত হয়নি। প্রস্তুত হলে এসএমএস পাবেন।')]);
        }

        return view('reports.document', LabController::reportData($invoice, [], ['backUrl' => route('site.report')]));
    }

    public function verify(string $token)
    {
        $invoice = Invoice::where('report_token', $token)->with(['labItems.result.verifiedBy:id,name,qualification', 'branch'])->first();

        return $this->page('frontend.pages.report-verify', ['invoice' => $invoice]);
    }
}
