<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Gap-free running numbers for invoices, receipts and patient codes (row locked while incrementing).
        Schema::create('number_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->comment('e.g. INV-MAIN-2611');
            $table->unsignedBigInteger('last_number')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('number_sequences');
    }
};
