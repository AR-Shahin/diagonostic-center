<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_parameters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_test_id')->constrained()->cascadeOnDelete();
            $table->string('group_name')->nullable()->comment('e.g. Differential Count');
            $table->string('code', 30)->comment('Short key used in formulas, e.g. TC, HDL');
            $table->string('name');
            $table->foreignId('unit_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('input_type', ['number', 'text', 'option'])->default('number');
            $table->text('options')->nullable()->comment('Comma separated choices for option inputs');
            $table->unsignedTinyInteger('decimal_places')->default(2);
            $table->string('formula')->nullable()->comment('e.g. TC - HDL - TG / 5 (calculated, not typed)');
            $table->string('default_value')->nullable();
            $table->boolean('status')->default(true);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();

            $table->unique(['lab_test_id', 'code']);
        });

        Schema::create('reference_ranges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_parameter_id')->constrained()->cascadeOnDelete();
            $table->enum('gender', ['any', 'male', 'female'])->default('any');
            $table->unsignedInteger('age_min_days')->nullable();
            $table->unsignedInteger('age_max_days')->nullable();
            $table->decimal('low', 12, 4)->nullable();
            $table->decimal('high', 12, 4)->nullable();
            $table->string('normal_text')->nullable()->comment('Printed range text, e.g. "< 200" or "Negative"');
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reference_ranges');
        Schema::dropIfExists('test_parameters');
    }
};
