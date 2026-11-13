<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('website_infos', function (Blueprint $table) {
            $table->string('theme_preset', 30)->default('ocean')->after('favicon');
            $table->string('opening_hours_en')->nullable()->after('address');
            $table->string('opening_hours_bn')->nullable()->after('opening_hours_en');
        });
    }

    public function down(): void
    {
        Schema::table('website_infos', function (Blueprint $table) {
            $table->dropColumn(['theme_preset', 'opening_hours_en', 'opening_hours_bn']);
        });
    }
};
