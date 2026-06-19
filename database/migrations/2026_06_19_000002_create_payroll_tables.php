<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_departments', function (Blueprint $table): void {
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

        Schema::create('payroll_designations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('payroll_departments')->nullOnDelete();
            $table->string('name', 120);
            $table->text('description')->nullable();
            $table->string('status', 30)->default('active')->index();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['school_id', 'status']);
        });

        Schema::create('salary_components', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('name_display', 120);
            $table->string('component_type', 30)->default('earning');
            $table->string('calculation_type', 30)->default('fixed');
            $table->decimal('value', 10, 2)->default(0.00);
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->string('status', 30)->default('active')->index();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['school_id', 'status']);
        });

        Schema::create('pay_grades', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->text('description')->nullable();
            $table->decimal('min_salary', 12, 2)->nullable();
            $table->decimal('max_salary', 12, 2)->nullable();
            $table->string('status', 30)->default('active')->index();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['school_id', 'status']);
        });

        Schema::create('employee_salary_structures', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('employee_id', 40);
            $table->string('employee_type', 30);
            $table->foreignId('pay_grade_id')->nullable()->constrained('pay_grades')->nullOnDelete();
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->decimal('total_ctc', 12, 2)->default(0.00);
            $table->string('status', 30)->default('active')->index();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['school_id', 'employee_type', 'employee_id'], 'payroll_ess_sch_emp_type_id_idx');
            $table->index(['school_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_salary_structures');
        Schema::dropIfExists('pay_grades');
        Schema::dropIfExists('salary_components');
        Schema::dropIfExists('payroll_designations');
        Schema::dropIfExists('payroll_departments');
    }
};
