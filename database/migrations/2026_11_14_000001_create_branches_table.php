<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name_en');
            $table->string('name_bn')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email')->nullable();
            $table->text('address_en')->nullable();
            $table->text('address_bn')->nullable();
            $table->string('opening_hours_en')->nullable();
            $table->string('opening_hours_bn')->nullable();
            $table->text('facilities_en')->nullable()->comment('One per line, shown as chips on the website');
            $table->text('facilities_bn')->nullable();
            $table->string('map_url', 500)->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_main')->default(false);
            $table->boolean('show_on_website')->default(true);
            $table->boolean('status')->default(true);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
