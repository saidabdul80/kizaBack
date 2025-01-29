<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('saved_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('phone');
            $table->text('bank_account_details');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_recipients');
    }
};
