<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\CrudHelpers;
use App\Http\Controllers\Controller;
use App\Models\WebRequest;
use App\Support\BranchAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Inbox for everything visitors send from the website. */
class WebRequestController extends Controller
{
    use CrudHelpers;

    public function index(Request $request)
    {
        $this->allow('webrequest-view');
        $type = array_key_exists($request->input('type'), WebRequest::TYPES) ? $request->input('type') : null;
        $status = array_key_exists($request->input('status'), WebRequest::STATUSES) ? $request->input('status') : 'open';

        $base = $this->visible();
        $requests = (clone $base)->with(['branch:id,code,name_en', 'doctor:id,name_en,name_bn', 'handler:id,name'])
            ->when($type, fn ($q, $t) => $q->where('type', $t))
            ->when($status === 'open', fn ($q) => $q->whereIn('status', ['new', 'confirmed']), fn ($q) => $q->where('status', $status))
            ->orderByRaw("CASE status WHEN 'new' THEN 0 WHEN 'confirmed' THEN 1 ELSE 2 END")
            ->orderByRaw('COALESCE(preferred_date, DATE(created_at))')->latest('id')
            ->paginate(25)->withQueryString();

        $newCounts = (clone $base)->where('status', 'new')->selectRaw('type, COUNT(*) c')->groupBy('type')->pluck('c', 'type');

        return view('admin.web-request.index', compact('requests', 'type', 'status', 'newCounts'));
    }

    public function update(Request $request, WebRequest $webRequest): RedirectResponse
    {
        $this->allow('webrequest-handle');
        abort_unless($this->visible()->whereKey($webRequest->id)->exists(), 404);

        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(WebRequest::STATUSES))],
            'staff_note' => ['nullable', 'string', 'max:255'],
            'preferred_date' => ['nullable', 'date'],
            'preferred_time' => ['nullable', 'string', 'max:40'],
        ]);
        $webRequest->fill($data + ['handled_by' => auth('admin')->id(), 'handled_at' => now()])->save();
        $this->updatedAlert();

        return back();
    }

    /** Branch staff see requests for their branch plus ones that did not name a branch. */
    private function visible(): Builder
    {
        $admin = auth('admin')->user();

        return WebRequest::query()->when(BranchAccess::isRestricted($admin),
            fn ($q) => $q->where(fn ($w) => $w->where('branch_id', $admin->branch_id)->orWhereNull('branch_id')));
    }
}
