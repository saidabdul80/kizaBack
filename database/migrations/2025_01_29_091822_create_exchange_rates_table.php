<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('currency_id_from')->constrained('currencies')->cascadeOnDelete();
            $table->foreignId('currency_id_to')->constrained('currencies')->cascadeOnDelete();
            $table->decimal('rate', 10, 6);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['currency_id_from', 'currency_id_to']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};
