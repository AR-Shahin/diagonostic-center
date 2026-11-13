<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name_en');
            $table->string('name_bn')->nullable();
            $table->string('slug')->unique();
            $table->string('tagline_en')->nullable();
            $table->string('tagline_bn')->nullable();
            $table->string('icon', 60)->default('bi-clipboard2-pulse');
            $table->decimal('price', 12, 2)->comment('Fixed package price; regular price = sum of test prices');
            $table->enum('gender', ['any', 'male', 'female'])->default('any');
            $table->unsignedTinyInteger('min_age')->nullable();
            $table->unsignedTinyInteger('max_age')->nullable();
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('show_on_website')->default(true);
            $table->boolean('status')->default(true);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('package_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lab_test_id')->constrained()->restrictOnDelete();
            $table->unique(['package_id', 'lab_test_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_items');
        Schema::dropIfExists('packages');
    }
};
