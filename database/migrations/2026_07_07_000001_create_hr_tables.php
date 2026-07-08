<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('employee_code')->unique();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->string('marital_status')->nullable();
            $table->string('blood_group')->nullable();
            $table->string('nationality')->nullable();
            $table->string('religion')->nullable();
            $table->text('address_line1')->nullable();
            $table->text('address_line2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('country')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account_no')->nullable();
            $table->string('bank_ifsc_code')->nullable();
            $table->string('pan_number')->nullable();
            $table->string('uan_number')->nullable();
            $table->string('pf_number')->nullable();
            $table->string('esi_number')->nullable();
            $table->date('date_of_joining')->nullable();
            $table->date('date_of_leaving')->nullable();
            $table->string('employment_type')->default('permanent');
            $table->string('employment_status')->default('active');
            $table->foreignId('department_id')->nullable()->constrained('payroll_departments')->nullOnDelete();
            $table->foreignId('designation_id')->nullable()->constrained('payroll_designations')->nullOnDelete();
            $table->foreignId('reporting_to_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('profile_image')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('employee_contracts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('contract_type')->default('permanent');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->integer('probation_period_months')->nullable();
            $table->integer('notice_period_days')->nullable();
            $table->text('documents_json')->nullable();
            $table->string('status')->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('employee_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('document_type');
            $table->string('document_name');
            $table->string('document_number')->nullable();
            $table->string('file_path');
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('pending');
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('payroll_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('payroll_currency', 3)->default('INR');
            $table->integer('salary_day')->default(1);
            $table->boolean('enable_professional_tax')->default(true);
            $table->boolean('enable_provident_fund')->default(true);
            $table->boolean('enable_esi')->default(false);
            $table->decimal('pf_employee_share', 5, 2)->default(12.00);
            $table->decimal('pf_employer_share', 5, 2)->default(12.00);
            $table->decimal('esi_employee_share', 5, 2)->default(0.75);
            $table->decimal('esi_employer_share', 5, 2)->default(3.25);
            $table->decimal('professional_tax_monthly', 10, 2)->default(200.00);
            $table->decimal('overtime_rate_multiplier', 5, 2)->default(1.50);
            $table->string('pay_period')->default('monthly');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_settings');
        Schema::dropIfExists('employee_documents');
        Schema::dropIfExists('employee_contracts');
        Schema::dropIfExists('employees');
    }
};
