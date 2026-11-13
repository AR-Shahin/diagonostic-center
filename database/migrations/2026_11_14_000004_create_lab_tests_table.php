<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->restrictOnDelete();
            $table->foreignId('sample_type_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code', 30)->unique();
            $table->string('name_en');
            $table->string('name_bn')->nullable();
            $table->string('slug')->unique();
            $table->decimal('price', 12, 2);
            $table->decimal('offer_price', 12, 2)->nullable()->comment('Optional campaign price; billing uses it when set');
            $table->decimal('cost', 12, 2)->default(0)->comment('Reagent/consumable cost for profit reports');
            $table->enum('report_type', ['parameter', 'template', 'culture'])->default('parameter');
            $table->unsignedSmallInteger('delivery_hours')->nullable();
            $table->text('preparation_en')->nullable();
            $table->text('preparation_bn')->nullable();
            $table->text('description_en')->nullable();
            $table->text('description_bn')->nullable();
            $table->boolean('is_popular')->default(false);
            $table->boolean('show_on_website')->default(true);
            $table->boolean('status')->default(true);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'show_on_website']);
        });

        // Branch availability. No rows for a test = available at every branch.
        Schema::create('branch_lab_test', function (Blueprint $table) {
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lab_test_id')->constrained()->cascadeOnDelete();
            $table->primary(['branch_id', 'lab_test_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_lab_test');
        Schema::dropIfExists('lab_tests');
    }
};
