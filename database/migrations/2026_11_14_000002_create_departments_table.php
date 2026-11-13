<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name_en');
            $table->string('name_bn')->nullable();
            $table->string('slug')->unique();
            $table->string('icon', 60)->default('bi-clipboard2-pulse')->comment('Bootstrap Icons class');
            $table->text('description_en')->nullable();
            $table->text('description_bn')->nullable();
            $table->text('highlights_en')->nullable()->comment('One per line, shown on the website');
            $table->text('highlights_bn')->nullable();
            $table->boolean('show_on_website')->default(true);
            $table->boolean('status')->default(true);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
