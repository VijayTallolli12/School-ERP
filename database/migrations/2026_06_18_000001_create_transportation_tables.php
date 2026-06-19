<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drivers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('mobile', 20);
            $table->string('license_number', 60);
            $table->date('license_expiry_date');
            $table->text('address')->nullable();
            $table->string('status', 30)->default('active')->index();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['school_id', 'status']);
        });

        Schema::create('vehicles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('vehicle_number', 40);
            $table->string('vehicle_name', 120);
            $table->string('vehicle_type', 40)->default('bus');
            $table->unsignedSmallInteger('capacity')->default(0);
            $table->foreignId('driver_id')->nullable()->constrained('drivers')->nullOnDelete();
            $table->string('attendant', 120)->nullable();
            $table->string('status', 30)->default('active')->index();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['school_id', 'vehicle_number']);
            $table->index(['school_id', 'status']);
        });

        Schema::create('routes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('route_name', 120);
            $table->string('start_point', 255);
            $table->string('end_point', 255);
            $table->decimal('distance', 8, 2)->nullable();
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained('drivers')->nullOnDelete();
            $table->string('status', 30)->default('active')->index();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['school_id', 'status']);
        });

        Schema::create('route_stops', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('route_id')->constrained()->cascadeOnDelete();
            $table->string('stop_name', 255);
            $table->time('pickup_time')->nullable();
            $table->time('drop_time')->nullable();
            $table->unsignedSmallInteger('sequence')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['route_id', 'sequence']);
        });

        Schema::create('transport_assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('route_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('route_stop_id')->nullable()->constrained('route_stops')->nullOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
            $table->string('pickup_point', 255)->nullable();
            $table->decimal('monthly_fee', 10, 2)->default(0);
            $table->string('status', 30)->default('active')->index();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['school_id', 'student_id']);
            $table->index(['school_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_assignments');
        Schema::dropIfExists('route_stops');
        Schema::dropIfExists('routes');
        Schema::dropIfExists('vehicles');
        Schema::dropIfExists('drivers');
    }
};
