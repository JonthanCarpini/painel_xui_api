<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('whatsapp_settings', function (Blueprint $table) {
            $table->string('send_start_time', 5)->default('09:00')->after('expiry_message_today');
            $table->unsignedInteger('send_interval_seconds')->default(30)->after('send_start_time');
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_settings', function (Blueprint $table) {
            $table->dropColumn(['send_start_time', 'send_interval_seconds']);
        });
    }
};
