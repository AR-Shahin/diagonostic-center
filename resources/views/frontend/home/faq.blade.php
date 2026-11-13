@push('schema')
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => array_map(fn ($f) => [
            '@type' => 'Question',
            'name' => $f['q'],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']],
        ], $content['faqs']),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
@endpush

<section class="section section-alt" id="faq" aria-labelledby="faq-title">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-4 reveal">
                <span class="eyebrow">সাধারণ প্রশ্ন</span>
                <h2 class="section-title text-start" id="faq-title">আপনার প্রশ্নের <span class="grad">উত্তর</span></h2>
                <p class="section-sub text-start">উত্তর না পেলে হটলাইনে কল করুন।</p>
                <a class="btn btn-primary" href="{{ $site['phone_link'] }}"><i class="bi bi-telephone" aria-hidden="true"></i> {{ $site['phone'] }}</a>
            </div>

            <div class="col-lg-8 reveal" style="--d:.1s">
                <div class="accordion faq" id="faqAccordion">
                    @foreach ($content['faqs'] as $faq)
                        <div class="accordion-item">
                            <h3 class="accordion-header">
                                <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#faq-{{ $loop->index }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}" aria-controls="faq-{{ $loop->index }}">
                                    {{ $faq['q'] }}
                                </button>
                            </h3>
                            <div id="faq-{{ $loop->index }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">{{ $faq['a'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
