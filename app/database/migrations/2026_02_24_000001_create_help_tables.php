<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('help_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('icon')->default('bi-folder');
            $table->string('color')->default('#f97316');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('help_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('help_category_id')->constrained('help_categories')->cascadeOnDelete();
            $table->string('title');
            $table->text('content');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('help_post_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('help_post_id')->constrained('help_posts')->cascadeOnDelete();
            $table->enum('type', ['image', 'video']);
            $table->string('url');
            $table->string('caption')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('help_post_media');
        Schema::dropIfExists('help_posts');
        Schema::dropIfExists('help_categories');
    }
};
