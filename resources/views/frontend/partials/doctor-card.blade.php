{{-- Props: $doctor --}}
<article class="card-soft doctor-card" data-filter-item data-cat="{{ $doctor['speciality'] }}" data-name="{{ mb_strtolower($doctor['name'].' '.$doctor['speciality']) }}">
    <div class="avatar-ring">
        @if ($doctor['image'])
            <img src="{{ $doctor['image'] }}" alt="{{ $doctor['name'] }}" width="96" height="96" loading="lazy">
        @else
            <span class="avatar-fallback" aria-hidden="true"><i class="bi bi-person-fill"></i></span>
        @endif
    </div>
    <h3 class="doctor-name">{{ $doctor['name'] }}</h3>
    <p class="doctor-qual">{{ $doctor['qualification'] }}</p>
    <p class="doctor-role">{{ $doctor['designation'] }}</p>
    <span class="tag tag-blue">{{ $doctor['speciality'] }}</span>

    <ul class="meta-list text-start">
        <li><i class="bi bi-calendar3" aria-hidden="true"></i> {{ $doctor['schedule'] }}</li>
        <li><i class="bi bi-wallet2" aria-hidden="true"></i> {{ __('ভিজিট:') }} ৳ {{ $doctor['fee'] }}</li>
    </ul>

    <a href="{{ route('site.appointment', ['doctor' => 1]) }}" class="btn btn-primary w-100">{{ __('অ্যাপয়েন্টমেন্ট নিন') }}</a>
</article>
