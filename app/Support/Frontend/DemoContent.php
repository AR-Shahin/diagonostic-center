<?php

namespace App\Support\Frontend;

use Illuminate\Support\Carbon;

/**
 * PLACEHOLDER CONTENT FOR DESIGN REVIEW.
 *
 * Every section of the public website reads from the arrays below, so replacing
 * this class with Eloquent queries (tests, packages, doctors, branches, ...) is
 * the only change needed to make the site dynamic from the admin panel.
 *
 * Numbers ("00"), prices ("000"), person names, addresses and reviews are
 * deliberately fake. Publish only figures the center can verify.
 */
class DemoContent
{
    /** Values the seeder/migration put into website_infos; treated as "not configured yet". */
    private const WEBSITE_DEFAULTS = ['Admin Panel', 'admin@mail.com', '01754100439'];

    /** Site settings: uses website_infos (admin > Settings) when filled, placeholders otherwise. */
    public static function site(): array
    {
        $w = self::websiteInfo();
        $pick = fn ($value, string $fallback) => filled($value) && ! in_array($value, self::WEBSITE_DEFAULTS, true) ? $value : $fallback;

        $phone = $pick($w->phone ?? null, '01XXX-XXXXXX');
        $digits = preg_replace('/\D/', '', $phone);
        $name = $pick($w->name ?? null, 'Diagnostic Center');

        return [
            'name' => $name,
            'tagline' => 'নির্ভরযোগ্য ডায়াগনস্টিক সেবা',
            'description' => 'টেস্ট খুঁজুন, অ্যাপয়েন্টমেন্ট নিন, প্রয়োজনে বাসা থেকে নমুনা দিন এবং অনলাইনে রিপোর্ট ডাউনলোড করুন।',
            'logo' => ($w && filled($w->logo) && ! in_array($w->logo, self::WEBSITE_DEFAULTS, true)) ? asset($w->logo) : null,
            'favicon' => ($w && filled($w->favicon ?? null)) ? asset($w->favicon) : null,
            'phone' => $phone,
            'phone_set' => $phone !== '01XXX-XXXXXX',
            'phone_link' => strlen($digits) >= 10 ? 'tel:'.$digits : '#',
            'email' => $pick($w->email ?? null, 'info@example.com'),
            'address' => $pick($w->address ?? null, '[ঠিকানা], [এলাকা], [শহর]'),
            'address_set' => filled($w->address ?? null),
            'hours' => 'প্রতিদিন [খোলার সময়]',
            'social' => [
                'facebook' => $w->facebook_url ?? null,
                'instagram' => $w->instagram_url ?? null,
                'twitter' => $w->twitter_url ?? null,
                'linkedin' => $w->linkedin_url ?? null,
            ],
            'footer_text' => filled($w->footer_text ?? null) ? $w->footer_text : '© '.date('Y').' '.$name.'. সর্বস্বত্ব সংরক্ষিত।',
        ];
    }

    public static function all(): array
    {
        $departments = self::departments();

        return [
            'hero' => [
                'eyebrow' => 'অনলাইনে টেস্ট, অ্যাপয়েন্টমেন্ট ও রিপোর্ট',
                'lead' => 'টেস্ট খুঁজুন, অ্যাপয়েন্টমেন্ট নিন, প্রয়োজনে বাসা থেকে নমুনা দিন এবং ফোনেই রিপোর্ট ডাউনলোড করুন।',
                'image' => null, // set to a banner URL from the admin panel to replace the built-in illustration
                'image_alt' => 'ডায়াগনস্টিক ল্যাবরেটরি',
                'popular' => ['CBC', 'HbA1c', 'Lipid Profile', 'TSH', 'USG'],
            ],
            'stats' => [
                ['icon' => 'bi-award', 'value' => '00', 'suffix' => '+', 'label' => 'বছরের অভিজ্ঞতা'],
                ['icon' => 'bi-clipboard2-pulse', 'value' => '00', 'suffix' => '+', 'label' => 'ডায়াগনস্টিক টেস্ট'],
                ['icon' => 'bi-person-badge', 'value' => '00', 'suffix' => '+', 'label' => 'বিশেষজ্ঞ চিকিৎসক'],
                ['icon' => 'bi-emoji-smile', 'value' => '00', 'suffix' => '+', 'label' => 'সন্তুষ্ট রোগী'],
                ['icon' => 'bi-buildings', 'value' => '00', 'suffix' => '', 'label' => 'শাখা'],
            ],
            'quick' => [
                ['icon' => 'bi-search', 'title' => 'টেস্ট খুঁজুন', 'desc' => 'নাম লিখে টেস্ট, মূল্য ও প্রস্তুতি দেখুন।', 'url' => route('site.tests')],
                ['icon' => 'bi-calendar2-check', 'title' => 'অ্যাপয়েন্টমেন্ট নিন', 'desc' => 'পছন্দের তারিখ ও সময় বেছে নিন।', 'url' => route('site.appointment')],
                ['icon' => 'bi-file-earmark-arrow-down', 'title' => 'রিপোর্ট ডাউনলোড', 'desc' => 'মোবাইল নম্বর বা ইনভয়েস দিয়ে রিপোর্ট নিন।', 'url' => route('site.report')],
                ['icon' => 'bi-house-heart', 'title' => 'বাসায় নমুনা সংগ্রহ', 'desc' => 'ঘরে বসেই নমুনা দেওয়ার অনুরোধ করুন।', 'url' => route('site.home').'#home-collection'],
            ],
            'departments' => $departments,
            'tests' => self::tests(),
            'packages' => self::packages(),
            'services' => self::services(),
            'doctors' => self::doctors(),
            'specialities' => ['মেডিসিন', 'কার্ডিওলজি', 'গাইনোকোলজি', 'প্যাথলজি', 'রেডিওলজি', 'এন্ডোক্রাইনোলজি', 'নেফ্রোলজি', 'শিশুরোগ'],
            'home_collection' => [
                'areas' => ['[এলাকা ১]', '[এলাকা ২]', '[এলাকা ৩]', '[এলাকা ৪]'],
                'charge' => '000',
                'steps' => [
                    'ফোনে বা অনলাইনে অনুরোধ করুন',
                    'পছন্দের সময় ও ঠিকানা নিশ্চিত করুন',
                    'আমাদের কর্মী বাসায় এসে নমুনা সংগ্রহ করবেন',
                    'রিপোর্ট প্রস্তুত হলে এসএমএস পাবেন',
                ],
            ],
            'why' => [
                ['icon' => 'bi-cpu', 'title' => 'আধুনিক যন্ত্রপাতি', 'desc' => 'নির্ভুল ফলাফলের জন্য নিয়মিত রক্ষণাবেক্ষণ করা যন্ত্র।'],
                ['icon' => 'bi-people', 'title' => 'অভিজ্ঞ চিকিৎসক ও টেকনোলজিস্ট', 'desc' => 'দক্ষ পেশাজীবীদের তত্ত্বাবধানে প্রতিটি পরীক্ষা।'],
                ['icon' => 'bi-shield-check', 'title' => 'মান নিয়ন্ত্রিত ল্যাবরেটরি', 'desc' => 'প্রতিটি ধাপে মান যাচাইয়ের প্রক্রিয়া।'],
                ['icon' => 'bi-patch-check', 'title' => 'নির্ভরযোগ্য রিপোর্টিং', 'desc' => 'স্পষ্ট ও যাচাইযোগ্য রিপোর্ট।'],
                ['icon' => 'bi-cash-coin', 'title' => 'সাশ্রয়ী মূল্য', 'desc' => 'স্বচ্ছ মূল্য, কোনো লুকানো খরচ নয়।'],
                ['icon' => 'bi-lock', 'title' => 'রোগীর গোপনীয়তা', 'desc' => 'আপনার তথ্য ও রিপোর্ট সুরক্ষিত।'],
                ['icon' => 'bi-lightning-charge', 'title' => 'দ্রুত রিপোর্ট ডেলিভারি', 'desc' => 'রিপোর্ট প্রস্তুত হলেই এসএমএস ও অনলাইনে।'],
            ],
            'steps' => [
                ['icon' => 'bi-search', 'title' => 'টেস্ট নির্বাচন করুন', 'desc' => 'প্রয়োজনীয় টেস্ট বা প্যাকেজ বেছে নিন।'],
                ['icon' => 'bi-calendar2-check', 'title' => 'অ্যাপয়েন্টমেন্ট নিন', 'desc' => 'পছন্দের তারিখ ও সময় নিশ্চিত করুন।'],
                ['icon' => 'bi-hospital', 'title' => 'সেন্টারে আসুন বা বাসায় নমুনা দিন', 'desc' => 'শাখায় এসে বা ঘরে বসে নমুনা দিন।'],
                ['icon' => 'bi-cloud-arrow-down', 'title' => 'অনলাইনে রিপোর্ট নিন', 'desc' => 'প্রস্তুত হলে এসএমএস পাবেন, ডাউনলোড করুন।'],
            ],
            'branches' => [
                ['name' => '[প্রধান শাখার নাম]', 'address' => '[ঠিকানা], [এলাকা], [শহর]', 'phone' => '[ফোন নম্বর]', 'hours' => '[খোলার সময়]', 'services' => ['প্যাথলজি', 'ইমেজিং', 'নমুনা সংগ্রহ'], 'image' => null],
                ['name' => '[শাখা ২]', 'address' => '[ঠিকানা], [এলাকা], [শহর]', 'phone' => '[ফোন নম্বর]', 'hours' => '[খোলার সময়]', 'services' => ['প্যাথলজি', 'নমুনা সংগ্রহ'], 'image' => null],
                ['name' => '[শাখা ৩]', 'address' => '[ঠিকানা], [এলাকা], [শহর]', 'phone' => '[ফোন নম্বর]', 'hours' => '[খোলার সময়]', 'services' => ['প্যাথলজি', 'আল্ট্রাসাউন্ড'], 'image' => null],
            ],
            'testimonials' => array_fill(0, 3, [
                'text' => 'রোগীর মতামত এখানে প্রদর্শিত হবে। শুধুমাত্র সম্মতিসহ সংগৃহীত বা যাচাইকৃত রিভিউ অ্যাডমিন প্যানেল থেকে যুক্ত করুন।',
                'name' => '[রোগীর নাম]',
                'meta' => '[সেবার ধরন]',
                'rating' => 5,
            ]),
            'corporate' => [
                ['icon' => 'bi-briefcase', 'title' => 'কর্পোরেট হেলথ চেকআপ'],
                ['icon' => 'bi-people', 'title' => 'কর্মীদের মেডিকেল স্ক্রিনিং'],
                ['icon' => 'bi-clipboard2-check', 'title' => 'চাকরিপূর্ব মেডিকেল টেস্ট'],
                ['icon' => 'bi-building', 'title' => 'প্রাতিষ্ঠানিক হেলথ প্যাকেজ'],
                ['icon' => 'bi-people', 'title' => 'কর্পোরেট পার্টনারশিপ'],
            ],
            'articles' => [
                ['category' => 'টেস্ট প্রস্তুতি', 'title' => 'রক্ত পরীক্ষার আগে যা জানা জরুরি', 'excerpt' => 'উপবাস, পানি পান ও ওষুধ সংক্রান্ত সাধারণ নির্দেশনা এখানে প্রকাশিত হবে।', 'date' => Carbon::now()->subDays(3), 'image' => null, 'url' => '#'],
                ['category' => 'স্বাস্থ্য সচেতনতা', 'title' => 'নিয়মিত স্বাস্থ্য পরীক্ষা কেন গুরুত্বপূর্ণ', 'excerpt' => 'কোন বয়সে কোন পরীক্ষা করানো উচিত—সংক্ষিপ্ত গাইড এখানে যুক্ত হবে।', 'date' => Carbon::now()->subDays(10), 'image' => null, 'url' => '#'],
                ['category' => 'মৌসুমি স্বাস্থ্য', 'title' => 'বর্ষায় জ্বর ও সংক্রমণ থেকে সতর্কতা', 'excerpt' => 'মৌসুমি রোগের লক্ষণ ও কখন পরীক্ষা করাবেন, তার নির্দেশনা এখানে থাকবে।', 'date' => Carbon::now()->subDays(18), 'image' => null, 'url' => '#'],
            ],
            'faqs' => [
                ['q' => 'কীভাবে ডায়াগনস্টিক টেস্ট বুক করবেন?', 'a' => '“অ্যাপয়েন্টমেন্ট নিন” পেজে টেস্ট বা চিকিৎসক বেছে তারিখ ও সময় নির্ধারণ করুন। চাইলে হটলাইনে কল করেও বুক করতে পারেন।'],
                ['q' => 'কীভাবে রিপোর্ট ডাউনলোড করবেন?', 'a' => '“রিপোর্ট ডাউনলোড” পেজে মোবাইল নম্বর ও ওটিপি, অথবা ইনভয়েস নম্বর ও রিপোর্ট পিন দিয়ে রিপোর্ট খুলে PDF ডাউনলোড করতে পারবেন।'],
                ['q' => 'রিপোর্ট পেতে কত সময় লাগে?', 'a' => 'টেস্টভেদে সময় আলাদা। প্রতিটি টেস্টের কার্ডে রিপোর্ট ডেলিভারির আনুমানিক সময় উল্লেখ থাকে এবং ইনভয়েসেও দেওয়া হয়।'],
                ['q' => 'বাসা থেকে নমুনা সংগ্রহের সুবিধা আছে কি?', 'a' => 'নির্ধারিত সেবা এলাকায় বাসা থেকে নমুনা সংগ্রহের সুবিধা রয়েছে। এলাকা ও চার্জ “বাসায় নমুনা সংগ্রহ” সেকশনে দেখুন।'],
                ['q' => 'কোন কোন পেমেন্ট পদ্ধতি গ্রহণযোগ্য?', 'a' => 'সেন্টারে চালু থাকা পদ্ধতি অনুযায়ী পেমেন্ট করা যায়। বুকিংয়ের সময় চালু অপশনগুলো দেখানো হবে।'],
                ['q' => 'রক্ত পরীক্ষার আগে কি উপবাস প্রয়োজন?', 'a' => 'সব টেস্টে নয়। প্রতিটি টেস্টের প্রস্তুতি নির্দেশনা টেস্ট কার্ডে দেওয়া থাকে। নিশ্চিত হতে চিকিৎসক বা আমাদের হটলাইনে জানুন।'],
                ['q' => 'আপনাদের খোলার সময় কখন?', 'a' => 'শাখাভেদে সময় আলাদা হতে পারে। প্রতিটি শাখার সময় “আমাদের শাখা” সেকশনে দেখুন।'],
            ],
        ];
    }

    private static function departments(): array
    {
        return [
            'pathology' => ['bn' => 'প্যাথলজি', 'en' => 'Pathology', 'icon' => 'bi-eyedropper', 'desc' => 'রক্ত, প্রস্রাব ও অন্যান্য নমুনার ল্যাব পরীক্ষা ও বিশ্লেষণ।', 'services' => ['রুটিন রক্ত ও প্রস্রাব পরীক্ষা', 'হিস্টোপ্যাথলজি ও সাইটোলজি', 'নমুনা সংগ্রহ ও প্রক্রিয়াকরণ']],
            'biochemistry' => ['bn' => 'বায়োকেমিস্ট্রি', 'en' => 'Biochemistry', 'icon' => 'bi-droplet-half', 'desc' => 'রক্তের রাসায়নিক উপাদান মেপে লিভার, কিডনি ও মেটাবলিজমের অবস্থা বোঝা।', 'services' => ['লিভার ও কিডনি ফাংশন টেস্ট', 'ব্লাড সুগার ও HbA1c', 'লিপিড প্রোফাইল ও হরমোন']],
            'hematology' => ['bn' => 'হেমাটোলজি', 'en' => 'Hematology', 'icon' => 'bi-droplet-fill', 'desc' => 'রক্তকণিকার গণনা ও রক্ত জমাট বাঁধা সংক্রান্ত পরীক্ষা।', 'services' => ['কমপ্লিট ব্লাড কাউন্ট (CBC)', 'ESR ও ব্লাড গ্রুপিং', 'কোয়াগুলেশন প্রোফাইল']],
            'microbiology' => ['bn' => 'মাইক্রোবায়োলজি', 'en' => 'Microbiology', 'icon' => 'bi-virus', 'desc' => 'জীবাণু শনাক্তকরণ ও কার্যকর অ্যান্টিবায়োটিক নির্ধারণের পরীক্ষা।', 'services' => ['কালচার ও সেনসিটিভিটি', 'সংক্রামক রোগের স্ক্রিনিং', 'র‍্যাপিড অ্যান্টিজেন টেস্ট']],
            'radiology' => ['bn' => 'রেডিওলজি ও ইমেজিং', 'en' => 'Radiology & Imaging', 'icon' => 'bi-image-alt', 'desc' => 'এক্স-রে, সিটি স্ক্যান ও এমআরআইসহ ইমেজিং পরীক্ষা।', 'services' => ['এক্স-রে', 'সিটি স্ক্যান', 'এমআরআই']],
            'cardiology' => ['bn' => 'কার্ডিওলজি', 'en' => 'Cardiology', 'icon' => 'bi-heart-pulse', 'desc' => 'হৃদযন্ত্রের কার্যক্ষমতা মূল্যায়নের পরীক্ষা।', 'services' => ['ইসিজি (ECG)', 'ইকো (ECHO)', 'হার্ট মূল্যায়ন']],
            'ultrasonography' => ['bn' => 'আল্ট্রাসোনোগ্রাফি', 'en' => 'Ultrasonography', 'icon' => 'bi-soundwave', 'desc' => 'শব্দতরঙ্গের মাধ্যমে অঙ্গ-প্রত্যঙ্গের ছবি তুলে পরীক্ষা।', 'services' => ['হোল অ্যাবডোমেন', 'প্রেগন্যান্সি স্ক্যান', 'থাইরয়েড ও ব্রেস্ট']],
        ];
    }

    private static function tests(): array
    {
        $t = fn (string $slug, string $name, string $bn, string $cat, string $prep, bool $popular = false, ?string $discount = null) => [
            'slug' => $slug, 'name' => $name, 'bn' => $bn, 'cat' => $cat, 'prep' => $prep,
            'price' => '000', 'discount' => $discount, 'report' => '00 ঘণ্টা', 'popular' => $popular,
        ];

        return [
            $t('cbc', 'CBC', 'কমপ্লিট ব্লাড কাউন্ট', 'hematology', 'উপবাস প্রয়োজন নেই', true),
            $t('fbs', 'Blood Glucose (Fasting)', 'রক্তে শর্করা (খালি পেটে)', 'biochemistry', 'উপবাস প্রয়োজন'),
            $t('hba1c', 'HbA1c', 'গড় রক্তশর্করা (৩ মাস)', 'biochemistry', 'উপবাস প্রয়োজন নেই', true, '000'),
            $t('lipid', 'Lipid Profile', 'লিপিড প্রোফাইল', 'biochemistry', 'উপবাস প্রয়োজন', true, '000'),
            $t('lft', 'Liver Function Test', 'লিভার ফাংশন টেস্ট', 'biochemistry', 'নির্দেশনা অনুযায়ী'),
            $t('creatinine', 'Serum Creatinine', 'সিরাম ক্রিয়েটিনিন', 'biochemistry', 'উপবাস প্রয়োজন নেই'),
            $t('tsh', 'TSH', 'থাইরয়েড (TSH)', 'biochemistry', 'উপবাস প্রয়োজন নেই', true),
            $t('urine-re', 'Urine R/E', 'প্রস্রাব রুটিন পরীক্ষা', 'pathology', 'মধ্যপ্রবাহ নমুনা', true),
            $t('urine-cs', 'Urine Culture & Sensitivity', 'প্রস্রাব কালচার', 'microbiology', 'জীবাণুমুক্ত পাত্র প্রয়োজন'),
            $t('dengue-ns1', 'Dengue NS1 Antigen', 'ডেঙ্গু এনএস১', 'microbiology', 'উপবাস প্রয়োজন নেই'),
            $t('xray-chest', 'X-Ray Chest P/A', 'বুকের এক্স-রে', 'radiology', 'ধাতব বস্তু খুলে রাখুন'),
            $t('ecg', 'ECG', 'ইসিজি', 'cardiology', 'বিশেষ প্রস্তুতি নেই'),
            $t('usg-whole', 'USG Whole Abdomen', 'আল্ট্রাসোনোগ্রাম (পুরো পেট)', 'ultrasonography', 'নির্দেশনা অনুযায়ী প্রস্তুতি', true, '000'),
        ];
    }

    private static function packages(): array
    {
        $p = fn (string $slug, string $bn, string $en, string $icon, string $tagline, array $tests, bool $featured = false) => [
            'slug' => $slug, 'bn' => $bn, 'en' => $en, 'icon' => $icon, 'tagline' => $tagline,
            'tests' => $tests, 'price' => '000', 'old_price' => '000', 'saving' => '000', 'featured' => $featured,
        ];

        return [
            $p('full-body', 'ফুল বডি চেকআপ', 'Full Body Checkup', 'bi-person-check', 'সামগ্রিক স্বাস্থ্য যাচাইয়ের একটি পূর্ণাঙ্গ প্যাকেজ।', ['CBC', 'ব্লাড সুগার', 'লিপিড প্রোফাইল', 'লিভার ফাংশন', 'কিডনি ফাংশন', 'প্রস্রাব রুটিন', 'ইসিজি'], true),
            $p('diabetes', 'ডায়াবেটিস স্ক্রিনিং', 'Diabetes Screening', 'bi-droplet-half', 'ডায়াবেটিসের ঝুঁকি ও নিয়ন্ত্রণ যাচাই।', ['ফাস্টিং ব্লাড সুগার', 'HbA1c', 'প্রস্রাব রুটিন', 'সিরাম ক্রিয়েটিনিন']),
            $p('heart', 'হার্ট হেলথ প্যাকেজ', 'Heart Health Package', 'bi-heart-pulse', 'হৃদযন্ত্রের সুস্থতা যাচাই।', ['লিপিড প্রোফাইল', 'ইসিজি', 'ইকো', 'ব্লাড সুগার']),
            $p('women', 'উইমেন্স হেলথ প্যাকেজ', "Women's Health Package", 'bi-gender-female', 'নারীর স্বাস্থ্য-সংক্রান্ত প্রয়োজনীয় পরীক্ষা।', ['CBC', 'থাইরয়েড (TSH)', 'ভিটামিন ডি', 'আয়রন স্টাডি', 'পেলভিক আল্ট্রাসাউন্ড']),
            $p('senior', 'সিনিয়র সিটিজেন প্যাকেজ', 'Senior Citizen Package', 'bi-person-heart', 'প্রবীণদের জন্য নিয়মিত স্বাস্থ্য পরীক্ষা।', ['CBC', 'ব্লাড সুগার', 'কিডনি ফাংশন', 'লিপিড প্রোফাইল', 'ইসিজি']),
        ];
    }

    private static function services(): array
    {
        $s = fn (string $slug, string $bn, string $en, string $icon, string $desc, string $prep, int $span, string $tone) => compact('slug', 'bn', 'en', 'icon', 'desc', 'prep', 'span', 'tone');

        return [
            $s('blood-tests', 'রক্ত পরীক্ষা', 'Blood Tests', 'bi-droplet-half', 'রুটিন থেকে বিশেষায়িত রক্ত পরীক্ষার নমুনা সংগ্রহ ও ল্যাব বিশ্লেষণ।', 'কিছু টেস্টে উপবাস লাগে—টেস্ট কার্ডে নির্দেশনা দেখুন।', 5, 'blue'),
            $s('x-ray', 'এক্স-রে', 'X-Ray', 'bi-radioactive', 'দ্রুত ও স্পষ্ট ডিজিটাল এক্স-রে ইমেজ।', 'ধাতব বস্তু ও গয়না খুলে রাখুন।', 4, 'light'),
            $s('ecg-echo', 'ইসিজি / ইকো', 'ECG / ECHO', 'bi-activity', 'হৃদযন্ত্রের বৈদ্যুতিক ও কাঠামোগত মূল্যায়ন।', 'আরামদায়ক পোশাকে আসুন।', 3, 'light'),
            $s('ct-scan', 'সিটি স্ক্যান', 'CT Scan', 'bi-disc', 'বিস্তারিত অভ্যন্তরীণ ছবির জন্য সিটি ইমেজিং।', 'চিকিৎসকের রেফারেল ও পূর্বের রিপোর্ট সঙ্গে আনুন।', 3, 'dark'),
            $s('mri', 'এমআরআই', 'MRI', 'bi-magnet', 'নরম টিস্যু ও স্নায়ুর বিস্তারিত ইমেজিং।', 'শরীরে ধাতব ইমপ্ল্যান্ট থাকলে আগে জানান।', 4, 'light'),
            $s('ultrasound', 'আল্ট্রাসাউন্ড', 'Ultrasound', 'bi-soundwave', 'নিরাপদ ও ব্যথাহীন আল্ট্রাসোনোগ্রাফি পরীক্ষা।', 'পরীক্ষাভেদে খালি বা পূর্ণ মূত্রথলির প্রয়োজন হতে পারে।', 5, 'teal'),
            $s('digital-imaging', 'ডিজিটাল ইমেজিং', 'Digital Imaging', 'bi-display', 'ছবি ডিজিটালভাবে সংরক্ষণ ও সহজে শেয়ারের সুবিধা।', 'পূর্বের ছবি থাকলে সঙ্গে আনুন।', 6, 'light'),
            $s('specialized', 'বিশেষায়িত ডায়াগনস্টিক সেবা', 'Specialized Diagnostic Services', 'bi-stars', 'বিশেষ ও জটিল পরীক্ষার জন্য আলাদা ব্যবস্থা।', 'বিস্তারিত জানতে হটলাইনে যোগাযোগ করুন।', 6, 'blue'),
        ];
    }

    private static function doctors(): array
    {
        $specs = ['মেডিসিন', 'কার্ডিওলজি', 'গাইনোকোলজি', 'প্যাথলজি', 'রেডিওলজি', 'এন্ডোক্রাইনোলজি', 'নেফ্রোলজি', 'শিশুরোগ'];

        return array_map(fn ($spec) => [
            'name' => 'ডা. [চিকিৎসকের নাম]',
            'qualification' => 'MBBS, [ডিগ্রি]',
            'designation' => '[পদবি]',
            'speciality' => $spec,
            'schedule' => '[দিন ও সময়]',
            'fee' => '000',
            'image' => null,
        ], $specs);
    }

    private static function websiteInfo()
    {
        try {
            return class_exists(\App\Models\WebsiteInfo::class) ? \App\Models\WebsiteInfo::first() : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
