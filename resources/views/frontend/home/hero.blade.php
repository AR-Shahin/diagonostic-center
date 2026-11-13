@php $hero = $content['hero']; @endphp
<section class="hero" aria-labelledby="hero-title">
    <div class="hero-bg" aria-hidden="true"></div>
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <span class="eyebrow-pill reveal"><i class="bi bi-stars" aria-hidden="true"></i> {{ $hero['eyebrow'] }}</span>

                <h1 class="hero-title reveal" id="hero-title" style="--d:.05s">
                    আপনার সুস্থতার নিশ্চয়তায় <span class="grad">নির্ভরযোগ্য ডায়াগনস্টিক সেবা</span>
                </h1>

                <p class="hero-lead reveal" style="--d:.1s">{{ $hero['lead'] }}</p>

                <form class="search-pill reveal" role="search" action="{{ route('site.tests') }}" method="get" style="--d:.15s">
                    <label for="heroSearch" class="visually-hidden">টেস্ট খুঁজুন</label>
                    <i class="bi bi-search" aria-hidden="true"></i>
                    <input id="heroSearch" type="search" name="q" placeholder="টেস্টের নাম লিখুন, যেমন CBC, HbA1c" autocomplete="off">
                    <button class="btn btn-primary" type="submit">খুঁজুন</button>
                </form>

                <div class="chip-row reveal" style="--d:.2s">
                    <span>জনপ্রিয়:</span>
                    @foreach ($hero['popular'] as $term)
                        <a class="chip" href="{{ route('site.tests', ['q' => $term]) }}">{{ $term }}</a>
                    @endforeach
                </div>

                <div class="hero-actions reveal" style="--d:.25s">
                    <a class="btn btn-primary btn-lg" href="{{ route('site.appointment') }}"><i class="bi bi-calendar2-check" aria-hidden="true"></i> অ্যাপয়েন্টমেন্ট নিন</a>
                    <a class="btn btn-ghost btn-lg" href="{{ route('site.report') }}"><i class="bi bi-file-earmark-arrow-down" aria-hidden="true"></i> রিপোর্ট ডাউনলোড</a>
                    <a class="btn btn-teal-soft btn-lg" href="#home-collection"><i class="bi bi-house-heart" aria-hidden="true"></i> বাসায় নমুনা সংগ্রহ</a>
                </div>
            </div>

            <div class="col-lg-6">
                @if ($hero['image'])
                    <img class="hero-image reveal" src="{{ $hero['image'] }}" alt="{{ $hero['image_alt'] }}" width="640" height="640" fetchpriority="high">
                @else
                    <div class="hero-visual reveal" style="--d:.15s" aria-hidden="true">
                        <span class="orb orb-1"></span>
                        <span class="orb orb-2"></span>

                        <div class="vcard vcard-report">
                            <div class="vcard-head">
                                <span class="vdot"></span>
                                <strong>ল্যাব রিপোর্ট</strong>
                                <i class="bi bi-qr-code ms-auto"></i>
                            </div>
                            <div class="vline" style="width:62%"></div>
                            <div class="vline" style="width:42%"></div>
                            <div class="vrows">
                                @foreach ([['Hemoglobin', '72%'], ['Glucose', '48%'], ['Cholesterol', '60%']] as [$label, $w])
                                    <div class="vrow"><span>{{ $label }}</span><div class="vbar"><i style="width:{{ $w }}"></i></div></div>
                                @endforeach
                            </div>
                            <span class="badge-verified"><i class="bi bi-patch-check-fill"></i> যাচাইকৃত রিপোর্ট</span>
                        </div>

                        <div class="vchip vchip-a"><span class="vchip-icon tone-teal"><i class="bi bi-check-lg"></i></span><div><b>নমুনা সংগ্রহ সম্পন্ন</b><small>Sample collected</small></div></div>
                        <div class="vchip vchip-b"><span class="vchip-icon tone-blue"><i class="bi bi-bell-fill"></i></span><div><b>রিপোর্ট প্রস্তুত</b><small>এসএমএসে জানানো হবে</small></div></div>
                        <div class="vchip vchip-c"><span class="vchip-icon tone-blue"><i class="bi bi-house-heart-fill"></i></span><div><b>বাসায় নমুনা সংগ্রহ</b><small>Home collection</small></div></div>

                        <div class="vcard vcard-trend">
                            <small>ট্রেন্ড</small>
                            <div class="vbars">
                                @foreach ([40, 62, 48, 74, 58, 86] as $h)
                                    <i style="height:{{ $h }}%"></i>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
