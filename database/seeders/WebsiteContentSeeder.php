<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Phase 7 (website content) permissions + the starter FAQs, so the website never shows an empty FAQ.
 * Safe to re-run: php artisan db:seed --class=WebsiteContentSeeder
 */
class WebsiteContentSeeder extends Seeder
{
    public function run(): void
    {
        $all = ['webcontent-edit', 'webcontent-delete', 'webrequest-view', 'webrequest-handle'];
        foreach ($all as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'admin']);
        }
        foreach (['Super Admin' => $all, 'Manager' => $all, 'Receptionist' => ['webrequest-view', 'webrequest-handle']] as $roleName => $permissions) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'admin'])->givePermissionTo($permissions);
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        if (Faq::exists()) {
            return;
        }
        $faqs = [
            ['কীভাবে ডায়াগনস্টিক টেস্ট বুক করবেন?', '“অ্যাপয়েন্টমেন্ট নিন” পেজে টেস্ট বা চিকিৎসক বেছে তারিখ ও সময় নির্ধারণ করুন। চাইলে হটলাইনে কল করেও বুক করতে পারেন।'],
            ['কীভাবে রিপোর্ট ডাউনলোড করবেন?', '“রিপোর্ট ডাউনলোড” পেজে মোবাইল নম্বর ও ওটিপি, অথবা ইনভয়েস নম্বর ও রিপোর্ট পিন দিয়ে রিপোর্ট খুলে PDF ডাউনলোড করতে পারবেন।'],
            ['রিপোর্ট পেতে কত সময় লাগে?', 'টেস্টভেদে সময় আলাদা। প্রতিটি টেস্টের কার্ডে রিপোর্ট ডেলিভারির আনুমানিক সময় উল্লেখ থাকে এবং ইনভয়েসেও দেওয়া হয়।'],
            ['বাসা থেকে নমুনা সংগ্রহের সুবিধা আছে কি?', 'নির্ধারিত সেবা এলাকায় বাসা থেকে নমুনা সংগ্রহের সুবিধা রয়েছে। এলাকা ও চার্জ “বাসায় নমুনা সংগ্রহ” সেকশনে দেখুন।'],
            ['কোন কোন পেমেন্ট পদ্ধতি গ্রহণযোগ্য?', 'সেন্টারে চালু থাকা পদ্ধতি অনুযায়ী পেমেন্ট করা যায়। বুকিংয়ের সময় চালু অপশনগুলো দেখানো হবে।'],
            ['রক্ত পরীক্ষার আগে কি উপবাস প্রয়োজন?', 'সব টেস্টে নয়। প্রতিটি টেস্টের প্রস্তুতি নির্দেশনা টেস্ট কার্ডে দেওয়া থাকে। নিশ্চিত হতে চিকিৎসক বা আমাদের হটলাইনে জানুন।'],
            ['আপনাদের খোলার সময় কখন?', 'শাখাভেদে সময় আলাদা হতে পারে। প্রতিটি শাখার সময় “আমাদের শাখা” সেকশনে দেখুন।'],
        ];
        foreach ($faqs as $i => [$q, $a]) {
            Faq::create(['question_bn' => $q, 'answer_bn' => $a, 'question_en' => __($q, [], 'en'), 'answer_en' => __($a, [], 'en'), 'sort' => $i]);
        }
    }
}
