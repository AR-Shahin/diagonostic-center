<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Department;
use App\Models\LabTest;
use App\Models\Package;
use App\Models\SampleType;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Sample catalog for local development only (called from DatabaseSeeder when APP_ENV=local).
 * Prices are typical Bangladeshi rates and reference ranges are common adult values —
 * the centre's pathologist must review both before going live.
 */
class DemoCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $this->branches();
        $dept = $this->departments();
        $sample = $this->sampleTypes();
        $unit = $this->units();
        $tests = $this->tests($dept, $sample, $unit);
        $this->packages($tests);
    }

    private function branches(): void
    {
        Branch::create([
            'code' => 'MAIN', 'name_en' => 'Main Branch', 'name_bn' => 'প্রধান শাখা', 'is_main' => true,
            'phone' => '01XXX-XXXXXX', 'address_en' => '[Address], [Area], [City]', 'address_bn' => '[ঠিকানা], [এলাকা], [শহর]',
            'opening_hours_en' => 'Every day 8:00 AM – 10:00 PM', 'opening_hours_bn' => 'প্রতিদিন সকাল ৮টা – রাত ১০টা',
            'facilities_en' => "Pathology\nImaging\nSample collection", 'facilities_bn' => "প্যাথলজি\nইমেজিং\nনমুনা সংগ্রহ", 'sort' => 1,
        ]);
        Branch::create([
            'code' => 'BR2', 'name_en' => 'Branch 2', 'name_bn' => 'শাখা ২',
            'phone' => '01XXX-XXXXXX', 'address_en' => '[Address], [Area], [City]', 'address_bn' => '[ঠিকানা], [এলাকা], [শহর]',
            'opening_hours_en' => 'Sat–Thu 8:00 AM – 9:00 PM', 'opening_hours_bn' => 'শনি–বৃহস্পতি সকাল ৮টা – রাত ৯টা',
            'facilities_en' => "Pathology\nSample collection", 'facilities_bn' => "প্যাথলজি\nনমুনা সংগ্রহ", 'sort' => 2,
        ]);
    }

    /** @return array<string, int> slug => id */
    private function departments(): array
    {
        $rows = [
            ['pathology', 'Pathology', 'প্যাথলজি', 'bi-eyedropper', 'Laboratory testing and analysis of blood, urine and other samples.', 'রক্ত, প্রস্রাব ও অন্যান্য নমুনার ল্যাব পরীক্ষা ও বিশ্লেষণ।'],
            ['biochemistry', 'Biochemistry', 'বায়োকেমিস্ট্রি', 'bi-droplet-half', 'Measuring chemical components of the blood to understand liver, kidney and metabolic health.', 'রক্তের রাসায়নিক উপাদান মেপে লিভার, কিডনি ও মেটাবলিজমের অবস্থা বোঝা।'],
            ['hematology', 'Haematology', 'হেমাটোলজি', 'bi-droplet-fill', 'Blood cell counts and clotting-related tests.', 'রক্তকণিকার গণনা ও রক্ত জমাট বাঁধা সংক্রান্ত পরীক্ষা।'],
            ['microbiology', 'Microbiology', 'মাইক্রোবায়োলজি', 'bi-virus', 'Tests to identify germs and determine the effective antibiotic.', 'জীবাণু শনাক্তকরণ ও কার্যকর অ্যান্টিবায়োটিক নির্ধারণের পরীক্ষা।'],
            ['radiology', 'Radiology & Imaging', 'রেডিওলজি ও ইমেজিং', 'bi-radioactive', 'Imaging tests including X-ray, CT scan and MRI.', 'এক্স-রে, সিটি স্ক্যান ও এমআরআইসহ ইমেজিং পরীক্ষা।'],
            ['cardiology', 'Cardiology', 'কার্ডিওলজি', 'bi-heart-pulse', 'Tests to assess how well the heart is working.', 'হৃদযন্ত্রের কার্যক্ষমতা মূল্যায়নের পরীক্ষা।'],
            ['ultrasonography', 'Ultrasonography', 'আল্ট্রাসোনোগ্রাফি', 'bi-soundwave', 'Examination using sound waves to image organs.', 'শব্দতরঙ্গের মাধ্যমে অঙ্গ-প্রত্যঙ্গের ছবি তুলে পরীক্ষা।'],
        ];
        $ids = [];
        foreach ($rows as $i => [$slug, $en, $bn, $icon, $dEn, $dBn]) {
            $ids[$slug] = Department::create([
                'slug' => $slug, 'name_en' => $en, 'name_bn' => $bn, 'icon' => $icon,
                'description_en' => $dEn, 'description_bn' => $dBn, 'sort' => $i + 1,
            ])->id;
        }

        return $ids;
    }

    private function sampleTypes(): array
    {
        $rows = [
            'edta' => ['Blood (EDTA)', 'রক্ত (EDTA)', 'EDTA tube – purple cap'],
            'serum' => ['Blood (Serum)', 'রক্ত (সিরাম)', 'Clot activator tube – red/yellow cap'],
            'fluoride' => ['Blood (Fluoride)', 'রক্ত (ফ্লোরাইড)', 'Fluoride tube – grey cap'],
            'citrate' => ['Blood (Citrate)', 'রক্ত (সাইট্রেট)', 'Citrate tube – light blue cap'],
            'urine' => ['Urine', 'প্রস্রাব', 'Sterile urine container'],
            'stool' => ['Stool', 'মল', 'Stool container'],
            'none' => ['No sample (imaging)', 'নমুনা লাগে না (ইমেজিং)', null],
        ];
        $ids = [];
        foreach ($rows as $key => [$en, $bn, $container]) {
            $ids[$key] = SampleType::create(['name_en' => $en, 'name_bn' => $bn, 'container' => $container])->id;
        }

        return $ids;
    }

    private function units(): array
    {
        $ids = [];
        foreach (['g/dL', '%', 'x10^9/L', 'x10^12/L', 'fL', 'pg', 'mm/1st hr', 'mmol/L', 'mg/dL', 'U/L', 'µIU/mL', '/HPF'] as $name) {
            $ids[$name] = Unit::create(['name' => $name])->id;
        }

        return $ids;
    }

    /** @return array<string, LabTest> code => test */
    private function tests(array $dept, array $sample, array $unit): array
    {
        $make = function (array $a) use ($dept, $sample) {
            return LabTest::create([
                'department_id' => $dept[$a['dept']],
                'sample_type_id' => $sample[$a['sample']] ?? null,
                'code' => $a['code'], 'name_en' => $a['en'], 'name_bn' => $a['bn'],
                'slug' => Str::slug($a['en']), 'price' => $a['price'], 'offer_price' => $a['offer'] ?? null,
                'cost' => $a['cost'] ?? 0, 'report_type' => $a['type'] ?? 'parameter',
                'delivery_hours' => $a['hours'], 'preparation_en' => $a['prep_en'], 'preparation_bn' => $a['prep_bn'],
                'is_popular' => $a['popular'] ?? false, 'sort' => $a['sort'] ?? 0,
            ]);
        };

        // [code, name, unit, ranges[[gender, low, high, text]], group, type, formula, options]
        $addParams = function (LabTest $test, array $params) use ($unit) {
            foreach ($params as $i => $p) {
                $param = $test->parameters()->create([
                    'code' => $p[0], 'name' => $p[1], 'unit_id' => $p[2] ? $unit[$p[2]] : null,
                    'group_name' => $p[4] ?? null, 'input_type' => $p[5] ?? 'number',
                    'formula' => $p[6] ?? null, 'options' => $p[7] ?? null, 'sort' => $i + 1,
                    'decimal_places' => ($p[5] ?? 'number') === 'number' ? 2 : 0,
                ]);
                foreach ($p[3] as $j => [$gender, $low, $high, $text]) {
                    $param->referenceRanges()->create([
                        'gender' => $gender, 'low' => $low, 'high' => $high, 'normal_text' => $text, 'sort' => $j,
                    ]);
                }
            }
        };

        $noFast = ['No fasting required', 'উপবাস প্রয়োজন নেই'];
        $fast = ['8–10 hours fasting required', '৮–১০ ঘণ্টা উপবাস প্রয়োজন'];

        $t = [];
        $t['CBC'] = $make(['dept' => 'hematology', 'sample' => 'edta', 'code' => 'CBC', 'en' => 'CBC (Complete Blood Count)', 'bn' => 'কমপ্লিট ব্লাড কাউন্ট', 'price' => 400, 'cost' => 90, 'hours' => 6, 'prep_en' => $noFast[0], 'prep_bn' => $noFast[1], 'popular' => true, 'sort' => 1]);
        $addParams($t['CBC'], [
            ['HB', 'Haemoglobin', 'g/dL', [['male', 13, 17, null], ['female', 12, 15, null]]],
            ['ESR', 'ESR (Westergren)', 'mm/1st hr', [['male', 0, 10, null], ['female', 0, 20, null]]],
            ['WBC', 'Total WBC count', 'x10^9/L', [['any', 4, 11, null]]],
            ['RBC', 'Total RBC count', 'x10^12/L', [['male', 4.5, 5.5, null], ['female', 3.8, 4.8, null]]],
            ['PLT', 'Platelet count', 'x10^9/L', [['any', 150, 400, null]]],
            ['HCT', 'HCT / PCV', '%', [['male', 40, 50, null], ['female', 36, 46, null]]],
            ['MCV', 'MCV', 'fL', [['any', 83, 101, null]]],
            ['MCH', 'MCH', 'pg', [['any', 27, 32, null]]],
            ['MCHC', 'MCHC', 'g/dL', [['any', 31.5, 34.5, null]]],
            ['NEU', 'Neutrophils', '%', [['any', 40, 80, null]], 'Differential count'],
            ['LYM', 'Lymphocytes', '%', [['any', 20, 40, null]], 'Differential count'],
            ['MON', 'Monocytes', '%', [['any', 2, 10, null]], 'Differential count'],
            ['EOS', 'Eosinophils', '%', [['any', 1, 6, null]], 'Differential count'],
            ['BAS', 'Basophils', '%', [['any', 0, 2, null]], 'Differential count'],
        ]);

        $t['FBS'] = $make(['dept' => 'biochemistry', 'sample' => 'fluoride', 'code' => 'FBS', 'en' => 'Blood Glucose (Fasting)', 'bn' => 'রক্তে শর্করা (খালি পেটে)', 'price' => 150, 'cost' => 25, 'hours' => 4, 'prep_en' => $fast[0], 'prep_bn' => $fast[1], 'sort' => 2]);
        $addParams($t['FBS'], [['GLU', 'Glucose (fasting)', 'mmol/L', [['any', 3.9, 5.5, null]]]]);

        $t['HBA1C'] = $make(['dept' => 'biochemistry', 'sample' => 'edta', 'code' => 'HBA1C', 'en' => 'HbA1c', 'bn' => 'গড় রক্তশর্করা (৩ মাস)', 'price' => 1000, 'offer' => 850, 'cost' => 300, 'hours' => 24, 'prep_en' => $noFast[0], 'prep_bn' => $noFast[1], 'popular' => true, 'sort' => 3]);
        $addParams($t['HBA1C'], [['A1C', 'HbA1c', '%', [['any', null, 5.7, 'Normal < 5.7 · Prediabetes 5.7–6.4 · Diabetes ≥ 6.5']]]]);

        $t['LIPID'] = $make(['dept' => 'biochemistry', 'sample' => 'serum', 'code' => 'LIPID', 'en' => 'Lipid Profile', 'bn' => 'লিপিড প্রোফাইল', 'price' => 1000, 'offer' => 900, 'cost' => 250, 'hours' => 24, 'prep_en' => '10–12 hours fasting required', 'prep_bn' => '১০–১২ ঘণ্টা উপবাস প্রয়োজন', 'popular' => true, 'sort' => 4]);
        $addParams($t['LIPID'], [
            ['TC', 'Total Cholesterol', 'mg/dL', [['any', null, 200, '< 200']]],
            ['TG', 'Triglycerides', 'mg/dL', [['any', null, 150, '< 150']]],
            ['HDL', 'HDL Cholesterol', 'mg/dL', [['male', 40, null, '> 40'], ['female', 50, null, '> 50']]],
            ['LDL', 'LDL Cholesterol (calculated)', 'mg/dL', [['any', null, 100, '< 100']], null, 'number', 'TC - HDL - TG / 5'],
        ]);

        $t['SGPT'] = $make(['dept' => 'biochemistry', 'sample' => 'serum', 'code' => 'SGPT', 'en' => 'SGPT (ALT)', 'bn' => 'এসজিপিটি (লিভার)', 'price' => 400, 'cost' => 70, 'hours' => 12, 'prep_en' => $noFast[0], 'prep_bn' => $noFast[1], 'sort' => 5]);
        $addParams($t['SGPT'], [['ALT', 'SGPT (ALT)', 'U/L', [['male', null, 45, null], ['female', null, 34, null]]]]);

        $t['CREAT'] = $make(['dept' => 'biochemistry', 'sample' => 'serum', 'code' => 'CREAT', 'en' => 'Serum Creatinine', 'bn' => 'সিরাম ক্রিয়েটিনিন', 'price' => 400, 'cost' => 60, 'hours' => 12, 'prep_en' => $noFast[0], 'prep_bn' => $noFast[1], 'sort' => 6]);
        $addParams($t['CREAT'], [['CRE', 'Creatinine', 'mg/dL', [['male', 0.7, 1.3, null], ['female', 0.6, 1.1, null]]]]);

        $t['TSH'] = $make(['dept' => 'biochemistry', 'sample' => 'serum', 'code' => 'TSH', 'en' => 'TSH', 'bn' => 'থাইরয়েড (TSH)', 'price' => 800, 'cost' => 200, 'hours' => 24, 'prep_en' => $noFast[0], 'prep_bn' => $noFast[1], 'popular' => true, 'sort' => 7]);
        $addParams($t['TSH'], [['TSH', 'TSH', 'µIU/mL', [['any', 0.4, 4.0, null]]]]);

        $t['URE'] = $make(['dept' => 'pathology', 'sample' => 'urine', 'code' => 'URE', 'en' => 'Urine R/E', 'bn' => 'প্রস্রাব রুটিন পরীক্ষা', 'price' => 250, 'cost' => 40, 'hours' => 6, 'prep_en' => 'Morning midstream sample', 'prep_bn' => 'সকালের মধ্যপ্রবাহ নমুনা', 'popular' => true, 'sort' => 8]);
        $addParams($t['URE'], [
            ['COL', 'Colour', null, [['any', null, null, 'Straw']], 'Physical', 'option', null, 'Straw, Pale yellow, Yellow, Dark yellow, Red'],
            ['APP', 'Appearance', null, [['any', null, null, 'Clear']], 'Physical', 'option', null, 'Clear, Slightly hazy, Hazy, Turbid'],
            ['ALB', 'Albumin', null, [['any', null, null, 'Nil']], 'Chemical', 'option', null, 'Nil, Trace, +, ++, +++'],
            ['SUG', 'Sugar', null, [['any', null, null, 'Nil']], 'Chemical', 'option', null, 'Nil, Trace, +, ++, +++'],
            ['PUS', 'Pus cells', '/HPF', [['any', null, null, '0 - 5']], 'Microscopic', 'text'],
            ['RBCU', 'RBC', '/HPF', [['any', null, null, 'Nil']], 'Microscopic', 'text'],
        ]);

        $t['DENGUE'] = $make(['dept' => 'microbiology', 'sample' => 'serum', 'code' => 'NS1', 'en' => 'Dengue NS1 Antigen', 'bn' => 'ডেঙ্গু এনএস১', 'price' => 300, 'cost' => 120, 'hours' => 4, 'prep_en' => $noFast[0], 'prep_bn' => $noFast[1], 'sort' => 9]);
        $addParams($t['DENGUE'], [['NS1', 'Dengue NS1 Antigen', null, [['any', null, null, 'Negative']], null, 'option', null, 'Negative, Positive']]);

        $t['UCS'] = $make(['dept' => 'microbiology', 'sample' => 'urine', 'code' => 'UCS', 'en' => 'Urine Culture & Sensitivity', 'bn' => 'প্রস্রাব কালচার', 'price' => 800, 'cost' => 200, 'hours' => 72, 'type' => 'culture', 'prep_en' => 'Sterile container, before antibiotics', 'prep_bn' => 'জীবাণুমুক্ত পাত্র, অ্যান্টিবায়োটিক শুরুর আগে', 'sort' => 10]);

        $imaging = [
            ['XRAY', 'radiology', 'X-Ray Chest P/A', 'বুকের এক্স-রে', 500, 3, 'Remove metal objects', 'ধাতব বস্তু খুলে রাখুন', false],
            ['ECG', 'cardiology', 'ECG', 'ইসিজি', 300, 1, 'No special preparation', 'বিশেষ প্রস্তুতি নেই', false],
            ['ECHO', 'cardiology', 'Echocardiogram (2D)', 'ইকোকার্ডিওগ্রাম', 2000, 24, 'No special preparation', 'বিশেষ প্রস্তুতি নেই', false],
            ['USGWA', 'ultrasonography', 'USG Whole Abdomen', 'আল্ট্রাসোনোগ্রাম (পুরো পেট)', 1500, 2, '6 hours fasting, full bladder', '৬ ঘণ্টা উপবাস, মূত্রথলি পূর্ণ রাখুন', true],
        ];
        foreach ($imaging as $i => [$code, $d, $en, $bn, $price, $hours, $pEn, $pBn, $popular]) {
            $t[$code] = $make(['dept' => $d, 'sample' => 'none', 'code' => $code, 'en' => $en, 'bn' => $bn, 'price' => $price, 'hours' => $hours, 'type' => 'template', 'prep_en' => $pEn, 'prep_bn' => $pBn, 'popular' => $popular, 'sort' => 11 + $i]);
            $t[$code]->reportTemplates()->create([
                'name' => 'Normal', 'is_default' => true,
                'body' => '<p><b>Findings:</b></p><p>[Write findings]</p><p><b>Impression:</b></p><p>[Write impression]</p>',
            ]);
        }

        return $t;
    }

    private function packages(array $t): void
    {
        $rows = [
            ['FULLBODY', 'Full Body Checkup', 'ফুল বডি চেকআপ', 'A comprehensive package for an overall health assessment.', 'সামগ্রিক স্বাস্থ্য যাচাইয়ের একটি পূর্ণাঙ্গ প্যাকেজ।', 'bi-person-check', 4500, true, ['CBC', 'FBS', 'HBA1C', 'LIPID', 'SGPT', 'CREAT', 'TSH', 'URE', 'XRAY', 'ECG', 'USGWA']],
            ['DIABETES', 'Diabetes Screening', 'ডায়াবেটিস স্ক্রিনিং', 'Check diabetes risk and control.', 'ডায়াবেটিসের ঝুঁকি ও নিয়ন্ত্রণ যাচাই।', 'bi-droplet-half', 1400, false, ['FBS', 'HBA1C', 'CREAT', 'URE']],
            ['HEART', 'Heart Health Package', 'হার্ট হেলথ প্যাকেজ', 'Check the health of your heart.', 'হৃদযন্ত্রের সুস্থতা যাচাই।', 'bi-heart-pulse', 3200, false, ['LIPID', 'ECG', 'ECHO', 'FBS']],
        ];
        foreach ($rows as $i => [$code, $en, $bn, $tagEn, $tagBn, $icon, $price, $featured, $codes]) {
            $package = Package::create([
                'code' => $code, 'name_en' => $en, 'name_bn' => $bn, 'slug' => Str::slug($en),
                'tagline_en' => $tagEn, 'tagline_bn' => $tagBn, 'icon' => $icon, 'price' => $price,
                'is_featured' => $featured, 'sort' => $i + 1,
            ]);
            $package->labTests()->attach(collect($codes)->map(fn ($c) => $t[$c]->id));
        }
    }
}
