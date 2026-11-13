<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Who sent the patient ("Ref. by" on the invoice): doctors, RMPs, agents, companies.
        Schema::create('referrers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->enum('type', ['doctor', 'rmp', 'agent', 'corporate', 'other'])->default('doctor');
            $table->string('name');
            $table->string('degree')->nullable()->comment('e.g. MBBS, FCPS (Medicine)');
            $table->string('organisation')->nullable()->comment('Hospital / chamber / company');
            $table->string('mobile', 30)->nullable()->index();
            $table->text('address')->nullable();
            $table->boolean('commission_enabled')->default(true);
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referrers');
    }
};
