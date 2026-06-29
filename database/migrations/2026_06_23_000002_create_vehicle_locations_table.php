<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_locations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->decimal('speed', 8, 2)->nullable();
            $table->decimal('heading', 5, 2)->nullable();
            $table->timestamp('captured_at');
            $table->string('source', 30)->nullable()->comment('driver_app, gps_device, manual');
            $table->timestamps();

            $table->index(['vehicle_id', 'captured_at']);
            $table->index('captured_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_locations');
    }
};
