<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_payment_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('fee_payment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_fee_item_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->timestamps();

            $table->index(['student_fee_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_payment_items');
    }
};
