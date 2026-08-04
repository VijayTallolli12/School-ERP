<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_transfers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('transfer_type', 30)->default('transfer')->index();
            $table->foreignId('from_class_section_id')->nullable()->constrained('class_section')->nullOnDelete();
            $table->foreignId('to_class_section_id')->nullable()->constrained('class_section')->nullOnDelete();
            $table->foreignId('from_academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->foreignId('to_academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->date('transferred_on')->nullable();
            $table->text('reason')->nullable();
            $table->text('remarks')->nullable();
            $table->string('tc_no', 50)->nullable()->index();
            $table->date('tc_issued_on')->nullable();
            $table->string('conduct', 100)->nullable();
            $table->text('destination_school')->nullable();
            $table->string('status', 30)->default('issued')->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'transfer_type']);
            $table->index(['school_id', 'status']);
            $table->index(['school_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_transfers');
    }
};
