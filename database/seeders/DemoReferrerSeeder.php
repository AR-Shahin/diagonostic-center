<?php

namespace Database\Seeders;

use App\Models\Referrer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/** A few sample referrers for local development. */
class DemoReferrerSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['doctor', 'Dr. Sample Physician', 'MBBS, FCPS (Medicine)', 'Sample Medical College Hospital'],
            ['doctor', 'Dr. Sample Gynaecologist', 'MBBS, FCPS (Gynae & Obs)', 'Private chamber'],
            ['rmp', 'Sample RMP', null, 'Village pharmacy'],
            ['agent', 'Sample Field Agent', null, null],
        ];
        foreach ($rows as $i => [$type, $name, $degree, $org]) {
            Referrer::create([
                'code' => 'R-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                'type' => $type, 'name' => $name, 'degree' => $degree, 'organisation' => $org, 'mobile' => '01700-00000'.$i,
            ]);
        }
        DB::table('number_sequences')->updateOrInsert(['key' => 'REFERRER'], ['last_number' => count($rows)]);
    }
}
