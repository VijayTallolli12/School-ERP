<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_terms', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->date('starts_on');
            $table->date('ends_on');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->string('status', 30)->default('active')->index();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'academic_year_id', 'name']);
            $table->index(['school_id', 'academic_year_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_terms');
    }
};
