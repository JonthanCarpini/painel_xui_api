<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('whatsapp_setting_id');
            $table->unsignedBigInteger('xui_client_id');
            $table->string('notification_type', 20);
            $table->date('sent_date');
            $table->boolean('success')->default(false);
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['whatsapp_setting_id', 'xui_client_id', 'notification_type', 'sent_date'], 'notif_unique');
            $table->foreign('whatsapp_setting_id')->references('id')->on('whatsapp_settings')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
