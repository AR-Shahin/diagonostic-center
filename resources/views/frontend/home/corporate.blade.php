<section class="section section-alt" aria-labelledby="corp-title">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6 reveal">
                <span class="eyebrow">কর্পোরেট হেলথকেয়ার</span>
                <h2 class="section-title text-start" id="corp-title">আপনার প্রতিষ্ঠানের <span class="grad">স্বাস্থ্যসেবার সঙ্গী</span></h2>
                <p class="section-sub text-start">কর্মীদের স্বাস্থ্য পরীক্ষা থেকে চাকরিপূর্ব মেডিকেল—প্রতিষ্ঠানের চাহিদা অনুযায়ী সাজানো সেবা।</p>

                <ul class="corp-list">
                    @foreach ($content['corporate'] as $item)
                        <li><span class="icon-bubble icon-sm"><i class="bi {{ $item['icon'] }}" aria-hidden="true"></i></span> {{ $item['title'] }}</li>
                    @endforeach
                </ul>
            </div>

            <div class="col-lg-6 reveal" style="--d:.1s">
                <form class="form-card" data-demo-form>
                    <h3>কোটেশনের অনুরোধ</h3>
                    <p class="text-body-secondary">তথ্য দিন, আমরা যোগাযোগ করব।</p>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label" for="corpCompany">প্রতিষ্ঠানের নাম</label>
                            <input class="form-control" id="corpCompany" name="company" type="text" autocomplete="organization" required>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label" for="corpName">যোগাযোগের ব্যক্তি</label>
                            <input class="form-control" id="corpName" name="name" type="text" autocomplete="name" required>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label" for="corpPhone">মোবাইল নম্বর</label>
                            <input class="form-control" id="corpPhone" name="phone" type="tel" inputmode="tel" autocomplete="tel" required>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label" for="corpStaff">আনুমানিক কর্মী সংখ্যা</label>
                            <input class="form-control" id="corpStaff" name="staff" type="number" min="1" inputmode="numeric">
                        </div>
                        <div class="col-12">
                            <button class="btn btn-primary btn-lg w-100" type="submit">কোটেশনের অনুরোধ পাঠান</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
