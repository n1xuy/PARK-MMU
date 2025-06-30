<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportsTable extends Migration
{
    public function up()
    {
    Schema::create('reports', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->foreignId('parking_zone_id')->constrained('parking_zones')->cascadeOnDelete();
        $table->unsignedTinyInteger('status')->comment('1=Empty, 2=Half-Full, 3=Full');
        $table->timestamp('expires_at')->nullable()->comment('Auto-delete old records');
        $table->timestamps();
        
        $table->index(['parking_zone_id', 'created_at']); 
    });
    }

    public function down()
    {
        Schema::dropIfExists('reports');
    }
}