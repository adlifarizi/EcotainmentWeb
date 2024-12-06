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
        Schema::table('transactions', function (Blueprint $table) {
            // Mengubah enum status dengan menambahkan 'on_shipment'
            $table->enum('status', ['pending', 'waiting_for_confirmation', 'processed', 'on_shipment', 'completed', 'canceled'])->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Mengembalikan enum status ke nilai semula
            $table->enum('status', ['pending', 'waiting_for_confirmation', 'processed', 'completed', 'canceled'])->default('pending')->change();
        });
    }
};
