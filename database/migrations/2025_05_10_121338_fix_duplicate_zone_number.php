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
 
    // Remove duplicate columns if they exist
    Schema::table('parking_zones', function (Blueprint $table) {
        if (Schema::hasColumn('parking_zones', 'zone_number')) {
            $table->dropColumn('zone_number');
        }
    });
    
    // Add the column properly
    Schema::table('parking_zones', function (Blueprint $table) {
        $table->integer('zone_number')->unique()->after('id');
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
