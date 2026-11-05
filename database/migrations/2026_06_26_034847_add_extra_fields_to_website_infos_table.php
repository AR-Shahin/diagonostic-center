<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('website_infos', function (Blueprint $table) {
            $table->string('favicon')->nullable()->after('logo');
            $table->text('address')->nullable()->after('phone');
            $table->string('facebook_url')->nullable()->after('address');
            $table->string('twitter_url')->nullable()->after('facebook_url');
            $table->string('instagram_url')->nullable()->after('twitter_url');
            $table->string('linkedin_url')->nullable()->after('instagram_url');
            $table->string('footer_text')->nullable()->after('linkedin_url');
            $table->boolean('maintenance_mode')->default(false)->after('footer_text');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('website_infos', function (Blueprint $table) {
            $table->dropColumn([
                'favicon',
                'address',
                'facebook_url',
                'twitter_url',
                'instagram_url',
                'linkedin_url',
                'footer_text',
                'maintenance_mode'
            ]);
        });
    }
};
