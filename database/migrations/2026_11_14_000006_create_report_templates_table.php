<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Default report text for imaging/USG/ECG style tests (findings + impression).
        Schema::create('report_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_test_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->longText('body');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_templates');
    }
};
