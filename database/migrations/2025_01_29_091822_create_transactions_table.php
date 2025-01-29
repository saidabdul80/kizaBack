<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->foreignId('currency_id')->constrained('currencies')->cascadeOnDelete();
            $table->enum('type', ['deposit', 'fulfill']);
            $table->decimal('rate', 10, 4);
            $table->decimal('fees', 15, 2);
            $table->decimal('total_amount', 15, 2);
            $table->enum('status', ['initiated', 'processing', 'completed', 'failed'])->default('initiated');
            $table->string('receipt_path')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reference')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
