<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('panel_user_id')->unique();
            $table->string('instance_name')->unique();
            $table->boolean('notifications_enabled')->default(false);
            $table->text('expiry_message_3d')->nullable();
            $table->text('expiry_message_1d')->nullable();
            $table->text('expiry_message_today')->nullable();
            $table->string('connection_status')->default('disconnected');
            $table->timestamp('connected_at')->nullable();
            $table->timestamps();

            $table->foreign('panel_user_id')->references('id')->on('panel_users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_settings');
    }
};
