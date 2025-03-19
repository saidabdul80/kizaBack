<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('saved_recipients', function (Blueprint $table) {
            $table->tinyInteger('method')->change();
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->tinyInteger('method')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('saved_recipients', function (Blueprint $table) {
            //
        });
    }
};
