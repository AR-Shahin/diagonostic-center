@extends('admin.layouts.master')

@section('title', 'Global Settings')

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
