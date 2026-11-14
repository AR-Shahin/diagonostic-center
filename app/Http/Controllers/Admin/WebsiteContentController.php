<?php

namespace App\Http\Controllers\Admin;

use App\Helper\File\File;
use App\Http\Controllers\Admin\Concerns\CrudHelpers;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** Home page wording, numbers and home-collection areas (stored in the settings table). */
class WebsiteContentController extends Controller
{
    use CrudHelpers;

    public function index()
    {
        $this->allow('webcontent-edit');

        return view('admin.website-content.index', [
            'v' => collect(config('diagnostic.website'))->mapWithKeys(fn ($default, $key) => [$key => Setting::get($key, $default)]),
            'charge' => Setting::get('home_collection_charge', 0),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $this->allow('webcontent-edit');

        $data = $request->validate([
            'hero_eyebrow_bn' => ['nullable', 'string', 'max:120'],
            'hero_eyebrow_en' => ['nullable', 'string', 'max:120'],
            'hero_lead_bn' => ['nullable', 'string', 'max:300'],
            'hero_lead_en' => ['nullable', 'string', 'max:300'],
            'hero_popular' => ['nullable', 'string', 'max:200'],
            'stat_years' => ['nullable', 'integer', 'min:0', 'max:200'],
            'stat_patients' => ['nullable', 'integer', 'min:0', 'max:100000000'],
            'home_collection_areas_bn' => ['nullable', 'string', 'max:2000'],
            'home_collection_areas_en' => ['nullable', 'string', 'max:2000'],
            'google_reviews_url' => ['nullable', 'url:https', 'max:500'],
            'hero_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);
        unset($data['hero_image']);
        foreach (array_keys(config('diagnostic.website')) as $key) {
            if (! in_array($key, ['hero_image', 'home_collection_enabled'], true)) {
                $data[$key] = $data[$key] ?? '';
            }
        }
        $data['stat_years'] = (int) $data['stat_years'];
        $data['stat_patients'] = (int) $data['stat_patients'];
        $data['home_collection_enabled'] = $request->boolean('home_collection_enabled') ? 1 : 0;

        $old = Setting::get('hero_image', '');
        if ($request->hasFile('hero_image')) {
            File::deleteFile($old ?: null);
            $data['hero_image'] = File::upload($request->file('hero_image'), 'website');
        } elseif ($request->boolean('remove_hero_image')) {
            File::deleteFile($old ?: null);
            $data['hero_image'] = '';
        }

        Setting::put($data);
        $this->updatedAlert();

        return back();
    }
}
