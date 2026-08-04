<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('admission_no', 50)->nullable()->index();
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
            $table->text('current_address')->nullable();
            $table->text('permanent_address')->nullable();
            $table->foreignId('class_section_id')->nullable()->constrained('class_section')->nullOnDelete();
            $table->foreignId('academic_year_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 30)->default('enquiry')->index();
            $table->string('source', 30)->default('walk_in')->index();
            $table->string('guardian_name', 150)->nullable();
            $table->string('guardian_relation', 50)->nullable();
            $table->string('guardian_phone', 30)->nullable();
            $table->string('guardian_email', 255)->nullable();
            $table->string('guardian_occupation', 120)->nullable();
            $table->text('remarks')->nullable();
            $table->date('applied_on')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('converted_at')->nullable();
            $table->foreignId('converted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('student_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'admission_no']);
            $table->index(['school_id', 'status']);
            $table->index(['school_id', 'applied_on']);
        });

        Schema::create('admission_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('admission_id')->constrained()->cascadeOnDelete();
            $table->string('document_type', 50)->index();
            $table->string('document_name', 150)->nullable();
            $table->string('file_path', 255);
            $table->boolean('verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'admission_id']);
            $table->index(['school_id', 'document_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admission_documents');
        Schema::dropIfExists('admissions');
    }
};
