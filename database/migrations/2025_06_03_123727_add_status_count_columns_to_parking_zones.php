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
            $table->integer('total_empty')->default(0)->after('total_reports');
            $table->integer('total_half_full')->default(0)->after('total_empty');
            $table->integer('total_full')->default(0)->after('total_half_full');
        });    
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parking_zones', function (Blueprint $table) {
            $table->dropColumn(['total_empty', 'total_half_full', 'total_full']);
        });
    }
};
