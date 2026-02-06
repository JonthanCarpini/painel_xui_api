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
        Schema::create('ticket_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('responsible');
            $table->string('phone');
            $table->timestamps();
        });

        Schema::create('ticket_extras', function (Blueprint $table) {
            $table->id();
            $table->integer('ticket_id')->unique(); // ID do ticket no banco XUI
            $table->foreignId('category_id')->constrained('ticket_categories');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_extras');
        Schema::dropIfExists('ticket_categories');
    }
};
