<section class="section" aria-labelledby="cta-title">
    <div class="container">
        <div class="cta-panel reveal">
            <span class="hc-glow" aria-hidden="true"></span>
            <h2 id="cta-title">{{ __('আজই আপনার প্রয়োজনীয়') }} <br class="d-none d-md-block">{{ __('স্বাস্থ্য পরীক্ষা সম্পন্ন করুন।') }}</h2>
            <p>{{ __('এক মিনিটেই বুক করুন, বাকিটা আমরা দেখছি।') }}</p>
            <div class="cta-actions">
                <a class="btn btn-light btn-lg" href="{{ route('site.appointment') }}"><i class="bi bi-calendar2-check" aria-hidden="true"></i> {{ __('অ্যাপয়েন্টমেন্ট নিন') }}</a>
                <a class="btn btn-outline-light btn-lg" href="{{ route('site.tests') }}"><i class="bi bi-search" aria-hidden="true"></i> {{ __('টেস্ট খুঁজুন') }}</a>
                <a class="btn btn-outline-light btn-lg" href="{{ $site['phone_link'] }}"><i class="bi bi-telephone" aria-hidden="true"></i> {{ __('হটলাইনে কল করুন') }}</a>
            </div>
        </div>
    </div>
</section>
