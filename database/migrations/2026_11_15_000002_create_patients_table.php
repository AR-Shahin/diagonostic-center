<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One mobile number can belong to several patients (family members).
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('patient_code', 20)->unique();
            $table->string('name');
            $table->string('mobile', 20)->index();
            $table->enum('gender', ['male', 'female', 'other']);
            $table->date('date_of_birth')->nullable();
            $table->boolean('dob_estimated')->default(true)->comment('True when calculated from an age typed at the counter');
            $table->string('blood_group', 5)->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete()->comment('Registered at');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete()->comment('Patient portal account (phase 4)');
            $table->foreignId('created_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
