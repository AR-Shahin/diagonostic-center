<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Rates. referrer_id NULL = default for every referrer. department/test NULL = all.
        // The most specific matching row wins (referrer+test > referrer+dept > referrer > default+test > default+dept > default).
        Schema::create('referrer_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('lab_test_id')->nullable()->constrained()->cascadeOnDelete();
            $table->enum('type', ['percent', 'fixed'])->default('percent');
            $table->decimal('value', 10, 2);
            $table->timestamps();

            $table->unique(['referrer_id', 'department_id', 'lab_test_id'], 'referrer_rates_scope_unique');
        });

        Schema::create('commission_payouts', function (Blueprint $table) {
            $table->id();
            $table->string('payout_no', 40)->unique();
            $table->foreignId('referrer_id')->constrained()->restrictOnDelete();
            $table->date('period_to')->comment('Entries up to and including this date are paid');
            $table->decimal('amount', 12, 2);
            $table->enum('method', ['cash', 'bkash', 'nagad', 'rocket', 'card', 'bank'])->default('cash');
            $table->string('reference')->nullable();
            $table->string('note')->nullable();
            $table->foreignId('paid_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();
        });

        // Ledger: +credit when earned, −reversal when the bill is cancelled. Unpaid = rows without payout_id.
        Schema::create('commission_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_id')->constrained()->restrictOnDelete();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_item_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['credit', 'reversal']);
            $table->decimal('base_amount', 12, 2)->default(0);
            $table->enum('rate_type', ['percent', 'fixed'])->nullable();
            $table->decimal('rate_value', 10, 2)->nullable();
            $table->decimal('discount_deducted', 12, 2)->default(0);
            $table->decimal('amount', 12, 2)->comment('Signed: credit > 0, reversal < 0');
            $table->foreignId('payout_id')->nullable()->constrained('commission_payouts')->nullOnDelete();
            $table->string('note')->nullable();
            $table->timestamps();

            $table->index(['referrer_id', 'payout_id']);
            $table->index(['invoice_id', 'type']);
        });

        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->restrictOnDelete();
            $table->foreignId('expense_category_id')->constrained()->restrictOnDelete();
            $table->date('expense_date');
            $table->decimal('amount', 12, 2);
            $table->enum('method', ['cash', 'bkash', 'nagad', 'rocket', 'card', 'bank'])->default('cash');
            $table->string('reference')->nullable();
            $table->string('paid_to')->nullable();
            $table->text('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['branch_id', 'expense_date']);
        });

        // End of day: what the cashier's drawer should hold vs what was counted.
        Schema::create('cash_closings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->restrictOnDelete();
            $table->foreignId('admin_id')->constrained('admins')->restrictOnDelete();
            $table->date('closing_date');
            $table->decimal('opening_cash', 12, 2)->default(0);
            $table->decimal('cash_received', 12, 2)->default(0);
            $table->decimal('cash_refunded', 12, 2)->default(0);
            $table->decimal('cash_expenses', 12, 2)->default(0);
            $table->decimal('commission_paid_cash', 12, 2)->default(0);
            $table->decimal('expected_cash', 12, 2)->default(0);
            $table->decimal('counted_cash', 12, 2)->default(0);
            $table->decimal('difference', 12, 2)->default(0);
            $table->json('non_cash')->nullable()->comment('Received by bKash/Nagad/card… (not in the drawer)');
            $table->text('note')->nullable();
            $table->enum('status', ['submitted', 'verified'])->default('submitted');
            $table->foreignId('verified_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->dateTime('verified_at')->nullable();
            $table->timestamps();

            $table->unique(['admin_id', 'closing_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_closings');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('expense_categories');
        Schema::dropIfExists('commission_entries');
        Schema::dropIfExists('commission_payouts');
        Schema::dropIfExists('referrer_rates');
    }
};
