<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 7: everything on the public website that the centre edits from the admin panel,
 * plus the requests that come in from the website forms.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->string('name_bn')->nullable();
            $table->string('name_en');
            $table->string('slug')->unique();
            $table->string('qualification')->nullable()->comment('MBBS, FCPS (Medicine)');
            $table->string('designation_bn')->nullable();
            $table->string('designation_en')->nullable();
            $table->string('speciality_bn')->nullable();
            $table->string('speciality_en')->nullable();
            $table->text('bio_bn')->nullable();
            $table->text('bio_en')->nullable();
            $table->string('photo')->nullable();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete()->comment('Chamber');
            $table->json('days')->nullable()->comment('Chamber days: 0 = Sunday … 6 = Saturday');
            $table->time('time_from')->nullable();
            $table->time('time_to')->nullable();
            $table->string('schedule_note_bn')->nullable();
            $table->string('schedule_note_en')->nullable();
            $table->decimal('fee', 10, 2)->nullable();
            $table->boolean('accepts_appointments')->default(true);
            $table->boolean('show_on_website')->default(true);
            $table->boolean('status')->default(true);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('web_requests', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['appointment', 'home_collection', 'contact', 'corporate']);
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained()->nullOnDelete();
            $table->string('item')->nullable()->comment('Test or package the person asked for');
            $table->string('name');
            $table->string('phone', 20);
            $table->string('email')->nullable();
            $table->unsignedSmallInteger('age')->nullable();
            $table->string('organisation')->nullable();
            $table->unsignedInteger('staff_count')->nullable();
            $table->string('address')->nullable();
            $table->date('preferred_date')->nullable();
            $table->string('preferred_time', 40)->nullable();
            $table->string('subject')->nullable();
            $table->text('message')->nullable();
            $table->enum('status', ['new', 'confirmed', 'done', 'cancelled'])->default('new');
            $table->string('staff_note')->nullable();
            $table->foreignId('handled_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->dateTime('handled_at')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamps();
            $table->index(['type', 'status']);
        });

        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->string('question_bn')->nullable();
            $table->string('question_en')->nullable();
            $table->text('answer_bn')->nullable();
            $table->text('answer_en')->nullable();
            $table->boolean('status')->default(true);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('title_bn')->nullable();
            $table->string('title_en')->nullable();
            $table->string('slug')->unique();
            $table->string('category_bn')->nullable();
            $table->string('category_en')->nullable();
            $table->string('excerpt_bn', 300)->nullable();
            $table->string('excerpt_en', 300)->nullable();
            $table->longText('body_bn')->nullable();
            $table->longText('body_en')->nullable();
            $table->string('image')->nullable();
            $table->dateTime('published_at')->nullable()->comment('Empty = draft; future date = scheduled');
            $table->foreignId('created_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('meta_bn')->nullable()->comment('e.g. Health check-up, Dhanmondi');
            $table->string('meta_en')->nullable();
            $table->text('text_bn')->nullable();
            $table->text('text_en')->nullable();
            $table->unsignedTinyInteger('rating')->default(5);
            $table->boolean('consent')->default(false)->comment('Patient agreed in writing/SMS to publish');
            $table->string('consent_note')->nullable();
            $table->boolean('status')->default(true);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonials');
        Schema::dropIfExists('articles');
        Schema::dropIfExists('faqs');
        Schema::dropIfExists('web_requests');
        Schema::dropIfExists('doctors');
    }
};
