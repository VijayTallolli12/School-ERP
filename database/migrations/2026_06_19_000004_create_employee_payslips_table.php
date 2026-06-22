<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_payslips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payroll_run_id')->constrained('payroll_runs')->cascadeOnDelete();
            $table->foreignId('payroll_item_id')->constrained('payroll_items')->cascadeOnDelete();
            $table->string('payslip_number', 50)->unique();
            $table->string('employee_type', 20);
            $table->string('employee_id', 50);
            $table->string('employee_name');
            $table->string('department_name')->nullable();
            $table->string('designation_name')->nullable();
            $table->json('earnings_json');
            $table->json('deductions_json');
            $table->decimal('gross_salary', 12, 2);
            $table->decimal('total_deductions', 12, 2);
            $table->decimal('net_salary', 12, 2);
            $table->foreignId('generated_by')->constrained('users');
            $table->timestamp('generated_at');
            $table->unique(['payroll_run_id', 'payroll_item_id'], 'payslip_run_item_unique');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_payslips');
    }
};
