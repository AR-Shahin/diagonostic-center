{{-- Show only figures the center can verify: an empty value hides the tile. --}}
@php $stats = array_filter($content['stats'], fn ($s) => filled($s['value'])); @endphp
@if (count($stats))
    <section class="stats-wrap" aria-label="{{ __('এক নজরে') }}">
        <div class="container">
            <div class="stats-card reveal">
                @foreach ($stats as $stat)
                    <div class="stat">
                        <span class="stat-icon"><i class="bi {{ $stat['icon'] }}" aria-hidden="true"></i></span>
                        <strong class="stat-value"><span data-count="{{ $stat['value'] }}">{{ $stat['value'] }}</span>{{ $stat['suffix'] }}</strong>
                        <span class="stat-label">{{ $stat['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
