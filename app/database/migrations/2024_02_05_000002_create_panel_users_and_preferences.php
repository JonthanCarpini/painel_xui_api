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
        // 1. Tabela de usuários locais (sincronizada com XUI)
        Schema::create('panel_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('xui_id')->unique(); // ID original do XUI
            $table->string('username');
            $table->integer('group_id'); // 1 = Admin, 2 = Revenda, etc
            $table->timestamps();
        });

        // 2. Recriar user_preferences para ligar com panel_users
        // Primeiro dropamos a antiga se existir (foi criada na migração anterior mas sem FK)
        Schema::dropIfExists('user_preferences');

        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('panel_user_id')->constrained('panel_users')->onDelete('cascade');
            $table->string('key');
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, json, boolean, integer
            $table->timestamps();

            // Evitar duplicidade de chave para o mesmo usuário
            $table->unique(['panel_user_id', 'key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_preferences');
        Schema::dropIfExists('panel_users');
    }
};
