<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no', 40)->unique();
            $table->foreignId('branch_id')->constrained()->restrictOnDelete();
            $table->foreignId('patient_id')->constrained()->restrictOnDelete();
            $table->foreignId('referrer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('admins')->nullOnDelete();

            // Patient details as they were on the day (age matters for reference ranges)
            $table->string('patient_name');
            $table->string('patient_mobile', 20);
            $table->enum('patient_gender', ['male', 'female', 'other']);
            $table->string('patient_age', 30)->nullable()->comment('e.g. 32 Y, 1 Y 3 M');
            $table->unsignedInteger('patient_age_days')->nullable();

            $table->decimal('subtotal', 12, 2);
            $table->enum('discount_type', ['percent', 'flat'])->nullable();
            $table->decimal('discount_value', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->string('discount_reason')->nullable();
            $table->foreignId('discount_approved_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->decimal('vat_percent', 5, 2)->default(0);
            $table->decimal('vat_amount', 12, 2)->default(0);
            $table->boolean('home_collection')->default(false);
            $table->decimal('home_collection_charge', 12, 2)->default(0);
            $table->decimal('round_off', 8, 2)->default(0);
            $table->decimal('net_amount', 12, 2);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('due_amount', 12, 2)->default(0);
            $table->enum('status', ['unpaid', 'partial', 'paid', 'cancelled'])->default('unpaid');

            $table->dateTime('delivery_at')->nullable()->comment('Promised report time (slowest test)');
            $table->text('note')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('cancel_reason')->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'created_at']);
            $table->index(['status', 'due_amount']);
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('invoice_items')->cascadeOnDelete()
                ->comment('Set for tests that belong to a package line');
            $table->enum('item_type', ['test', 'package', 'package_test']);
            $table->foreignId('lab_test_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('package_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code', 30);
            $table->string('name');
            $table->decimal('price', 12, 2)->comment('Charged price; for package tests = share of the package price');
            $table->decimal('regular_price', 12, 2)->comment('List price on the day (for commission on gross)');
            $table->decimal('discount_amount', 12, 2)->default(0)->comment('This line\'s part of the invoice discount');
            $table->decimal('net_amount', 12, 2);
            $table->dateTime('delivery_at')->nullable();
            $table->enum('lab_status', ['pending', 'collected', 'processing', 'ready', 'delivered', 'cancelled'])->default('pending');
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();

            $table->index(['lab_status']);
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_no', 40)->unique();
            $table->foreignId('invoice_id')->constrained()->restrictOnDelete();
            $table->foreignId('branch_id')->constrained()->restrictOnDelete();
            $table->enum('type', ['payment', 'refund'])->default('payment');
            $table->enum('method', ['cash', 'bkash', 'nagad', 'rocket', 'card', 'bank'])->default('cash');
            $table->decimal('amount', 12, 2)->comment('Always positive; type tells the direction');
            $table->string('transaction_ref')->nullable();
            $table->string('note')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();

            $table->index(['branch_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
    }
};
