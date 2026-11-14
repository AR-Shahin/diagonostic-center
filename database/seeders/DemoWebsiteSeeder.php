<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Branch;
use App\Models\Doctor;
use Illuminate\Database\Seeder;

/** Local demo only: two sample doctors and one sample article so the website sections are visible while developing. */
class DemoWebsiteSeeder extends Seeder
{
    public function run(): void
    {
        $branch = Branch::query()->orderByDesc('is_main')->value('id');
        Doctor::create(['name_en' => 'Dr. Sample Physician', 'name_bn' => 'ডা. নমুনা চিকিৎসক', 'slug' => 'dr-sample-physician', 'qualification' => 'MBBS, FCPS (Medicine)',
            'designation_en' => 'Consultant, Medicine', 'designation_bn' => 'কনসালট্যান্ট, মেডিসিন', 'speciality_en' => 'Medicine', 'speciality_bn' => 'মেডিসিন',
            'branch_id' => $branch, 'days' => [6, 1, 3], 'time_from' => '16:00', 'time_to' => '20:00', 'fee' => 800, 'sort' => 1]);
        Doctor::create(['name_en' => 'Dr. Sample Sonologist', 'name_bn' => 'ডা. নমুনা সনোলজিস্ট', 'slug' => 'dr-sample-sonologist', 'qualification' => 'MBBS, DMU',
            'designation_en' => 'Sonologist', 'designation_bn' => 'সনোলজিস্ট', 'speciality_en' => 'Radiology', 'speciality_bn' => 'রেডিওলজি',
            'branch_id' => $branch, 'days' => [6, 0, 1, 2, 3, 4], 'time_from' => '09:00', 'time_to' => '13:00', 'schedule_note_bn' => 'সিরিয়ালের জন্য কল করুন', 'schedule_note_en' => 'Call for a serial', 'fee' => 600, 'sort' => 2]);
        Article::create(['title_bn' => 'রক্ত পরীক্ষার আগে যা জানা জরুরি', 'title_en' => 'What to know before a blood test', 'slug' => 'before-a-blood-test',
            'category_bn' => 'টেস্ট প্রস্তুতি', 'category_en' => 'Test preparation',
            'excerpt_bn' => 'উপবাস, পানি পান ও ওষুধ — পরীক্ষার আগের সাধারণ নির্দেশনা।', 'excerpt_en' => 'Fasting, water and medicines: the usual advice before a test.',
            'body_bn' => '<p>কিছু রক্ত পরীক্ষার আগে ৮–১২ ঘণ্টা না খেয়ে থাকতে হয়, যেমন ফাস্টিং ব্লাড সুগার ও লিপিড প্রোফাইল। পানি পান করা যায়।</p><h2>যা করবেন</h2><ul><li>প্রেসক্রিপশন সঙ্গে আনুন।</li><li>নিয়মিত ওষুধ খাওয়া যাবে কি না, চিকিৎসকের কাছে জেনে নিন।</li></ul>',
            'body_en' => '<p>Some blood tests need 8–12 hours of fasting, such as fasting blood sugar and the lipid profile. Water is fine.</p><h2>What to do</h2><ul><li>Bring your prescription.</li><li>Ask your doctor whether to take your regular medicines.</li></ul>',
            'published_at' => now()->subDays(2)]);
    }
}
