<?php

use App\Support\Phone;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Patients log in to the website with their phone number (unique, like an online-shop customer).
     * One account per phone; family members who share the phone are separate patient profiles under it.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->unique()->after('name');
            $table->string('email')->nullable()->change();
            $table->boolean('must_change_password')->default(true)->after('password');
            $table->timestamp('last_login_at')->nullable()->after('remember_token');
        });

        // Existing patients: give every phone number an account (password = the phone number).
        DB::table('patients')->whereNull('user_id')->orderBy('id')->get(['id', 'name', 'mobile'])
            ->groupBy(fn ($p) => Phone::normalize($p->mobile))
            ->each(function ($patients, $phone) {
                if (! preg_match(Phone::PATTERN, $phone)) {
                    return;
                }
                $userId = DB::table('users')->where('phone', $phone)->value('id') ?? DB::table('users')->insertGetId([
                    'name' => $patients->first()->name,
                    'phone' => $phone,
                    'password' => Hash::make($phone),
                    'must_change_password' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                DB::table('patients')->whereIn('id', $patients->pluck('id'))->update(['user_id' => $userId, 'mobile' => $phone]);
            });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['phone']);
            $table->dropColumn(['phone', 'must_change_password', 'last_login_at']);
        });
    }
};
