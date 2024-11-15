<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->string('user_id'); // sesuaikan tipe data dengan 'id' di tabel 'users'
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->foreignId('product_id')->constrained()->onDelete('cascade'); // Relasi ke tabel products
            $table->tinyInteger('rating')->unsigned(); // Nilai rating, misalnya antara 1-5
            $table->text('comment')->nullable(); // Komentar ulasan
            $table->timestamps();

            $table->unique(['user_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
