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
        Schema::create('client_applications', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('downloader_id')->nullable();
            $table->string('direct_link')->nullable();
            $table->string('compatible_devices')->nullable();
            $table->string('activation_code')->nullable();
            $table->text('login_instructions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('dns_servers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('url');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dns_servers');
        Schema::dropIfExists('client_applications');
    }
};
