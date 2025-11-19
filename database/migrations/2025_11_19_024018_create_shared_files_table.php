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
        Schema::create('shared_files', function (Blueprint $table) {
            $table->id();

            // user pengirim
            $table->foreignId('from_user')->constrained('users')->onDelete('cascade');

            // penerima (bisa belum punya akun)
            $table->string('to_email');

            $table->string('name');
            $table->string('type');
            $table->string('size');
            $table->string('status')->default('pending'); // pending — karena penerima belum daftar
            $table->text('desc');
            $table->boolean('favorite')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shared_files');
    }
};
