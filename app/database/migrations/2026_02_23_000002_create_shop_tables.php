<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Domínios dos revendedores (comprados via shop + particulares)
        Schema::create('reseller_domains', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reseller_id');
            $table->string('domain');
            $table->enum('type', ['purchased', 'custom'])->default('purchased');
            $table->boolean('is_active')->default(false);
            $table->boolean('dns_configured')->default(false);
            $table->string('namecheap_order_id')->nullable();
            $table->decimal('paid_amount_brl', 10, 2)->nullable();
            $table->integer('years')->default(1);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index('reseller_id');
            $table->unique(['reseller_id', 'domain']);
        });

        // Pedidos de domínio pendentes de pagamento
        Schema::create('domain_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_ref')->unique();
            $table->unsignedBigInteger('reseller_id');
            $table->string('domain');
            $table->integer('years')->default(1);
            $table->decimal('price_usd', 10, 2);
            $table->decimal('price_brl', 10, 2);
            $table->decimal('exchange_rate', 10, 4);
            $table->enum('status', ['pending', 'paid', 'registered', 'failed', 'expired'])->default('pending');
            $table->string('pix_qr_code_id')->nullable();
            $table->text('pix_payload')->nullable();
            $table->text('pix_encoded_image')->nullable();
            $table->string('payment_id')->nullable();
            $table->string('namecheap_order_id')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('registered_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index('reseller_id');
            $table->index('status');
            $table->index('order_ref');
        });

        // Gateway de pagamento do admin para o shop (separado dos gateways dos revendedores)
        Schema::create('shop_payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('provider');
            $table->text('credentials');
            $table->boolean('active')->default(false);
            $table->string('webhook_secret')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_payment_gateways');
        Schema::dropIfExists('domain_orders');
        Schema::dropIfExists('reseller_domains');
    }
};
