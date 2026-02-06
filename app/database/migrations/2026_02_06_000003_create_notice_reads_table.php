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
        Schema::create('notice_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('panel_users')->onDelete('cascade');
            $table->foreignId('notice_id')->constrained('notices')->onDelete('cascade');
            $table->timestamps();

            // Garante que um usuário só marque como lido uma vez cada aviso
            $table->unique(['user_id', 'notice_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notice_reads');
    }
};
