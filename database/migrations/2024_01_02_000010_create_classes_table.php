<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name', 80);
            $table->string('code', 30);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->string('status', 30)->default('active')->index();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'code']);
            $table->index(['school_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};
