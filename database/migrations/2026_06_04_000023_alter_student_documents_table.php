<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_documents', function (Blueprint $table) {
            // Rename existing columns
            $table->renameColumn('type', 'document_type');
            $table->renameColumn('size', 'file_size');

            // Add new columns
            $table->string('file_name')->nullable()->after('file_path');
            $table->date('issue_date')->nullable()->after('mime_type');
            $table->date('expiry_date')->nullable()->after('issue_date');
            $table->text('remarks')->nullable()->after('expiry_date');
            $table->foreignId('updated_by')->nullable()->after('uploaded_by')->constrained('users')->nullOnDelete();
            $table->boolean('is_verified')->default(false)->after('updated_by');
            $table->foreignId('verified_by')->nullable()->after('is_verified')->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable()->after('verified_by');
        });
    }

    public function down(): void
    {
        Schema::table('student_documents', function (Blueprint $table) {
            $table->renameColumn('document_type', 'type');
            $table->renameColumn('file_size', 'size');

            $table->dropColumn([
                'file_name', 'issue_date', 'expiry_date', 'remarks',
                'updated_by', 'is_verified', 'verified_by', 'verified_at',
            ]);
        });
    }
};
