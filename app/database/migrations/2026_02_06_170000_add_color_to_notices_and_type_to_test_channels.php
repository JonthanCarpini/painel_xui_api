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
        Schema::table('notices', function (Blueprint $table) {
            $table->string('color', 20)->nullable()->after('type')->comment('Hex color or class');
        });

        Schema::table('test_channels', function (Blueprint $table) {
            $table->string('type', 20)->default('live')->after('group_title')->index()->comment('live, movie, series');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notices', function (Blueprint $table) {
            $table->dropColumn('color');
        });

        Schema::table('test_channels', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
