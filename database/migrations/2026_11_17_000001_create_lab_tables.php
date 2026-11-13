<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // One tube/container per sample type per bill. The barcode on the label is sample_no.
        Schema::create('samples', function (Blueprint $table) {
            $table->id();
            $table->string('sample_no', 20)->unique();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->restrictOnDelete();
            $table->foreignId('sample_type_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['pending', 'collected', 'received', 'rejected'])->default('pending');
            $table->dateTime('collected_at')->nullable();
            $table->foreignId('collected_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->dateTime('received_at')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('rejected_reason')->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'status']);
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->foreignId('sample_id')->nullable()->after('department_id')->constrained()->nullOnDelete();
            $table->dateTime('delivered_at')->nullable()->after('lab_status');
            $table->foreignId('delivered_by')->nullable()->after('delivered_at')->constrained('admins')->nullOnDelete();
        });

        Schema::create('test_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_item_id')->unique()->constrained()->cascadeOnDelete();
            $table->enum('report_type', ['parameter', 'template', 'culture']);
            $table->longText('body')->nullable()->comment('Template reports (X-ray, USG…)');
            $table->json('culture')->nullable()->comment('{growth, organism, colony_count, antibiotics:[{name,result}]}');
            $table->text('comment')->nullable();
            $table->enum('status', ['draft', 'verified'])->default('draft');
            $table->foreignId('entered_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->dateTime('entered_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->dateTime('verified_at')->nullable();
            $table->unsignedSmallInteger('amendments')->default(0);
            $table->string('amend_reason')->nullable();
            $table->timestamps();
        });

        // Values with the name/unit/range copied in, so later catalog edits never change a signed report.
        Schema::create('result_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_result_id')->constrained()->cascadeOnDelete();
            $table->foreignId('test_parameter_id')->nullable()->constrained()->nullOnDelete();
            $table->string('group_name')->nullable();
            $table->string('code', 30);
            $table->string('name');
            $table->string('unit', 50)->nullable();
            $table->string('value')->nullable();
            $table->string('flag', 2)->nullable()->comment('H / L');
            $table->string('reference_text')->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::table('admins', function (Blueprint $table) {
            $table->string('designation')->nullable()->after('name')->comment('Printed under the signature, e.g. Consultant Pathologist');
            $table->string('qualification')->nullable()->after('designation')->comment('e.g. MBBS, M.Phil (Pathology)');
            $table->string('signature')->nullable()->after('qualification');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->string('report_pin', 8)->nullable()->after('note')->comment('Printed on the invoice; with the invoice no it opens the report online');
            $table->string('report_token', 40)->nullable()->unique()->after('report_pin')->comment('QR verification link');
        });

        DB::table('invoices')->whereNull('report_token')->orderBy('id')->each(function ($invoice) {
            DB::table('invoices')->where('id', $invoice->id)->update([
                'report_pin' => (string) random_int(100000, 999999),
                'report_token' => Str::random(32),
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('invoices', fn (Blueprint $t) => $t->dropColumn(['report_pin', 'report_token']));
        Schema::table('admins', fn (Blueprint $t) => $t->dropColumn(['designation', 'qualification', 'signature']));
        Schema::dropIfExists('result_values');
        Schema::dropIfExists('test_results');
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sample_id');
            $table->dropConstrainedForeignId('delivered_by');
            $table->dropColumn('delivered_at');
        });
        Schema::dropIfExists('samples');
    }
};
