@extends('admin.layouts.master')

@section('title', 'Global Settings')

@push('css')
<style>
    .theme-card { display: block; cursor: pointer; padding: .75rem; border: 2px solid var(--bs-border-color); border-radius: .9rem; background: #fff; transition: border-color .2s, box-shadow .2s; height: 100%; }
    .theme-card:hover { border-color: #b8c2cc; }
    .btn-check:checked + .theme-card { border-color: #198754; box-shadow: 0 0 0 .2rem rgba(25, 135, 84, .15); }
    .btn-check:focus-visible + .theme-card { outline: 3px solid rgba(13, 110, 253, .4); outline-offset: 2px; }
    .theme-check { color: #198754; opacity: 0; }
    .btn-check:checked + .theme-card .theme-check { opacity: 1; }
    .theme-preview { display: flex; flex-direction: column; gap: .4rem; padding: .7rem; border-radius: .6rem; background: var(--a); min-height: 6.5rem; }
    .tp-bar { height: .5rem; border-radius: 99px; background: var(--h); width: 40%; opacity: .85; }
    .tp-title { height: .75rem; border-radius: 99px; width: 85%; background: linear-gradient(90deg, var(--p), var(--s)); }
    .tp-line { height: .4rem; border-radius: 99px; width: 65%; background: #cfd8e3; }
    .tp-btns { display: flex; gap: .35rem; margin-top: auto; }
    .tp-btn { height: 1.1rem; width: 3rem; border-radius: 99px; background: var(--p); }
    .tp-btn-2 { background: var(--pd); opacity: .35; width: 2.2rem; }
    .theme-dot { width: .9rem; height: .9rem; border-radius: 50%; display: inline-block; box-shadow: inset 0 0 0 1px rgba(0,0,0,.1); }
</style>
@endpush

@section('master_content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h3><i class="fas fa-cogs text-primary me-2"></i> Global Settings</h3>
        </div>
        <hr>
    </div>
</div>

<form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="row">
        <!-- General Info Card -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle text-info me-2"></i> General Information</h5>
                </div>
                <div class="card-body">
                    <x-form.input label="Site Name" type="text" name="name" id="name" placeholder="Enter Site Name" :value="$setting->name" />
                    <x-form.input label="Email Address" type="email" name="email" id="email" placeholder="Enter Email" :value="$setting->email" />
                    <x-form.input label="Phone Number" type="text" name="phone" id="phone" placeholder="Enter Phone" :value="$setting->phone" />
                    
                    <div class="mb-3">
                        <label for="address" class="form-label"><b>Address:</b></label>
                        <textarea name="address" id="address" rows="3" class="form-control" placeholder="Enter full address">{{ old('address') ?? $setting->address }}</textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6"><x-admin.input name="opening_hours_en" label="Opening hours (English)" :value="$setting->opening_hours_en" placeholder="Every day 8 AM – 10 PM" /></div>
                        <div class="col-md-6"><x-admin.input name="opening_hours_bn" label="Opening hours (বাংলা)" :value="$setting->opening_hours_bn" placeholder="প্রতিদিন সকাল ৮টা – রাত ১০টা" /></div>
                    </div>

                    <x-form.input label="Footer Text" type="text" name="footer_text" id="footer_text" placeholder="Copyright © 2024..." :value="$setting->footer_text" />
                </div>
            </div>
        </div>

        <!-- Branding & System Card -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-paint-brush text-warning me-2"></i> Branding & Logos</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="logo"><b>Site Logo</b></label>
                            <input type="file" name="logo" id="logo" class="form-control">
                            @if($setting->logo)
                                <div class="mt-2">
                                    <img src="{{ asset($setting->logo) }}" alt="Logo" class="img-thumbnail" style="max-height: 80px;">
                                </div>
                            @endif
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="favicon"><b>Favicon</b></label>
                            <input type="file" name="favicon" id="favicon" class="form-control">
                            @if($setting->favicon)
                                <div class="mt-2">
                                    <img src="{{ asset($setting->favicon) }}" alt="Favicon" class="img-thumbnail" style="max-height: 50px;">
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-server text-danger me-2"></i> System Status</h5>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch fs-5">
                        <input class="form-check-input" type="checkbox" role="switch" id="maintenance_mode" name="maintenance_mode" value="1" {{ $setting->maintenance_mode ? 'checked' : '' }}>
                        <label class="form-check-label ms-2" for="maintenance_mode">Enable Maintenance Mode</label>
                    </div>
                    <small class="text-muted d-block mt-1">If enabled, standard users will see a maintenance page.</small>
                </div>
            </div>
        </div>


        <!-- Website theme -->
        <div class="col-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h5 class="mb-0"><i class="fas fa-palette text-success me-2"></i> Website theme</h5>
                    <small class="text-muted">Predefined palettes, all checked for readable contrast. Applies to the public website only.</small>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach ($themes as $key => $theme)
                            <div class="col-sm-6 col-lg-4 col-xxl-2">
                                <input type="radio" class="btn-check" name="theme_preset" id="theme_{{ $key }}" value="{{ $key }}" @checked(old('theme_preset', $setting->theme_preset ?: 'ocean') === $key)>
                                <label class="theme-card w-100" for="theme_{{ $key }}">
                                    <span class="theme-preview" style="--p: {{ $theme['primary'] }}; --pd: {{ $theme['primary_deep'] }}; --s: {{ $theme['secondary'] }}; --h: {{ $theme['heading'] }}; --a: {{ $theme['alt'] }};">
                                        <span class="tp-bar"></span>
                                        <span class="tp-title"></span>
                                        <span class="tp-line"></span>
                                        <span class="tp-btns"><span class="tp-btn"></span><span class="tp-btn tp-btn-2"></span></span>
                                    </span>
                                    <span class="d-flex align-items-center gap-2 mt-2">
                                        <span class="theme-dot" style="background: {{ $theme['primary'] }}"></span>
                                        <span class="theme-dot" style="background: {{ $theme['secondary'] }}"></span>
                                        <b class="small">{{ $theme['label'] }}</b>
                                        <i class="fas fa-check-circle ms-auto theme-check"></i>
                                    </span>
                                    <span class="d-block small text-muted mt-1">{{ $theme['description'] }}</span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Social Links Card -->
        <div class="col-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-share-alt text-primary me-2"></i> Social Media Links</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <x-form.input label="Facebook URL" type="url" name="facebook_url" id="facebook_url" placeholder="https://facebook.com/..." :value="$setting->facebook_url" />
                        </div>
                        <div class="col-md-6">
                            <x-form.input label="Twitter URL" type="url" name="twitter_url" id="twitter_url" placeholder="https://twitter.com/..." :value="$setting->twitter_url" />
                        </div>
                        <div class="col-md-6">
                            <x-form.input label="Instagram URL" type="url" name="instagram_url" id="instagram_url" placeholder="https://instagram.com/..." :value="$setting->instagram_url" />
                        </div>
                        <div class="col-md-6">
                            <x-form.input label="LinkedIn URL" type="url" name="linkedin_url" id="linkedin_url" placeholder="https://linkedin.com/..." :value="$setting->linkedin_url" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-12 text-end mb-5">
            <button type="submit" class="btn btn-sm btn-success px-5"><i class="fas fa-save me-2"></i> Save Settings</button>
        </div>
    </div>
</form>
@endsection
