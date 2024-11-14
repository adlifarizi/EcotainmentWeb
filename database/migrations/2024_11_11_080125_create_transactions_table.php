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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('user_id'); // sesuaikan tipe data dengan 'id' di tabel 'users'
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->integer('total_amount'); // total pembayaran
            $table->enum('status', ['pending', 'completed', 'canceled'])->default('pending'); // status transaksi
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
