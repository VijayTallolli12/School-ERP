<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_pending_actions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('tool');
            $table->json('parameters')->nullable();
            $table->text('question')->nullable();
            $table->string('status', 30)->default('pending_confirmation'); // pending_confirmation | executing | completed | cancelled | expired
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'school_id', 'status']);
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_pending_actions');
    }
};
