@php $hc = $content['home_collection']; @endphp
<section class="section pt-0" id="home-collection" aria-labelledby="hc-title">
    <div class="container">
        <div class="hc-panel reveal">
            <span class="hc-glow" aria-hidden="true"></span>
            <div class="row g-5 align-items-center">
                <div class="col-lg-7">
                    <span class="eyebrow eyebrow-light">{{ __('বাসায় নমুনা সংগ্রহ') }}</span>
                    <h2 class="hc-title" id="hc-title">{{ __('ঘরে বসেই দিন নমুনা,') }} <br>{{ __('রিপোর্ট পান ফোনে') }}</h2>
                    <p class="hc-lead">{{ __('লাইনে দাঁড়ানো বা যাতায়াতের ঝামেলা ছাড়াই পরীক্ষার নমুনা দিন। প্রবীণ ও অসুস্থদের জন্য বিশেষ সুবিধাজনক।') }}</p>

                    <ol class="hc-steps">
                        @foreach ($hc['steps'] as $step)
                            <li><span>{{ $loop->iteration }}</span> {{ $step }}</li>
                        @endforeach
                    </ol>

                    <div class="hc-meta">
                        @if ($hc['areas'])
                            <div>
                                <small>{{ __('সেবা এলাকা') }}</small>
                                <div class="hc-areas">
                                    @foreach ($hc['areas'] as $area)
                                        <span>{{ $area }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        <div>
                            <small>{{ __('কালেকশন চার্জ') }}</small>
                            <strong>{{ $hc['charge'] ? '৳ '.$hc['charge'] : __('কল করে জানুন') }}</strong>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-3 mt-4">
                        <a class="btn btn-light btn-lg" href="{{ route('site.appointment', ['type' => 'home']) }}"><i class="bi bi-house-heart" aria-hidden="true"></i> {{ __('হোম কালেকশনের অনুরোধ') }}</a>
                        <a class="btn btn-outline-light btn-lg" href="{{ $site['phone_link'] }}"><i class="bi bi-telephone" aria-hidden="true"></i> {{ __('কল করুন') }}</a>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="track-card" aria-hidden="true">
                        <div class="track-head"><b>{{ __('অর্ডার ট্র্যাকিং') }}</b><span class="tag tag-light">{{ __('লাইভ') }}</span></div>
                        <ul class="track-list">
                            <li class="done"><i class="bi bi-check-circle-fill"></i><div><b>{{ __('অনুরোধ নিশ্চিত') }}</b><small>{{ __('সময় নির্ধারিত হয়েছে') }}</small></div></li>
                            <li class="done"><i class="bi bi-check-circle-fill"></i><div><b>{{ __('কর্মী রওনা দিয়েছেন') }}</b><small>{{ __('শীঘ্রই পৌঁছাবেন') }}</small></div></li>
                            <li class="now"><i class="bi bi-record-circle"></i><div><b>{{ __('নমুনা সংগ্রহ চলছে') }}</b><small>{{ __('ল্যাবে পাঠানো হবে') }}</small></div></li>
                            <li><i class="bi bi-circle"></i><div><b>{{ __('রিপোর্ট প্রস্তুত') }}</b><small>{{ __('এসএমএসে জানানো হবে') }}</small></div></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
