<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tickets')) {
            Schema::create('tickets', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('member_id');
                $table->string('title');
                $table->tinyInteger('status')->default(1);
                $table->boolean('admin_read')->default(false);
                $table->boolean('user_read')->default(false);
                $table->timestamps();
                $table->index('member_id');
                $table->index('status');
            });
        }

        if (!Schema::hasTable('tickets_replies')) {
            Schema::create('tickets_replies', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ticket_id')->constrained('tickets')->onDelete('cascade');
                $table->boolean('admin_reply')->default(false);
                $table->text('message');
                $table->unsignedInteger('date')->nullable();
                $table->timestamps();
                $table->index('ticket_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets_replies');
        Schema::dropIfExists('tickets');
    }
};
