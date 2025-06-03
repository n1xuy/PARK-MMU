<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('parking_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('status')->default('empty'); 
            $table->timestamp('last_reported_at')->nullable();
            $table->integer('total_reports')->default(0);
            $table->integer('zone_number')->unique(); 
            $table->text('location')->nullable();
            $table->timestamps();  
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parking_zones');
    }
};