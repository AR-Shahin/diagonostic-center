<?php

namespace App\Http\Controllers\Admin;

use App\Helper\File\File;
use App\Http\Controllers\Controller;
use App\Models\WebsiteInfo;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        // Get the first setting row, or create one if it doesn't exist
        $setting = WebsiteInfo::firstOrCreate(['id' => 1]);
        
        return view('admin.setting.index', compact('setting'));
    }

    public function update(Request $request)
    {
        $setting = WebsiteInfo::firstOrCreate(['id' => 1]);

        $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:1000',
            'facebook_url' => 'nullable|url|max:255',
            'twitter_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'linkedin_url' => 'nullable|url|max:255',
            'footer_text' => 'nullable|string|max:500',
            'maintenance_mode' => 'nullable|boolean',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
            'favicon' => 'nullable|image|mimes:jpeg,png,jpg,gif,ico|max:2048', // 2MB max
        ]);

        $data = $request->except(['_token', 'logo', 'favicon']);

        // Handle File Uploads
        if ($request->hasFile('logo')) {
            if ($setting->logo) {
                File::deleteFile($setting->logo);
            }
            $data['logo'] = File::upload($request->file('logo'), 'settings');
        }

        if ($request->hasFile('favicon')) {
            if ($setting->favicon) {
                File::deleteFile($setting->favicon);
            }
            $data['favicon'] = File::upload($request->file('favicon'), 'settings');
        }

        // Handle Checkbox (if unchecked, it won't be in request)
        $data['maintenance_mode'] = $request->has('maintenance_mode') ? 1 : 0;

        $setting->update($data);

        return redirect()->back()->with('status', 'Settings updated successfully!');
    }
}
