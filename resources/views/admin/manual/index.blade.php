@extends('admin.layouts.master')

@section('title', 'ব্যবহার নির্দেশিকা')

@push('css')
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap">
    <style>
        /* the layout's .app-main has overflow:auto, which stops the sticky table of contents */
        .app-main { overflow: visible !important; }
        .manual { font-family: 'Hind Siliguri', 'Source Sans 3', sans-serif; font-size: 1.02rem; line-height: 1.8; color: #263445; }
        .manual h2 { font-size: 1.45rem; font-weight: 700; margin: 0 0 .9rem; display: flex; align-items: center; gap: .6rem; }
        .manual h2 .num { width: 2.1rem; height: 2.1rem; border-radius: .7rem; display: inline-grid; place-items: center; background: #1557A6; color: #fff; font-size: 1rem; flex: none; }
        .manual h3 { font-size: 1.12rem; font-weight: 700; margin: 1.4rem 0 .5rem; }
        .manual .card:hover { transform: none; }
        .manual-section { scroll-margin-top: 4.5rem; margin-bottom: 1.5rem; }
        .manual-section > .card-body { padding: 1.6rem 1.8rem; }
        .manual-hero { background: linear-gradient(135deg, #1557A6, #0F3F7A); color: #fff; border-radius: 1.2rem; padding: 1.8rem 2rem; margin-bottom: 1.5rem; }
        .manual-hero h1 { font-size: 1.9rem; font-weight: 700; margin: 0 0 .4rem; }
        .manual-hero p { margin: 0; opacity: .9; }
        .toc { position: sticky; top: 1rem; max-height: calc(100vh - 2rem); overflow: auto; }
        .toc .nav-link { color: #3c4a58; padding: .3rem .75rem; border-radius: .5rem; font-size: .95rem; border-left: 3px solid transparent; }
        .toc .nav-link.active { color: #1557A6; background: #eef4fb; border-left-color: #1557A6; font-weight: 600; }
        .steps-list { counter-reset: step; list-style: none; padding: 0; margin: 0; }
        .steps-list > li { counter-increment: step; position: relative; padding: .2rem 0 .9rem 2.8rem; }
        .steps-list > li::before { content: counter(step, bengali); position: absolute; left: 0; top: .15rem; width: 1.9rem; height: 1.9rem; border-radius: 50%; display: grid; place-items: center; background: #e8f0fa; color: #1557A6; font-weight: 700; }
        .tip, .warn, .eg { border-radius: .8rem; padding: .8rem 1rem; margin: .9rem 0; }
        .tip { background: #ecfdf5; border: 1px solid #b7ebd4; }
        .warn { background: #fff7ed; border: 1px solid #fcd9b0; }
        .eg { background: #f4f8fc; border: 1px solid #dbe5f0; }
        .tip b:first-child, .warn b:first-child, .eg b:first-child { display: block; margin-bottom: .2rem; }
        .manual code, .manual kbd { font-size: .9em; }
        .manual pre { background: #1d2733; color: #e6edf5; border-radius: .7rem; padding: .9rem 1rem; font-size: .85rem; }
        .manual table { font-size: .95rem; }
        .go-btn { font-family: 'Source Sans 3', sans-serif; }
        .check-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(15rem, 1fr)); gap: .75rem; }
        .check-item { display: flex; gap: .7rem; padding: .85rem 1rem; border: 1px solid #e3e8ee; border-radius: .9rem; background: #fff; }
        .check-item .n { flex: none; width: 1.8rem; height: 1.8rem; border-radius: 50%; display: grid; place-items: center; background: #1557A6; color: #fff; font-weight: 700; font-size: .9rem; }
        .check-item b { display: block; }
        .check-item small { color: #5a6876; line-height: 1.5; display: block; }
        mark { background: #fff3a3; padding: 0 .1rem; }
        @media print { .toc-col, .manual-tools, .go-btn, .app-header, .app-sidebar, .app-footer { display: none !important; } .manual-col { width: 100% !important; } .manual-section { break-inside: avoid-page; } }
    </style>
@endpush

@section('master_content')
@php
    $can = fn (string $p) => in_array($p, $permissions);
    $go = fn (string $route, string $perm, string $label, array $params = []) => $can($perm)
        ? '<a class="btn btn-sm btn-outline-primary go-btn ms-1" href="'.route($route, $params).'"><i class="fas fa-arrow-right me-1"></i>'.e($label).'</a>'
        : '';
    $sections = [
        'intro' => 'পরিচিতি',
        'install' => 'সিস্টেম চালু করা (প্রথমবার)',
        'checklist' => 'প্রথম দিনের সেটআপ — ক্রম অনুযায়ী',
        'settings' => 'সাধারণ সেটিংস ও ওয়েবসাইট থিম',
        'rules' => 'ব্যবসার নিয়ম (Business rules)',
        'branches' => 'শাখা (Branches)',
        'staff' => 'স্টাফ, রোল ও ডিসকাউন্ট সীমা',
        'departments' => 'বিভাগ, নমুনার ধরন ও ইউনিট',
        'tests' => 'টেস্ট যোগ করা',
        'packages' => 'হেলথ প্যাকেজ',
        'referrers' => 'রেফারার (Ref. by)',
        'billing' => 'বিল করা (কাউন্টার)',
        'invoice' => 'ইনভয়েস, বকেয়া আদায় ও বাতিল',
        'reports' => 'ইনভয়েস তালিকা, দৈনিক হিসাব ও বকেয়া তালিকা',
        'patients' => 'রোগী ও রোগীর অ্যাকাউন্ট',
        'website' => 'ওয়েবসাইট ও রোগীর লগইন',
        'calc' => 'হিসাবের নিয়ম (উদাহরণসহ)',
        'faq' => 'সাধারণ সমস্যা ও সমাধান',
        'next' => 'শীঘ্রই আসছে',
    ];
@endphp

<div class="manual">
    <div class="manual-hero">
        <h1><i class="fas fa-book-open me-2"></i> ব্যবহার নির্দেশিকা</h1>
        <p>শুরু থেকে প্রতিদিনের কাজ পর্যন্ত — ধাপে ধাপে। যেকোনো অংশে যেতে বাঁ পাশের সূচি ব্যবহার করুন, অথবা নিচে খুঁজুন।</p>
    </div>

    <div class="row g-4">
        <div class="col-lg-3 toc-col">
            <div class="toc card">
                <div class="card-body p-2">
                    <div class="manual-tools p-2">
                        <input type="search" class="form-control form-control-sm mb-2" id="manualSearch" placeholder="খুঁজুন… যেমন: ডিসকাউন্ট" aria-label="নির্দেশিকায় খুঁজুন">
                        <button class="btn btn-sm btn-light w-100" type="button" onclick="window.print()"><i class="fas fa-print me-1"></i> প্রিন্ট / PDF</button>
                    </div>
                    <nav id="manualToc" class="nav flex-column">
                        @foreach ($sections as $id => $label)
                            <a class="nav-link" href="#{{ $id }}">{{ $loop->iteration }}. {{ $label }}</a>
                        @endforeach
                    </nav>
                </div>
            </div>
        </div>

        <div class="col-lg-9 manual-col">
            <p class="text-muted small d-none" id="noResult">কিছু পাওয়া যায়নি। অন্য শব্দ দিয়ে খুঁজুন।</p>

            {{-- 1 --}}
            <section class="card manual-section" id="intro">
                <div class="card-body">
                    <h2><span class="num">১</span> পরিচিতি</h2>
                    <p>এই সফটওয়্যার দিয়ে একটি ডায়াগনস্টিক সেন্টারের সব কাজ এক জায়গায় করা যায়:</p>
                    <ul>
                        <li><b>টেস্ট ও প্যাকেজের তালিকা</b> — মূল্য, প্রস্তুতি, রিপোর্টের সময় ও নরমাল রেঞ্জসহ।</li>
                        <li><b>কাউন্টারে বিল</b> — রোগী খোঁজা/যোগ করা, ডিসকাউন্ট, পেমেন্ট, ইনভয়েস ও মানি রিসিট প্রিন্ট।</li>
                        <li><b>বকেয়া আদায়, বাতিল ও রিফান্ড</b> — প্রতিটি টাকার হিসাব রসিদসহ।</li>
                        <li><b>একাধিক শাখা</b> — প্রতিটি শাখার আলাদা বিল নম্বর ও হিসাব।</li>
                        <li><b>ওয়েবসাইট</b> — অ্যাডমিন থেকে দেওয়া টেস্ট, প্যাকেজ ও শাখার তথ্য স্বয়ংক্রিয়ভাবে ওয়েবসাইটে দেখায়।</li>
                        <li><b>রোগীর অ্যাকাউন্ট</b> — রোগী মোবাইল নম্বর দিয়ে লগইন করে নিজের ও পরিবারের বিল দেখতে পারেন।</li>
                    </ul>
                    <div class="tip"><b>মনে রাখুন</b> বাঁ পাশের মেনুতে আপনি শুধু সেই অংশগুলো দেখবেন, যেগুলোর অনুমতি (permission) আপনার রোলে আছে। কোনো মেনু না দেখলে অ্যাডমিনকে বলুন।</div>
                </div>
            </section>

            {{-- 2 --}}
            <section class="card manual-section" id="install">
                <div class="card-body">
                    <h2><span class="num">২</span> সিস্টেম চালু করা (প্রথমবার)</h2>
                    <p>এই অংশটি যিনি সার্ভারে সফটওয়্যার বসাবেন (ডেভেলপার/আইটি) তাঁর জন্য। একবার করলেই হয়।</p>
                    <ol class="steps-list">
                        <li><b>প্রজেক্ট সেটআপ:</b> প্রজেক্ট ফোল্ডারে নিচের কমান্ডগুলো চালান।
<pre>composer install
cp .env.example .env      # তারপর .env-এ ডাটাবেস, APP_URL ইত্যাদি দিন
php artisan key:generate
php artisan migrate
php artisan db:seed --class=CatalogPermissionSeeder
php artisan db:seed --class=BillingPermissionSeeder
php artisan storage:link</pre>
                            একদম নতুন ডাটাবেসে <code>php artisan migrate --seed</code> দিলে অ্যাডমিন, রোল ও অনুমতি একবারে তৈরি হয়।
                        </li>
                        <li><b>লাইভ সার্ভারে:</b> <code>.env</code>-এ অবশ্যই <code>APP_ENV=production</code> ও <code>APP_DEBUG=false</code> দিন। <code>local</code> থাকলে প্রতিটি রিকোয়েস্টে ক্যাশ মুছে যায় এবং লগইনের ভুল-পাসওয়ার্ড লক কাজ করে না।</li>
                        <li><b>প্রথম লগইন:</b> অ্যাডমিন প্যানেলে <code>/admin/login</code> ঠিকানায় যান। ডেমো ডাটায় <code>admin@mail.com</code> / <code>password</code> দিয়ে ঢোকা যায়।</li>
                        <li><b>সাথে সাথে পাসওয়ার্ড বদলান</b> এবং ডেমো অ্যাকাউন্টগুলো (Viewer ইত্যাদি) মুছে দিন বা পাসওয়ার্ড বদলে দিন।</li>
                    </ol>
                    <div class="warn"><b>সতর্কতা</b> <code>DemoCatalogSeeder</code> শুধু পরীক্ষার জন্য (লোকাল মোডে নিজে থেকে চলে)। এর মূল্য ও নরমাল রেঞ্জ নমুনা মাত্র — লাইভে যাওয়ার আগে আপনার প্যাথলজিস্ট দিয়ে যাচাই করিয়ে নিন বা নিজের তালিকা দিন।</div>
                </div>
            </section>

            {{-- 3 --}}
            <section class="card manual-section" id="checklist">
                <div class="card-body">
                    <h2><span class="num">৩</span> প্রথম দিনের সেটআপ — ক্রম অনুযায়ী</h2>
                    <p>বিল শুরু করার আগে নিচের কাজগুলো এই ক্রমেই করুন। কারণ পরের ধাপ আগের ধাপের ওপর নির্ভর করে (যেমন টেস্ট যোগ করতে আগে বিভাগ লাগে)।</p>
                    <div class="check-grid">
                        @foreach ([
                            ['সাধারণ সেটিংস', 'সেন্টারের নাম, লোগো, ফোন, ঠিকানা, খোলার সময়, ওয়েবসাইটের রং।', '#settings'],
                            ['ব্যবসার নিয়ম', 'VAT, হোম কালেকশন চার্জ, কমিশন ও রিপোর্ট আটকানোর নিয়ম।', '#rules'],
                            ['শাখা', 'প্রতিটি শাখার কোড, ঠিকানা ও সময়।', '#branches'],
                            ['স্টাফ', 'প্রত্যেকের অ্যাকাউন্ট, রোল, শাখা ও ডিসকাউন্ট সীমা।', '#staff'],
                            ['বিভাগ', 'প্যাথলজি, রেডিওলজি… (টেস্টের আগে লাগবে)।', '#departments'],
                            ['নমুনা ও ইউনিট', 'Blood (EDTA), Urine… এবং mg/dL, g/dL…', '#departments'],
                            ['টেস্ট', 'মূল্য, প্রস্তুতি, রিপোর্টের সময়, প্যারামিটার ও রেঞ্জ।', '#tests'],
                            ['প্যাকেজ', 'কয়েকটি টেস্ট একসাথে কম দামে।', '#packages'],
                            ['রেফারার', 'যে ডাক্তার/এজেন্ট রোগী পাঠান।', '#referrers'],
                        ] as [$title, $text, $link])
                            <a class="check-item text-decoration-none text-reset" href="{{ $link }}">
                                <span class="n">{{ $loop->iteration }}</span>
                                <span><b>{{ $title }}</b><small>{{ $text }}</small></span>
                            </a>
                        @endforeach
                    </div>
                    <div class="tip"><b>এরপর</b> এই কাজগুলো শেষ হলে “নতুন বিল” থেকে বিল শুরু করুন। সেটআপ পরে যেকোনো সময় বদলানো যায় — পুরনো বিলের মূল্য তাতে বদলায় না।</div>
                </div>
            </section>

            {{-- 4 --}}
            <section class="card manual-section" id="settings">
                <div class="card-body">
                    <h2><span class="num">৪</span> সাধারণ সেটিংস ও ওয়েবসাইট থিম {!! $go('admin.settings.index', 'admin-view', 'Settings খুলুন') !!}</h2>
                    <p>মেনু: <b>Settings</b>। এখানকার তথ্য ওয়েবসাইট, ইনভয়েস ও রিসিটে ছাপা হয়।</p>
                    <ul>
                        <li><b>Site Name, Email, Phone, Address</b> — সেন্টারের নাম ও যোগাযোগ। ফোন নম্বরটি ওয়েবসাইটের হটলাইন হিসেবে দেখায়।</li>
                        <li><b>Opening hours (English / বাংলা)</b> — ওয়েবসাইটের ওপরের বারে দেখায়। দুই ভাষাতেই লিখুন।</li>
                        <li><b>Site Logo, Favicon</b> — লোগো ইনভয়েসের মাথায় ও ওয়েবসাইটে দেখায়।</li>
                        <li><b>Website theme</b> — ৬টি তৈরি রঙের থিম থেকে একটি বেছে নিন। প্রতিটি থিম পড়ার সুবিধার (কনট্রাস্ট) নিয়ম মেনে তৈরি, তাই যেকোনোটি নিরাপদ। সেভ করলে পুরো ওয়েবসাইটের রং বদলে যায়; অ্যাডমিন প্যানেলের রং বদলায় না।</li>
                        <li><b>Social Media Links</b> — ফেসবুক ইত্যাদির লিংক; ফাঁকা রাখলে ওয়েবসাইটে আইকন কাজ করবে না।</li>
                    </ul>
                </div>
            </section>

            {{-- 5 --}}
            <section class="card manual-section" id="rules">
                <div class="card-body">
                    <h2><span class="num">৫</span> ব্যবসার নিয়ম (Business rules) {!! $go('admin.business-settings.index', 'businesssetting-view', 'খুলুন') !!}</h2>
                    <p>মেনু: <b>Business rules</b>। এখানকার নিয়ম সব শাখায় একসাথে প্রযোজ্য।</p>
                    <table class="table table-sm table-bordered align-middle">
                        <thead class="table-light"><tr><th style="width:32%">ঘর</th><th>কী করে</th></tr></thead>
                        <tbody>
                            <tr><td>Invoice / Receipt prefix</td><td>বিল ও রসিদ নম্বরের শুরু। যেমন <code>INV-MAIN-2611-00001</code> = INV + শাখা কোড + বছর-মাস + ক্রমিক নম্বর। প্রতি মাসে প্রতিটি শাখার নম্বর ১ থেকে শুরু হয়।</td></tr>
                            <tr><td>VAT %</td><td>ডিসকাউন্টের পরের অংকের ওপর VAT বসে। VAT না নিলে ০ রাখুন (বর্তমান: {{ $rules['vat'] }}%)।</td></tr>
                            <tr><td>Home collection charge</td><td>বিলে “বাসায় নমুনা সংগ্রহ” চালু করলে এই টাকা যোগ হয় (বর্তমান: ৳{{ number_format($rules['home']) }})।</td></tr>
                            <tr><td>Default report time</td><td>যে টেস্টে রিপোর্টের সময় দেওয়া নেই, সেখানে এই সময় ধরা হয়।</td></tr>
                            <tr><td>Round to whole taka</td><td>চালু থাকলে মোট টাকা পূর্ণ টাকায় গোল করা হয় (যেমন ১০৫৭.৮৫ → ১০৫৮)। পার্থক্যটি “Round off” হিসেবে দেখায়।</td></tr>
                            <tr><td>Referral commission</td><td>রেফারার (ডাক্তার/এজেন্ট)-কে কমিশন দেওয়া হবে কি না। <b>Who bears the discount</b> — “Company” মানে ডিসকাউন্ট সেন্টার বহন করবে, কমিশন পুরো মূল্যের ওপর হিসাব হবে। কমিশনের হিসাব ও পরিশোধ পরের ধাপে আসছে।</td></tr>
                            <tr><td>Hold report until due is paid</td><td>চালু থাকলে বকেয়া থাকা বিলে “রিপোর্ট আটকে রাখুন” দেখায় এবং ওয়েবসাইটেও রিপোর্ট দেখা যাবে না।</td></tr>
                            <tr><td>Patients must set their own password</td><td>রোগীর প্রথম পাসওয়ার্ড তাঁর মোবাইল নম্বর। চালু থাকলে প্রথম লগইনের পরই নতুন পাসওয়ার্ড দিতে হবে (চালু রাখার পরামর্শ দেওয়া হচ্ছে)।</td></tr>
                        </tbody>
                    </table>
                </div>
            </section>

            {{-- 6 --}}
            <section class="card manual-section" id="branches">
                <div class="card-body">
                    <h2><span class="num">৬</span> শাখা (Branches) {!! $go('admin.branches.index', 'branch-view', 'খুলুন') !!}</h2>
                    <ol class="steps-list">
                        <li><b>Add branch</b> চাপুন।</li>
                        <li><b>Code</b> দিন — ছোট ও ইংরেজিতে, যেমন <code>MAIN</code>, <code>UTR</code>। এটি বিল নম্বরে বসে, তাই পরে না বদলানোই ভালো।</li>
                        <li>নাম, ফোন, ঠিকানা ও খোলার সময় <b>ইংরেজি ও বাংলা</b> দুই ভাষায় দিন। <b>Facilities</b>-এ প্রতি লাইনে একটি সুবিধা লিখুন (যেমন প্যাথলজি, ইমেজিং) — ওয়েবসাইটে ছোট ট্যাগ হিসেবে দেখায়।</li>
                        <li><b>Google Maps link</b> দিলে ওয়েবসাইটের “গুগল ম্যাপ” বাটন সরাসরি সেখানে যাবে।</li>
                        <li>প্রধান শাখায় <b>Main branch</b> চালু করুন (একটিই হতে পারে)। ওয়েবসাইটে না দেখাতে চাইলে <b>Show on website</b> বন্ধ করুন।</li>
                    </ol>
                    <div class="tip"><b>পরামর্শ</b> কোনো শাখা বন্ধ হলে মুছবেন না — <b>Active</b> বন্ধ করে দিন। এতে পুরনো বিলের হিসাব ঠিক থাকে।</div>
                </div>
            </section>

            {{-- 7 --}}
            <section class="card manual-section" id="staff">
                <div class="card-body">
                    <h2><span class="num">৭</span> স্টাফ, রোল ও ডিসকাউন্ট সীমা {!! $go('admin.admins.index', 'admin-view', 'Users খুলুন') !!}</h2>
                    <p>মেনু: <b>Users → Admin</b>। প্রত্যেক কর্মীর জন্য আলাদা অ্যাকাউন্ট দিন — কে কোন বিল করেছে, কে টাকা নিয়েছে সব রেকর্ড থাকে।</p>
                    <ol class="steps-list">
                        <li><b>Create</b> চাপুন, নাম, ইমেইল ও পাসওয়ার্ড দিন।</li>
                        <li><b>Role</b> বেছে নিন (নিচের টেবিল দেখুন)।</li>
                        <li><b>Branch</b> — কর্মী যে শাখায় বসেন সেটি দিন। শাখা দিলে তিনি শুধু সেই শাখার বিল দেখতে ও করতে পারবেন। হেড অফিস/মালিকের ক্ষেত্রে ফাঁকা রাখুন — সব শাখা দেখবেন।</li>
                        <li><b>Max discount without approval (%)</b> — এই কর্মী নিজে সর্বোচ্চ কত % ছাড় দিতে পারবেন। এর বেশি দিলে ম্যানেজারের পাসওয়ার্ড লাগবে।</li>
                    </ol>
                    <table class="table table-sm table-bordered">
                        <thead class="table-light"><tr><th>রোল</th><th>কী করতে পারেন</th></tr></thead>
                        <tbody>
                            <tr><td>Super Admin</td><td>সবকিছু (সেটিংস, রোল, শাখা, ক্যাটালগ, বিল, বাতিল)।</td></tr>
                            <tr><td>Manager</td><td>বিল, বকেয়া আদায়, বিল বাতিল, রোগী ও রেফারার, টেস্ট ও প্যাকেজ যোগ/সম্পাদনা।</td></tr>
                            <tr><td>Receptionist</td><td>রোগী যোগ, নতুন বিল, বকেয়া আদায়। বিল বাতিল করতে পারেন না।</td></tr>
                            <tr><td>Accountant</td><td>বিল দেখা ও বকেয়া আদায়।</td></tr>
                        </tbody>
                    </table>
                    <p>রোলের অনুমতি বদলাতে: <b>Security → Role → Assign permissions</b>।</p>
                    <div class="eg"><b>উদাহরণ</b> রিসেপশনিস্টের সীমা ১০%, ম্যানেজারের ৩০%। রিসেপশনিস্ট ১৫% ছাড় দিতে চাইলে বিলের ঘরে ম্যানেজার নিজের নাম বেছে পাসওয়ার্ড দেবেন। বিলে লেখা থাকবে কে অনুমোদন করেছেন।</div>
                </div>
            </section>

            {{-- 8 --}}
            <section class="card manual-section" id="departments">
                <div class="card-body">
                    <h2><span class="num">৮</span> বিভাগ, নমুনার ধরন ও ইউনিট {!! $go('admin.departments.index', 'department-view', 'বিভাগ') !!} {!! $go('admin.sample-types.index', 'sampletype-view', 'নমুনা') !!} {!! $go('admin.units.index', 'unit-view', 'ইউনিট') !!}</h2>
                    <h3>বিভাগ (Departments)</h3>
                    <p>প্যাথলজি, বায়োকেমিস্ট্রি, রেডিওলজি ইত্যাদি। প্রতিটি টেস্ট একটি বিভাগের অধীনে থাকে। ওয়েবসাইটের “ডায়াগনস্টিক বিভাগ” অংশ ও টেস্ট ফিল্টার এখান থেকে তৈরি হয়। আইকন বাছাই করুন, ছোট বিবরণ ও <b>Highlights</b> (প্রতি লাইনে একটি) দিন।</p>
                    <div class="tip"><b>মনে রাখুন</b> যে বিভাগে টেস্ট আছে সেটি মোছা যায় না — আগে টেস্ট সরান অথবা বিভাগটি Inactive করুন।</div>
                    <h3>নমুনার ধরন (Sample types)</h3>
                    <p>Blood (EDTA), Serum, Urine, Stool… সাথে টিউবের তথ্য (যেমন “EDTA tube – purple cap”)। পরের ধাপে নমুনার লেবেলে ছাপা হবে।</p>
                    <h3>ইউনিট (Units)</h3>
                    <p>ফলাফলের একক: mg/dL, g/dL, %, U/L… টেস্টের প্যারামিটারে ব্যবহার হয়।</p>
                </div>
            </section>

            {{-- 9 --}}
            <section class="card manual-section" id="tests">
                <div class="card-body">
                    <h2><span class="num">৯</span> টেস্ট যোগ করা {!! $go('admin.lab-tests.create', 'labtest-create', 'নতুন টেস্ট') !!}</h2>
                    <p>মেনু: <b>Test catalog → Tests → Add test</b>। একটি টেস্টের ফর্ম কয়েকটি অংশে ভাগ করা:</p>
                    <h3>ক) মূল তথ্য</h3>
                    <ul>
                        <li><b>Department, Sample type</b> বেছে নিন।</li>
                        <li><b>Code</b> — ছোট ও অনন্য, যেমন <code>CBC</code>। বিলে খোঁজার সময় কোড লিখলেও টেস্ট আসে।</li>
                        <li><b>Name (English / বাংলা)</b> — ইনভয়েসে ইংরেজি নাম ছাপা হয়; ওয়েবসাইটে দুটোই।</li>
                        <li><b>Report in (hours)</b> — কত ঘণ্টায় রিপোর্ট হবে। বিলে “Delivery” সময় এখান থেকে হিসাব হয় (বিলের সবচেয়ে দেরির টেস্ট ধরে)।</li>
                    </ul>
                    <h3>খ) মূল্য</h3>
                    <ul>
                        <li><b>Regular price</b> — আসল মূল্য।</li>
                        <li><b>Offer price</b> (ঐচ্ছিক) — অফার চললে দিন; বিলে অফার মূল্য ধরা হবে এবং ওয়েবসাইটে আসল মূল্য কেটে দেখাবে। অফার শেষ হলে ঘরটি ফাঁকা করুন।</li>
                        <li><b>Cost</b> — রিএজেন্ট/খরচ। পাশে প্রতি টেস্টে লাভ (Margin) দেখায়; ভবিষ্যতে লাভের রিপোর্টে লাগবে।</li>
                    </ul>
                    <h3>গ) রোগীর তথ্য</h3>
                    <p><b>Preparation</b> (যেমন “৮–১০ ঘণ্টা উপবাস প্রয়োজন”) ও <b>About this test</b> — ওয়েবসাইটে দেখায়। দুই ভাষাতেই লিখুন।</p>
                    <h3>ঘ) কোথায় পাওয়া যায় ও দৃশ্যমানতা</h3>
                    <ul>
                        <li><b>Available at</b> — যে শাখাগুলোতে টেস্টটি হয় শুধু সেগুলো টিক দিন। <b>সব ফাঁকা রাখলে সব শাখায় পাওয়া যায়।</b> যে শাখায় টেস্ট নেই, সেখানে বিলে টেস্টটি আসবে না।</li>
                        <li><b>Active</b> — বন্ধ করলে আর বিল করা যাবে না। <b>Show on website</b> — ওয়েবসাইটে দেখাবে কি না। <b>Popular</b> — হোম পেজে ও বিলের “Quick add” বাটনে আসে।</li>
                    </ul>
                    <h3>ঙ) রিপোর্ট সেটআপ</h3>
                    <p>তিন ধরনের রিপোর্ট আছে:</p>
                    <ul>
                        <li><b>Parameter</b> — রক্ত, প্রস্রাব, বায়োকেমিস্ট্রি। প্রতিটি মান (যেমন Haemoglobin, WBC) একটি প্যারামিটার।</li>
                        <li><b>Template</b> — এক্স-রে, আল্ট্রাসাউন্ড, ইসিজি। কনসালট্যান্ট যে লেখা থেকে রিপোর্ট শুরু করবেন সেটি দিন (Findings / Impression)।</li>
                        <li><b>Culture</b> — কালচার ও সেনসিটিভিটি (ফলাফল দেওয়ার স্ক্রিন পরের ধাপে আসছে)।</li>
                    </ul>
                    <p><b>প্যারামিটার যোগ করতে</b> <b>Add parameter</b> চাপুন এবং দিন:</p>
                    <ul>
                        <li><b>Code</b> (ইংরেজি বড় হাতের অক্ষর, যেমন <code>HB</code>), <b>Name</b>, <b>Unit</b>।</li>
                        <li><b>Input</b> — Number (সংখ্যা), Text (লেখা), Option list (নির্দিষ্ট বিকল্প, যেমন “Nil, Trace, +, ++” — কমা দিয়ে লিখুন)।</li>
                        <li><b>Group</b> (ঐচ্ছিক) — যেমন “Differential count”; রিপোর্টে একসাথে দেখাবে।</li>
                        <li><b>Formula</b> — যে মান হিসাব করে বের হয়, যেমন LDL = <code>TC - HDL - TG / 5</code>। এখানে অন্য প্যারামিটারের Code ব্যবহার করুন।</li>
                        <li><b>Reference ranges</b> — নরমাল রেঞ্জ। পুরুষ/মহিলা ও বয়স অনুযায়ী আলাদা সারি দেওয়া যায়। বয়স ফাঁকা = সব বয়স। <b>Low</b> ও <b>High</b> দিলে ফলাফল কম/বেশি হলে রিপোর্টে L/H চিহ্ন বসবে। <b>Printed text</b>-এ লিখলে রিপোর্টে সেটিই ছাপা হবে (যেমন “&lt; 200” বা “Negative”)।</li>
                    </ul>
                    <div class="eg"><b>উদাহরণ: Haemoglobin</b> সারি ১: Male, Low 13, High 17 · সারি ২: Female, Low 12, High 15। ৩৫ বছরের মহিলার ফল ১১.২ হলে রিপোর্টে “L” আসবে এবং রেঞ্জ “12 - 15” ছাপা হবে।</div>
                    <p>প্যারামিটারের ক্রম ↑ ↓ বাটনে বদলানো যায় — রিপোর্টে এই ক্রমেই ছাপা হবে।</p>
                    <div class="warn"><b>সতর্কতা</b> কোনো প্যাকেজে থাকা টেস্ট মোছা যায় না। টেস্ট বন্ধ করতে চাইলে <b>Active</b> বন্ধ করুন।</div>
                </div>
            </section>

            {{-- 10 --}}
            <section class="card manual-section" id="packages">
                <div class="card-body">
                    <h2><span class="num">১০</span> হেলথ প্যাকেজ {!! $go('admin.packages.create', 'package-create', 'নতুন প্যাকেজ') !!}</h2>
                    <ol class="steps-list">
                        <li><b>Code</b>, নাম (দুই ভাষায়), ছোট এক লাইনের বিবরণ ও আইকন দিন।</li>
                        <li><b>Included tests</b> থেকে টেস্টগুলো টিক দিন (সার্চ বক্সে নাম লিখে খুঁজুন)। কমপক্ষে ২টি টেস্ট লাগবে।</li>
                        <li>ডান পাশে <b>Tests separately</b> দেখায় আলাদা কিনলে কত হতো। <b>Package price</b> দিন — রোগীর সাশ্রয় সাথে সাথে দেখাবে। প্যাকেজের দাম আলাদা দামের চেয়ে বেশি দেওয়া যাবে না।</li>
                        <li><b>Who & when</b> — শুধু মহিলা/পুরুষের জন্য, বয়সসীমা এবং কবে থেকে কবে পর্যন্ত চলবে (অফার প্যাকেজের জন্য)। মেয়াদ শেষ হলে ওয়েবসাইট ও বিল থেকে নিজে থেকেই সরে যায়।</li>
                        <li><b>Featured</b> — ওয়েবসাইটে বড় করে হাইলাইট হয় (একটিই হতে পারে)।</li>
                    </ol>
                    <div class="eg"><b>প্যাকেজের টাকা কীভাবে ভাগ হয়</b> হিসাব ও কমিশনের জন্য প্যাকেজের দাম প্রতিটি টেস্টের দামের অনুপাতে ভাগ হয়। যেমন CBC ৳৪০০ + ECG ৳৩০০ = ৳৭০০, প্যাকেজ ৳৫৬০ হলে CBC পায় ৩২০ আর ECG পায় ২৪০ — যোগফল সবসময় প্যাকেজের দামের সমান।</div>
                </div>
            </section>

            {{-- 11 --}}
            <section class="card manual-section" id="referrers">
                <div class="card-body">
                    <h2><span class="num">১১</span> রেফারার (Ref. by) {!! $go('admin.referrers.index', 'referrer-view', 'খুলুন') !!}</h2>
                    <p>যে ডাক্তার, পল্লী চিকিৎসক (RMP), এজেন্ট বা প্রতিষ্ঠান রোগী পাঠান। মেনু: <b>Billing → Referrers → Add referrer</b>।</p>
                    <ul>
                        <li><b>Type</b>, নাম, ডিগ্রি (যেমন MBBS, FCPS), প্রতিষ্ঠান ও মোবাইল দিন। ডিগ্রি ইনভয়েসের “Ref. by”-তে ছাপা হয়।</li>
                        <li><b>Eligible for commission</b> — এই রেফারার কমিশন পাবেন কি না।</li>
                        <li>বিল করার সময় নাম বা মোবাইল লিখলেই রেফারার খুঁজে পাওয়া যায়। কেউ না পাঠালে ফাঁকা রাখুন — “Self” ছাপা হবে।</li>
                    </ul>
                </div>
            </section>

            {{-- 12 --}}
            <section class="card manual-section" id="billing">
                <div class="card-body">
                    <h2><span class="num">১২</span> বিল করা (কাউন্টার) {!! $go('admin.invoices.create', 'invoice-create', 'নতুন বিল') !!}</h2>
                    <p>মেনু: <b>Billing → New bill</b>। প্রতিদিনের সবচেয়ে বেশি ব্যবহৃত স্ক্রিন।</p>
                    <h3>ধাপ ১ — রোগী</h3>
                    <ul>
                        <li><b>পুরনো রোগী:</b> ওপরের সার্চ বক্সে মোবাইল নম্বর, নাম বা রোগীর আইডি (P-000001) লিখুন → তালিকা থেকে বেছে নিন। আগের বকেয়া থাকলে লাল করে দেখাবে।</li>
                        <li><b>নতুন রোগী:</b> নাম, মোবাইল, লিঙ্গ (M/F/O) ও বয়স (বছর / মাস / দিন) দিন। শিশুর ক্ষেত্রে মাস ও দিন দিন — নরমাল রেঞ্জ বয়সের ওপর নির্ভর করে।</li>
                        <li><b>এক মোবাইল = এক অ্যাকাউন্ট।</b> মোবাইল লিখলেই দেখাবে নম্বরটি নতুন কি না। নম্বরটি আগে থেকে থাকলে “Already registered: …” দেখাবে — নামের ওপর ক্লিক করে সেই রোগী বেছে নিন।</li>
                        <li><b>একই নম্বরে পরিবারের অন্য সদস্য</b> (স্ত্রী, সন্তান) হলে <b>“Add as a new family member on this number”</b> টিক দিন। তখন একই অ্যাকাউন্টে তাঁর আলাদা প্রোফাইল তৈরি হবে, যাতে রিপোর্টে সঠিক নাম, বয়স ও লিঙ্গ যায়।</li>
                    </ul>
                    <h3>ধাপ ২ — টেস্ট ও প্যাকেজ</h3>
                    <ul>
                        <li>“Type a test or package name / code” বক্সে লিখুন (কীবোর্ডে <kbd>/</kbd> চাপলে সরাসরি এখানে আসে) → ↑ ↓ দিয়ে বেছে <kbd>Enter</kbd>, অথবা মাউসে ক্লিক।</li>
                        <li>জনপ্রিয় টেস্ট <b>Quick add</b> বাটনে এক ক্লিকে যোগ হয়। ভুল হলে লাইনের ✕ চাপুন।</li>
                        <li>একই টেস্ট দুবার (আলাদা করে ও প্যাকেজের ভেতরে) যোগ করা যায় না — সিস্টেম সতর্ক করবে।</li>
                    </ul>
                    <h3>ধাপ ৩ — ডান পাশের ঘর</h3>
                    <ul>
                        <li><b>Branch</b> — হেড অফিসের কর্মী হলে শাখা বেছে নিন; শাখার কর্মীর ক্ষেত্রে নিজে থেকে বসে যায়।</li>
                        <li><b>Ref. by</b> — রেফারারের নাম লিখে বেছে নিন।</li>
                        <li><b>Home sample collection</b> — বাসা থেকে নমুনা নিলে চালু করুন; চার্জ যোগ হবে।</li>
                        <li><b>Discount</b> — অংক লিখে <b>%</b> বা <b>৳</b> বেছে নিন। ছাড় দিলে <b>কারণ লেখা বাধ্যতামূলক</b>। আপনার সীমার বেশি হলে হলুদ ঘরে ম্যানেজারের নাম ও পাসওয়ার্ড দিতে হবে।</li>
                        <li><b>Paid now</b> — রোগী এখন কত দিচ্ছেন (নিজে থেকে পুরো টাকা বসে; কম নিলে অংক বদলান)। <b>Method</b> — Cash, bKash, Nagad, Rocket, Card, Bank। ক্যাশ ছাড়া অন্য হলে ট্রানজেকশন আইডি দিন। নিচে <b>Due</b> দেখায়।</li>
                    </ul>
                    <h3>ধাপ ৪ — সেভ</h3>
                    <ul>
                        <li><b>Save & print</b> — বিল সেভ হয়ে প্রিন্ট পেজ খোলে এবং প্রিন্ট ডায়ালগ আসে। ওপরের বার থেকে কাগজ <b>A4 / A5</b> বেছে নেওয়া যায়।</li>
                        <li><b>Save only</b> — শুধু সেভ, ইনভয়েস পেজ খোলে।</li>
                    </ul>
                    <div class="tip"><b>নিশ্চিন্ত থাকুন</b> স্ক্রিনে দেখানো হিসাব শুধু আগাম দেখার জন্য; সেভ করার সময় সার্ভার ডাটাবেসের মূল্য থেকে আবার হিসাব করে। বাটনে দুবার চাপলেও দুটি বিল হয় না।</div>
                </div>
            </section>

            {{-- 13 --}}
            <section class="card manual-section" id="invoice">
                <div class="card-body">
                    <h2><span class="num">১৩</span> ইনভয়েস, বকেয়া আদায় ও বাতিল</h2>
                    <p>যেকোনো বিলের নম্বরে ক্লিক করলে ইনভয়েস পেজ খোলে। এখানে টেস্টের তালিকা (প্যাকেজের ভেতরের টেস্টসহ), প্রতিটি টেস্টের রিপোর্টের সময়, ছাড়, পেমেন্টের ইতিহাস ও রোগীর তথ্য থাকে।</p>
                    <h3>বকেয়া আদায়</h3>
                    <ol class="steps-list">
                        <li><b>Collect due</b> চাপুন।</li>
                        <li>অংক দিন (পুরো বা আংশিক), পদ্ধতি ও ট্রানজেকশন আইডি দিন → <b>Receive</b>।</li>
                        <li>সবুজ বার্তায় <b>Print money receipt</b> চেপে রসিদ প্রিন্ট করুন। প্রতিটি পেমেন্টের রসিদ পরেও পেমেন্ট তালিকার 🧾 বাটন থেকে প্রিন্ট করা যায়।</li>
                    </ol>
                    <h3>বিল বাতিল ও রিফান্ড</h3>
                    <p><b>Cancel</b> চেপে কারণ লিখুন। রোগী আগে যা দিয়েছেন তা পুরোটাই রিফান্ড হিসেবে রেকর্ড হয় এবং রিফান্ড রসিদ তৈরি হয়। বিলের সব টেস্টও বাতিল হয়ে যায়। (এ সুবিধা শুধু যাঁদের অনুমতি আছে, যেমন ম্যানেজার।)</p>
                    <div class="warn"><b>ভুল বিল হলে</b> বিল মোছা যায় না — হিসাব স্বচ্ছ রাখার জন্য। ভুল হলে বাতিল করে নতুন বিল করুন। একটি টেস্ট আলাদা করে ফেরত দেওয়ার সুবিধা এখনো নেই।</div>
                    <h3>রিপোর্ট আটকে রাখা</h3>
                    <p>Business rules-এ চালু থাকলে বকেয়া থাকা বিলে হলুদ বার্তা দেখায়: “The report must be held until the due is paid”। বকেয়া পরিশোধ হলে বার্তা চলে যায়।</p>
                </div>
            </section>

            {{-- 14 --}}
            <section class="card manual-section" id="reports">
                <div class="card-body">
                    <h2><span class="num">১৪</span> ইনভয়েস তালিকা, দৈনিক হিসাব ও বকেয়া তালিকা {!! $go('admin.invoices.index', 'invoice-view', 'Invoices') !!} {!! $go('admin.invoices.index', 'invoice-view', 'Due list', ['status' => 'due']) !!}</h2>
                    <p><b>Invoices</b> মেনুতে তারিখ (From / To), শাখা ও অবস্থা দিয়ে ফিল্টার করুন। <b>Today</b> ও <b>This month</b> বাটনে দ্রুত বাছাই হয়। সার্চে বিল নম্বর, নাম বা মোবাইল লিখুন।</p>
                    <table class="table table-sm table-bordered">
                        <thead class="table-light"><tr><th style="width:28%">ঘর</th><th>মানে</th></tr></thead>
                        <tbody>
                            <tr><td>Bills</td><td>বাছাই করা সময়ে কতটি বিল হয়েছে (বাতিল ছাড়া)।</td></tr>
                            <tr><td>Gross</td><td>ছাড়ের আগে মোট মূল্য।</td></tr>
                            <tr><td>Discount</td><td>মোট কত ছাড় দেওয়া হয়েছে।</td></tr>
                            <tr><td>Net billed</td><td>ছাড়ের পর (VAT ও চার্জসহ) মোট বিল।</td></tr>
                            <tr><td>Cash received</td><td>এই সময়ে আসলে হাতে আসা টাকা — পুরনো বকেয়া আদায়ও এতে আছে, রিফান্ড বাদ দিয়ে। দিনশেষে ক্যাশ মেলাতে এই অংকটি দেখুন।</td></tr>
                            <tr><td>Due on these bills</td><td>এই সময়ের বিলগুলোর মধ্যে এখনো কত বাকি।</td></tr>
                        </tbody>
                    </table>
                    <p><b>Due list</b> মেনুতে সব বকেয়া বিল একসাথে দেখায়, ওপরে মোট বকেয়া। রোগী এলে এখান থেকে বিল খুলে আদায় করুন।</p>
                </div>
            </section>

            {{-- 15 --}}
            <section class="card manual-section" id="patients">
                <div class="card-body">
                    <h2><span class="num">১৫</span> রোগী ও রোগীর অ্যাকাউন্ট {!! $go('admin.patients.index', 'patient-view', 'Patients') !!}</h2>
                    <ul>
                        <li><b>Patients</b> মেনুতে সব রোগী — আইডি, মোবাইল, বয়স, মোট বিল ও বকেয়া।</li>
                        <li>নামে ক্লিক করলে রোগীর পাতা: সব বিলের ইতিহাস, মোট বকেয়া এবং <b>New bill</b> বাটন (রোগী আগে থেকেই বসানো থাকে)।</li>
                        <li><b>Register patient</b> দিয়ে বিল ছাড়াও রোগী যোগ করা যায়।</li>
                        <li>বিল সেভ হওয়ার সময় রোগীর নাম, বয়স ও লিঙ্গ বিলের সাথে সংরক্ষিত হয় — পরে রোগীর তথ্য বদলালেও পুরনো বিল ও রিপোর্ট ঠিক থাকে।</li>
                    </ul>
                    <h3>ওয়েবসাইট অ্যাকাউন্ট (Website account কার্ড)</h3>
                    <ul>
                        <li><b>Login phone</b> — রোগীর লগইন নম্বর। <b>Password</b> — “Default (phone number)” মানে রোগী এখনো নিজের পাসওয়ার্ড দেননি।</li>
                        <li><b>Family on this number</b> — একই নম্বরের অন্য সদস্যরা।</li>
                        <li>রোগী পাসওয়ার্ড ভুলে গেলে <b>Reset password to phone number</b> চাপুন — পাসওয়ার্ড আবার মোবাইল নম্বর হয়ে যাবে। পরিচয় নিশ্চিত না হয়ে রিসেট করবেন না।</li>
                    </ul>
                </div>
            </section>

            {{-- 16 --}}
            <section class="card manual-section" id="website">
                <div class="card-body">
                    <h2><span class="num">১৬</span> ওয়েবসাইট ও রোগীর লগইন</h2>
                    <h3>ওয়েবসাইট নিজে থেকেই আপডেট হয়</h3>
                    <ul>
                        <li>টেস্ট, প্যাকেজ, বিভাগ ও শাখা — যেগুলোতে <b>Show on website</b> ও <b>Active</b> চালু, সেগুলো ওয়েবসাইটে দেখায়। মূল্য বদলালে ওয়েবসাইটেও সাথে সাথে বদলায়।</li>
                        <li>ওয়েবসাইট বাংলায় খোলে; ওপরের বারে <b>English</b> চাপলে ইংরেজি হয়। তাই সব নাম ও বিবরণ দুই ভাষায় লিখুন।</li>
                        <li>রং বদলাতে: Settings → Website theme।</li>
                    </ul>
                    <h3>রোগীরা কীভাবে লগইন করবেন (রোগীকে এভাবে বুঝিয়ে দিন)</h3>
                    <ol class="steps-list">
                        <li>ওয়েবসাইটের একদম ওপরে <b>“লগইন”</b> চাপুন।</li>
                        <li>মোবাইল নম্বর দিন। <b>প্রথমবার পাসওয়ার্ডও একই মোবাইল নম্বর।</b></li>
                        <li>প্রথম লগইনের পর নিজের পছন্দের নতুন পাসওয়ার্ড দিতে হবে (কমপক্ষে ৬ অক্ষর, মোবাইল নম্বর নয়)।</li>
                        <li>এরপর অ্যাকাউন্টে পরিবারের সবার প্রোফাইল, সব বিল, পরিশোধ ও বকেয়া দেখা যাবে।</li>
                    </ol>
                    <div class="tip"><b>নিরাপত্তা</b> পরপর ৫ বার ভুল পাসওয়ার্ড দিলে নম্বরটি কিছুক্ষণের জন্য আটকে যায়। একজন রোগী অন্য কারো বিল কখনো দেখতে পারেন না।</div>
                </div>
            </section>

            {{-- 17 --}}
            <section class="card manual-section" id="calc">
                <div class="card-body">
                    <h2><span class="num">১৭</span> হিসাবের নিয়ম (উদাহরণসহ)</h2>
                    <table class="table table-sm table-bordered">
                        <tbody>
                            <tr><td style="width:30%">Subtotal</td><td>সব লাইনের দামের যোগফল (অফার থাকলে অফার মূল্য, প্যাকেজ হলে প্যাকেজের দাম)।</td></tr>
                            <tr><td>Discount</td><td>% হলে Subtotal-এর % ; ৳ হলে নির্দিষ্ট অংক (Subtotal-এর বেশি নয়)।</td></tr>
                            <tr><td>VAT</td><td>(Subtotal − Discount) × VAT%</td></tr>
                            <tr><td>Net payable</td><td>Subtotal − Discount + VAT + Home collection charge → পূর্ণ টাকায় গোল</td></tr>
                            <tr><td>Due</td><td>Net payable − মোট পরিশোধ (রিফান্ড বাদে)</td></tr>
                        </tbody>
                    </table>
                    <div class="eg"><b>উদাহরণ</b>
                        CBC ৳৪০০ + Heart Health Package ৳৩,২০০ + USG ৳১,৫০০ = Subtotal ৳৫,১০০<br>
                        ১৫% ছাড় = ৳৭৬৫ → Net payable ৳৪,৩৩৫<br>
                        এখন দিলেন ৳৩,০০০ → Due ৳১,৩৩৫ → পরে বিকাশে ৳১,৩৩৫ দিলে বিল “Paid”।
                    </div>
                    <p>ছাড় প্রতিটি লাইনে দামের অনুপাতে ভাগ হয় (বিভাগভিত্তিক আয়ের হিসাবের জন্য)। Business rules-এ “Company bears the discount” থাকায় রেফারারের কমিশন ছাড়ের আগের পুরো মূল্যের ওপর হিসাব হবে।</p>
                </div>
            </section>

            {{-- 18 --}}
            <section class="card manual-section" id="faq">
                <div class="card-body">
                    <h2><span class="num">১৮</span> সাধারণ সমস্যা ও সমাধান</h2>
                    <div class="accordion" id="faqList">
                        @foreach ([
                            ['মেনুতে কোনো অংশ দেখা যাচ্ছে না।', 'আপনার রোলে সেই অনুমতি নেই। Super Admin → Security → Role → Assign permissions থেকে অনুমতি দিন। নতুন আপডেটের পর অনুমতি না দেখালে ডেভেলপারকে CatalogPermissionSeeder ও BillingPermissionSeeder চালাতে বলুন।'],
                            ['বিলে টেস্ট খুঁজে পাওয়া যাচ্ছে না।', 'টেস্টটি Inactive, অথবা “Available at”-এ এই শাখা টিক দেওয়া নেই। টেস্ট এডিট করে দেখুন।'],
                            ['প্যাকেজ বিলে আসছে না।', 'প্যাকেজটি Inactive অথবা মেয়াদ (Valid from / until) শেষ বা এখনো শুরু হয়নি।'],
                            ['“is already registered to …” লেখা আসছে।', 'নম্বরটি অন্য একজনের নামে আছে। একই রোগী হলে সার্চ থেকে তাঁকে বেছে নিন; পরিবারের অন্য সদস্য হলে “Add as a new family member” টিক দিন।'],
                            ['“added twice” লেখা আসছে।', 'একটি টেস্ট আলাদাভাবে এবং প্যাকেজের ভেতরে দুবার যোগ হয়েছে। যেকোনো একটি সরান।'],
                            ['ছাড় দিতে পারছি না।', 'আপনার সীমার বেশি ছাড়। ম্যানেজারের নাম বেছে তাঁর পাসওয়ার্ড দিতে হবে, অথবা ছাড় কমান। ছাড়ের কারণও লিখতে হবে।'],
                            ['ভুল বিল করে ফেলেছি।', 'Cancel করুন (কারণ লিখে) এবং সঠিক বিল নতুন করে করুন। পরিশোধিত টাকা রিফান্ড হিসেবে রেকর্ড হবে।'],
                            ['রোগী পাসওয়ার্ড ভুলে গেছেন।', 'Patients → রোগীর পাতা → Reset password to phone number। এরপর রোগী মোবাইল নম্বর দিয়ে লগইন করে নতুন পাসওয়ার্ড দেবেন।'],
                            ['রোগী লগইন করতে পারছেন না।', 'নম্বরটি 01XXXXXXXXX আকারে দিচ্ছেন কি না দেখুন। ৫ বার ভুল হলে এক মিনিট অপেক্ষা করতে বলুন। তারপরও না হলে পাসওয়ার্ড রিসেট করুন।'],
                            ['ওয়েবসাইটে নতুন টেস্ট দেখাচ্ছে না।', 'টেস্ট ও তার বিভাগ — দুটোতেই “Show on website” ও “Active” চালু আছে কি না দেখুন।'],
                            ['ইনভয়েসে সেন্টারের নাম/লোগো ভুল।', 'Settings-এ নাম, লোগো, ফোন ও ঠিকানা ঠিক করুন। শাখার ঠিকানা ও ফোন Branches থেকে আসে।'],
                        ] as [$q, $a])
                            <div class="accordion-item">
                                <h3 class="accordion-header m-0">
                                    <button class="accordion-button collapsed py-2" type="button" data-bs-toggle="collapse" data-bs-target="#faq{{ $loop->index }}" aria-expanded="false">{{ $q }}</button>
                                </h3>
                                <div id="faq{{ $loop->index }}" class="accordion-collapse collapse" data-bs-parent="#faqList">
                                    <div class="accordion-body">{{ $a }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            {{-- 19 --}}
            <section class="card manual-section" id="next">
                <div class="card-body">
                    <h2><span class="num">১৯</span> শীঘ্রই আসছে</h2>
                    <ul>
                        <li><b>ল্যাব:</b> নমুনা সংগ্রহ ও বারকোড লেবেল, ফলাফল দেওয়া, কনসালট্যান্টের যাচাই ও সই, QR কোডসহ রিপোর্ট PDF।</li>
                        <li><b>রোগীর পোর্টালে রিপোর্ট:</b> অনলাইনে রিপোর্ট ডাউনলোড, এসএমএস ও ওটিপি।</li>
                        <li><b>কমিশন:</b> রেফারারভিত্তিক হার, মাসিক কমিশন হিসাব ও পরিশোধ।</li>
                        <li><b>হিসাব:</b> খরচ, দৈনিক ক্যাশ ক্লোজিং, বিভাগভিত্তিক আয় ও লাভের রিপোর্ট।</li>
                    </ul>
                    <p class="text-muted small mb-0">নতুন অংশ যোগ হলে এই নির্দেশিকাও আপডেট হবে।</p>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection

@push('script')
    <script>
        (function () {
            // Simple search: hide sections without the words, highlight matches
            const input = document.getElementById('manualSearch');
            const sections = [...document.querySelectorAll('.manual-section')];
            const tocLinks = [...document.querySelectorAll('#manualToc .nav-link')];
            const originals = new Map(sections.map((s) => [s, s.innerHTML]));
            const esc = (s) => s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            let timer;
            input.addEventListener('input', () => {
                clearTimeout(timer);
                timer = setTimeout(() => {
                    const q = input.value.trim();
                    let shown = 0;
                    sections.forEach((s, i) => {
                        s.innerHTML = originals.get(s);
                        const match = !q || s.textContent.toLowerCase().includes(q.toLowerCase());
                        s.hidden = !match;
                        if (tocLinks[i]) tocLinks[i].hidden = !match;
                        if (match) shown++;
                        if (q && match) {
                            const re = new RegExp('(' + esc(q) + ')', 'gi');
                            const walker = document.createTreeWalker(s, NodeFilter.SHOW_TEXT);
                            const nodes = [];
                            while (walker.nextNode()) nodes.push(walker.currentNode);
                            nodes.forEach((n) => {
                                if (!re.test(n.nodeValue) || n.parentElement.closest('script,style')) return;
                                const span = document.createElement('span');
                                span.innerHTML = n.nodeValue.replace(/[&<>]/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;' }[c])).replace(re, '<mark>$1</mark>');
                                n.replaceWith(span);
                            });
                        }
                    });
                    document.getElementById('noResult').classList.toggle('d-none', shown > 0);
                }, 250);
            });

            // Jump to a section (also used by the setup checklist cards)
            document.querySelectorAll('.manual a[href^="#"]').forEach((a) => a.addEventListener('click', (e) => {
                const target = document.querySelector(a.getAttribute('href'));
                if (!target) return;
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                history.replaceState(null, '', a.getAttribute('href'));
            }));

            // Highlight the current section in the table of contents
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((e) => {
                    if (!e.isIntersecting) return;
                    tocLinks.forEach((a) => a.classList.toggle('active', a.getAttribute('href') === '#' + e.target.id));
                });
            }, { rootMargin: '0px 0px -70% 0px' });
            sections.forEach((s) => observer.observe(s));
        })();
    </script>
@endpush
