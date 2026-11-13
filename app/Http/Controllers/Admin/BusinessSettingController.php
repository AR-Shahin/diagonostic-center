<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\CrudHelpers;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Billing, commission and report-release rules (values stored in the settings table).
 */
class BusinessSettingController extends Controller
{
    use CrudHelpers;

    public function index()
    {
        $this->allow('businesssetting-view');

        $values = collect(config('diagnostic.defaults'))->mapWithKeys(fn ($default, $key) => [$key => Setting::get($key, $default)]);

        return view('admin.business-setting.index', [
            'values' => $values,
            'bearers' => config('diagnostic.discount_bearers'),
            'triggers' => config('diagnostic.commission_triggers'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $this->allow('businesssetting-edit');

        $data = $request->validate([
            'currency_symbol' => ['required', 'string', 'max:5'],
            'invoice_prefix' => ['required', 'string', 'max:10', 'alpha_dash'],
            'receipt_prefix' => ['required', 'string', 'max:10', 'alpha_dash'],
            'vat_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'default_delivery_hours' => ['required', 'integer', 'min:0', 'max:2000'],
            'home_collection_charge' => ['required', 'numeric', 'min:0'],
            'discount_bearer' => ['required', Rule::in(array_keys(config('diagnostic.discount_bearers')))],
            'commission_trigger' => ['required', Rule::in(array_keys(config('diagnostic.commission_triggers')))],
        ]);

        foreach (['round_to_whole_taka', 'commission_enabled', 'block_report_if_due', 'block_portal_report_if_due', 'portal_force_password_change'] as $flag) {
            $data[$flag] = $request->boolean($flag) ? 1 : 0;
        }
        $data['invoice_prefix'] = strtoupper($data['invoice_prefix']);
        $data['receipt_prefix'] = strtoupper($data['receipt_prefix']);

        Setting::put($data);
        $this->updatedAlert();

        return back();
    }
}
