<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trip_students', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('trip_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('route_stop_id')->constrained('route_stops')->cascadeOnDelete();
            $table->enum('pickup_status', ['pending', 'picked_up', 'missed'])->default('pending');
            $table->enum('drop_status', ['pending', 'dropped_off', 'missed'])->default('pending');
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('dropped_off_at')->nullable();
            $table->decimal('pickup_latitude', 10, 7)->nullable();
            $table->decimal('pickup_longitude', 10, 7)->nullable();
            $table->decimal('drop_latitude', 10, 7)->nullable();
            $table->decimal('drop_longitude', 10, 7)->nullable();
            $table->timestamps();

            $table->unique(['trip_id', 'student_id']);
            $table->index(['trip_id', 'route_stop_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trip_students');
    }
};
