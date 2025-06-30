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
        Schema::table('parking_zones', function (Blueprint $table) {
            $table->timestamp('block_expires_at')->nullable()->after('block_end_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parking_zones', function (Blueprint $table) {
            $table->dropColumn('block_expires_at');
        });
    }
};
