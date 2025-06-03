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
            $table->tinyInteger('reliable_status')->nullable()->after('last_reported_at');
            $table->timestamp('reliable_status_updated_at')->nullable()->after('reliable_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parking_zones', function (Blueprint $table) {
            $table->dropColumn(['reliable_status', 'reliable_status_updated_at']);
        });
    }
};
