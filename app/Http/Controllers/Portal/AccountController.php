<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Portal\Concerns\RendersSitePage;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AccountController extends Controller
{
    use RendersSitePage;

    public function dashboard(Request $request)
    {
        /** @var User $user */
        $user = $request->user('web');
        $patients = $user->patients()->get();
        $invoices = Invoice::query()->whereIn('patient_id', $patients->pluck('id'))
            ->with(['branch:id,name_en,name_bn', 'labItems:id,invoice_id,name,lab_status'])
            ->latest('id')->get();

        return $this->page('frontend.portal.dashboard', [
            'user' => $user,
            'patients' => $patients,
            'invoices' => $invoices,
            'due' => (float) $invoices->where('status', '!=', 'cancelled')->sum('due_amount'),
        ]);
    }

    public function invoice(Request $request, Invoice $invoice)
    {
        abort_unless($invoice->patient?->user_id === $request->user('web')->id, 404);
        $invoice->load(['billedItems.children', 'payments', 'branch', 'referrer']);

        return $this->page('frontend.portal.invoice', ['invoice' => $invoice]);
    }

    public function editPassword()
    {
        return $this->page('frontend.portal.password');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user('web');

        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::min(6), 'different:current_password'],
        ], [
            'password.different' => __('নতুন পাসওয়ার্ড আগের পাসওয়ার্ড থেকে আলাদা হতে হবে।'),
        ]);

        if (! Hash::check($request->input('current_password'), $user->password)) {
            return back()->withErrors(['current_password' => __('বর্তমান পাসওয়ার্ড সঠিক নয়।')]);
        }
        if ($request->input('password') === $user->phone) {
            return back()->withErrors(['password' => __('মোবাইল নম্বরকে পাসওয়ার্ড হিসেবে ব্যবহার করবেন না।')]);
        }

        $user->forceFill(['password' => $request->input('password'), 'must_change_password' => false])->save();

        return redirect()->route('portal.dashboard')->with('portal_status', __('পাসওয়ার্ড পরিবর্তন হয়েছে।'));
    }
}
