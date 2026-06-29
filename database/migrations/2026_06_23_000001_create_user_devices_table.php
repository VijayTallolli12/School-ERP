<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_devices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('device_type', 50)->nullable();
            $table->string('platform', 50)->nullable();
            $table->string('device_token', 500);
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'device_token']);
            $table->index(['user_id', 'last_seen_at']);
            $table->index('device_token');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_devices');
    }
};
