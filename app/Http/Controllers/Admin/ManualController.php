<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;

/**
 * In-app user manual (Bangla). Every signed-in staff member can read it.
 */
class ManualController extends Controller
{
    public function __invoke()
    {
        return view('admin.manual.index', [
            'rules' => [
                'vat' => (float) Setting::get('vat_percent', 0),
                'home' => (float) Setting::get('home_collection_charge', 0),
                'round' => (bool) Setting::get('round_to_whole_taka', 1),
            ],
        ]);
    }
}
