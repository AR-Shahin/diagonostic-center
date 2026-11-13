<section class="section" aria-labelledby="how-title">
    <div class="container">
        @include('frontend.partials.section-head', [
            'id' => 'how-title',
            'eyebrow' => 'কীভাবে কাজ করে',
            'title' => 'চার ধাপে <span class="grad">সহজ ডায়াগনস্টিক সেবা</span>',
        ])

        <ol class="steps">
            @foreach ($content['steps'] as $step)
                <li class="step reveal" style="--d:{{ $loop->index * 0.08 }}s">
                    <span class="step-no">{{ $loop->iteration }}</span>
                    <span class="icon-bubble"><i class="bi {{ $step['icon'] }}" aria-hidden="true"></i></span>
                    <h3>{{ $step['title'] }}</h3>
                    <p>{{ $step['desc'] }}</p>
                </li>
            @endforeach
        </ol>
    </div>
</section>
