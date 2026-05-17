<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_guardians', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('relation', 50);
            $table->string('name', 150);
            $table->string('phone', 30);
            $table->string('email')->nullable();
            $table->string('occupation', 120)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->boolean('can_pickup')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'student_id']);
            $table->index(['phone', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_guardians');
    }
};
