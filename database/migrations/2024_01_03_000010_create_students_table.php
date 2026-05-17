<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('admission_no', 50);
            $table->date('admission_date')->nullable();
            $table->string('first_name', 100);
            $table->string('middle_name', 100)->nullable();
            $table->string('last_name', 100)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender', 20);
            $table->string('blood_group', 10)->nullable();
            $table->string('religion', 80)->nullable();
            $table->string('category', 80)->nullable();
            $table->string('caste', 80)->nullable();
            $table->string('nationality', 80)->default('Indian');
            $table->string('mother_tongue', 80)->nullable();
            $table->string('aadhar_no', 20)->nullable();
            $table->string('photo_path')->nullable();
            $table->text('current_address')->nullable();
            $table->text('permanent_address')->nullable();
            $table->string('status', 30)->default('active')->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'admission_no']);
            $table->index(['school_id', 'status']);
            $table->index(['school_id', 'first_name', 'last_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
