<div class="topbar">
    <div class="container topbar-inner">
        <ul class="topbar-list">
            <li>
                <a href="{{ $site['phone_link'] }}"><i class="bi bi-telephone-fill" aria-hidden="true"></i>
                    <span class="d-none d-sm-inline">হটলাইন / জরুরি:</span> <strong>{{ $site['phone'] }}</strong></a>
            </li>
            <li class="d-none d-lg-flex"><i class="bi bi-clock" aria-hidden="true"></i> {{ $site['hours'] }}</li>
            <li class="d-none d-xl-flex"><i class="bi bi-geo-alt-fill" aria-hidden="true"></i> {{ $site['address'] }}</li>
        </ul>

        <div class="topbar-right">
            <div class="lang-switch" role="group" aria-label="ভাষা নির্বাচন">
                <a href="?lang=bn" class="is-active" aria-current="true" lang="bn">বাংলা</a>
                <a href="?lang=en" lang="en">English</a>
            </div>
            <ul class="social-list d-none d-sm-flex" aria-label="সোশ্যাল মিডিয়া">
                @foreach ([['facebook', 'bi-facebook', 'Facebook'], ['instagram', 'bi-instagram', 'Instagram'], ['twitter', 'bi-twitter-x', 'X'], ['linkedin', 'bi-linkedin', 'LinkedIn']] as [$key, $icon, $label])
                    <li><a href="{{ $site['social'][$key] ?: '#' }}" @if ($site['social'][$key]) target="_blank" rel="noopener" @endif aria-label="{{ $label }}"><i class="bi {{ $icon }}" aria-hidden="true"></i></a></li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
