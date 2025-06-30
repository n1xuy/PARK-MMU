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
            $table->integer('total_empty')->default(0);
            $table->integer('total_half_full')->default(0);
            $table->integer('total_full')->default(0);
            $table->integer('zone_number')->unique(); 
            $table->text('location')->nullable();
            $table->boolean('is_blocked')->default(false);
            $table->text('block_reason')->nullable();
            $table->date('block_date')->nullable();
            $table->time('block_start_time')->nullable();
            $table->time('block_end_time')->nullable();
            $table->timestamps();  
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parking_zones');
    }
};