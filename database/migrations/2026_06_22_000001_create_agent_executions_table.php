<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_executions', function (Blueprint $table): void {
            $table->id();
            $table->string('agent_name', 100);
            $table->foreignId('executed_by')->constrained('users')->cascadeOnDelete();
            $table->string('status', 30)->default('pending')->index();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('records_processed')->default(0);
            $table->text('result_summary')->nullable();
            $table->text('error_message')->nullable();
            $table->json('input_params')->nullable();
            $table->json('output_data')->nullable();
            $table->timestamps();

            $table->index(['agent_name', 'status']);
            $table->index(['executed_by', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_executions');
    }
};
