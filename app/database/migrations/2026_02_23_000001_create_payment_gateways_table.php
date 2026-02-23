<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reseller_id')->index();
            $table->string('provider'); // asaas, mercadopago, fastdepix
            $table->text('credentials')->nullable(); // JSON criptografado
            $table->boolean('active')->default(false);
            $table->string('webhook_secret')->nullable();
            $table->timestamps();

            $table->unique(['reseller_id', 'provider']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_gateways');
    }
};
