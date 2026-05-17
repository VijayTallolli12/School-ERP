<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_user', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('designation')->nullable();
            $table->string('employee_code', 50)->nullable();
            $table->date('joined_at')->nullable();
            $table->string('status', 30)->default('active')->index();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->unique(['school_id', 'user_id']);
            $table->index(['school_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_user');
    }
};
