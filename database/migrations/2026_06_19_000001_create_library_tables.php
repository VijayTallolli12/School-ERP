<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('library_categories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->string('status', 30)->default('active')->index();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['school_id', 'status']);
        });

        Schema::create('library_authors', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->text('biography')->nullable();
            $table->string('status', 30)->default('active')->index();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['school_id', 'status']);
        });

        Schema::create('library_publishers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->text('address')->nullable();
            $table->string('contact', 60)->nullable();
            $table->string('status', 30)->default('active')->index();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['school_id', 'status']);
        });

        Schema::create('library_books', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('isbn', 40)->nullable();
            $table->string('title', 255);
            $table->foreignId('category_id')->nullable()->constrained('library_categories')->nullOnDelete();
            $table->foreignId('author_id')->nullable()->constrained('library_authors')->nullOnDelete();
            $table->foreignId('publisher_id')->nullable()->constrained('library_publishers')->nullOnDelete();
            $table->string('edition', 60)->nullable();
            $table->string('language', 60)->default('English');
            $table->string('rack_number', 60)->nullable();
            $table->integer('quantity')->default(1);
            $table->integer('available_copies')->default(1);
            $table->text('description')->nullable();
            $table->string('status', 30)->default('active')->index();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['school_id', 'status']);
            $table->index('title');
            $table->index('isbn');
        });

        Schema::create('library_issues', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained('library_books')->cascadeOnDelete();
            $table->morphs('issueable');
            $table->date('issue_date');
            $table->date('due_date');
            $table->date('return_date')->nullable();
            $table->decimal('fine_amount', 10, 2)->default(0.00);
            $table->boolean('fine_paid')->default(false);
            $table->text('notes')->nullable();
            $table->string('status', 30)->default('issued')->index();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['school_id', 'status']);
            $table->index('due_date');
        });

        Schema::create('library_fine_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->decimal('fine_per_day', 10, 2)->default(1.00);
            $table->decimal('max_fine', 10, 2)->nullable();
            $table->integer('grace_period_days')->default(0);
            $table->string('status', 30)->default('active')->index();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['school_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_fine_settings');
        Schema::dropIfExists('library_issues');
        Schema::dropIfExists('library_books');
        Schema::dropIfExists('library_publishers');
        Schema::dropIfExists('library_authors');
        Schema::dropIfExists('library_categories');
    }
};
