@extends('admin.layouts.master')

@section('title', 'Home page text')

@push('css')
    <style>.card:hover { transform: none; }</style>
@endpush

@section('master_content')
    <x-admin.page-header title="Home page text" icon="fas fa-pen-nib" subtitle="Leave a box empty to keep the built-in wording. Name, phone, address, logo and colours are under Settings.">
        <a class="btn btn-sm btn-light" target="_blank" href="{{ route('site.home') }}"><i class="fas fa-external-link-alt me-1"></i> View website</a>
    </x-admin.page-header>

    <form method="POST" action="{{ route('admin.website-content.update') }}" enctype="multipart/form-data">
        @csrf
        <div class="row g-3">
            <div class="col-lg-7">
                <div class="card mb-3">
                    <div class="card-header"><b>Top banner</b></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6"><x-admin.input name="hero_eyebrow_bn" label="Small line above the title (বাংলা)" :value="$v['hero_eyebrow_bn']" placeholder="অনলাইনে টেস্ট, অ্যাপয়েন্টমেন্ট ও রিপোর্ট" /></div>
                            <div class="col-md-6"><x-admin.input name="hero_eyebrow_en" label="Small line (English)" :value="$v['hero_eyebrow_en']" placeholder="Tests, appointments and reports online" /></div>
                            <div class="col-md-6"><x-admin.textarea name="hero_lead_bn" label="Intro text (বাংলা)" rows="3" :value="$v['hero_lead_bn']" /></div>
                            <div class="col-md-6"><x-admin.textarea name="hero_lead_en" label="Intro text (English)" rows="3" :value="$v['hero_lead_en']" /></div>
                            <div class="col-12"><x-admin.input name="hero_popular" label="Popular test chips under the search box" :value="$v['hero_popular']" help="Comma separated, e.g. CBC, HbA1c, Lipid Profile" /></div>
                            <div class="col-md-8">
                                <label class="form-label fw-semibold" for="f_hero_image">Banner picture</label>
                                <input class="form-control @error('hero_image') is-invalid @enderror" type="file" name="hero_image" id="f_hero_image" accept="image/png,image/jpeg,image/webp">
                                @error('hero_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <div class="form-text">A real photo of your centre or lab, about 1200×900, under 2 MB. Without one, a drawing is shown.</div>
                            </div>
                            <div class="col-md-4">
                                @if ($v['hero_image'])
                                    <img src="{{ asset($v['hero_image']) }}" alt="Current banner" class="img-fluid rounded border mb-2" style="max-height:110px">
                                    <x-admin.switch name="remove_hero_image" label="Remove picture" />
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><b>Home sample collection</b></div>
                    <div class="card-body">
                        <x-admin.switch name="home_collection_enabled" label="Offer home sample collection on the website" :checked="(bool) $v['home_collection_enabled']" />
                        <div class="row">
                            <div class="col-md-6"><x-admin.textarea name="home_collection_areas_bn" label="Areas served (বাংলা)" rows="4" :value="$v['home_collection_areas_bn']" help="One area per line" /></div>
                            <div class="col-md-6"><x-admin.textarea name="home_collection_areas_en" label="Areas served (English)" rows="4" :value="$v['home_collection_areas_en']" /></div>
                        </div>
                        <p class="form-text mb-0">Charge shown on the website: <b>৳ {{ number_format((float) $charge) }}</b> — change it in Business rules, so the bill and the website always agree.</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card mb-3">
                    <div class="card-header"><b>Numbers on the home page</b></div>
                    <div class="card-body">
                        <p class="small text-muted">Tests, doctors and branches are counted automatically. Fill these two only with figures you can prove; 0 hides them.</p>
                        <x-admin.input name="stat_years" label="Years of service" type="number" min="0" :value="$v['stat_years']" />
                        <x-admin.input name="stat_patients" label="Patients served" type="number" min="0" :value="$v['stat_patients']" />
                    </div>
                </div>
                <div class="card mb-3">
                    <div class="card-header"><b>Reviews</b></div>
                    <div class="card-body">
                        <x-admin.input name="google_reviews_url" label="Google reviews link" type="url" :value="$v['google_reviews_url']" placeholder="https://g.page/r/…/review" help="Shows a “See Google reviews” link under the reviews. Empty = hidden." />
                    </div>
                </div>
                <div class="d-grid"><button class="btn btn-success"><i class="fas fa-save me-1"></i> Save</button></div>
            </div>
        </div>
    </form>
@endsection
