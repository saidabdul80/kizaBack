<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('saved_recipients', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
            $table->string('bank_name')->nullable()->change();
            $table->string('account_name')->nullable()->change();
            $table->string('account_number')->nullable()->change();
            $table->string('first_name')->nullable()->change();
            $table->string('last_name')->nullable()->change();
            $table->string('phone_number')->nullable()->change();
            $table->string('method');
        });
    }

    public function down(): void
    {
        Schema::table('saved_recipients', function (Blueprint $table) {
            $table->string('email')->nullable(false)->change();
            $table->string('bank_name')->nullable(false)->change();
            $table->string('account_name')->nullable(false)->change();
            $table->string('account_number')->nullable(false)->change();
            $table->string('first_name')->nullable(false)->change();
            $table->string('last_name')->nullable(false)->change();
            $table->string('phone_number')->nullable(false)->change();
            $table->dropColumn('method');
        });
    }
};
