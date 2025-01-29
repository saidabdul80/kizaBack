<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone_number')->unique();
            $table->string('email')->unique();
            $table->text('bank_account_details')->nullable();
            $table->string('picture_url')->nullable();
            $table->string('nin_slip_url')->nullable();
            $table->string('international_passport_url')->nullable();
            $table->string('utility_bills_url')->nullable();
            $table->string('drivers_license_url')->nullable();
            $table->string('permanent_residence_card_url')->nullable();
            $table->string('proof_of_address_url')->nullable();
            $table->string('email_otp', 10)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('phone_number_otp', 10)->nullable();
            $table->timestamp('phone_number_verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
