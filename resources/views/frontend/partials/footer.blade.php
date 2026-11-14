<footer class="site-footer">
    <div class="container">
        <div class="row g-4 g-lg-5">
            <div class="col-lg-4">
                <a class="brand brand-light" href="{{ route('site.home') }}">
                    @if ($site['logo'])
                        <img src="{{ $site['logo'] }}" alt="" height="40" width="40" class="brand-logo" loading="lazy">
                    @else
                        <span class="brand-mark" aria-hidden="true"><i class="bi bi-plus-lg"></i></span>
                    @endif
                    <span class="brand-text">{{ $site['name'] }}</span>
                </a>
                <p class="footer-about">{{ $site['description'] }}</p>
                <ul class="social-list social-list-lg" aria-label="{{ __('সোশ্যাল মিডিয়া') }}">
                    @foreach ([['facebook', 'bi-facebook', 'Facebook'], ['instagram', 'bi-instagram', 'Instagram'], ['twitter', 'bi-twitter-x', 'X'], ['linkedin', 'bi-linkedin', 'LinkedIn']] as [$key, $icon, $label])
                        <li><a href="{{ $site['social'][$key] ?: '#' }}" @if ($site['social'][$key]) target="_blank" rel="noopener" @endif aria-label="{{ $label }}"><i class="bi {{ $icon }}" aria-hidden="true"></i></a></li>
                    @endforeach
                </ul>
            </div>

            <div class="col-6 col-md-3 col-lg-2">
                <h2 class="footer-title">{{ __('দ্রুত লিংক') }}</h2>
                <ul class="footer-links">
                    <li><a href="{{ route('site.about') }}">{{ __('আমাদের সম্পর্কে') }}</a></li>
                    <li><a href="{{ route('site.tests') }}">{{ __('ডায়াগনস্টিক টেস্ট') }}</a></li>
                    <li><a href="{{ route('site.packages') }}">{{ __('হেলথ প্যাকেজ') }}</a></li>
                    <li><a href="{{ route('site.doctors') }}">{{ __('আমাদের ডাক্তার') }}</a></li>
                    <li><a href="{{ route('site.contact') }}">{{ __('যোগাযোগ') }}</a></li>
                </ul>
            </div>

            <div class="col-6 col-md-4 col-lg-3">
                <h2 class="footer-title">{{ __('ডায়াগনস্টিক সেবা') }}</h2>
                <ul class="footer-links">
                    @foreach (array_slice($content['services'], 0, 6) as $service)
                        <li><a href="{{ route('site.services') }}#{{ $service['slug'] }}">{{ $service['bn'] }}</a></li>
                    @endforeach
                </ul>
            </div>

            <div class="col-md-5 col-lg-3">
                <h2 class="footer-title">{{ __('রোগী সহায়তা') }}</h2>
                <ul class="footer-links">
                    <li><a href="{{ route('site.report') }}">{{ __('রিপোর্ট ডাউনলোড') }}</a></li>
                    <li><a href="{{ route('site.appointment') }}">{{ __('অ্যাপয়েন্টমেন্ট নিন') }}</a></li>
                    @if ($content['home_collection']['enabled'])
                        <li><a href="{{ route('site.appointment', ['type' => 'home']) }}">{{ __('বাসায় নমুনা সংগ্রহ') }}</a></li>
                    @endif
                    <li><a href="{{ route('site.articles') }}">{{ __('স্বাস্থ্য পরামর্শ') }}</a></li>
                    @if ($content['faqs'])<li><a href="{{ route('site.home') }}#faq">{{ __('সাধারণ প্রশ্ন') }}</a></li>@endif
                </ul>
            </div>
        </div>

        <div class="footer-contact">
            <div><i class="bi bi-geo-alt-fill" aria-hidden="true"></i><span>{{ $site['address'] }}</span></div>
            <div><i class="bi bi-telephone-fill" aria-hidden="true"></i><a href="{{ $site['phone_link'] }}">{{ $site['phone'] }}</a></div>
            <div><i class="bi bi-envelope-fill" aria-hidden="true"></i><a href="mailto:{{ $site['email'] }}">{{ $site['email'] }}</a></div>
        </div>

        <div class="footer-bottom">
            <p class="mb-0">{{ $site['footer_text'] }}</p>
            <ul class="footer-legal">
                <li><a href="#">{{ __('প্রাইভেসি পলিসি') }}</a></li>
                <li><a href="#">{{ __('শর্তাবলি') }}</a></li>
            </ul>
        </div>
    </div>
</footer>
