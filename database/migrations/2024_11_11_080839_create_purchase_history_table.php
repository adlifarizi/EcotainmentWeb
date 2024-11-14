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
        Schema::create('purchase_history', function (Blueprint $table) {
            $table->id();
            $table->string('user_id'); // sesuaikan tipe data dengan 'id' di tabel 'users'
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->foreignId('product_id')->constrained()->onDelete('cascade'); // relasi ke tabel products
            $table->integer('quantity'); // jumlah produk
            $table->integer('total_price'); // total harga
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_history');
    }
};
