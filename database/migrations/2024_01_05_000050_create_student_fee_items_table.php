<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_fee_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_fee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fee_category_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->date('due_date')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['student_fee_id', 'fee_category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_fee_items');
    }
};
