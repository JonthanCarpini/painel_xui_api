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
        Schema::create('test_channels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('group_title')->nullable()->index(); // Categoria
            $table->string('logo_url')->nullable();
            $table->string('stream_url'); // URL completa do stream
            $table->string('stream_id')->nullable(); // ID do stream se possível extrair
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_channels');
    }
};
