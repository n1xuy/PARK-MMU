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
        Schema::create('park_manage', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('zone_id');
            $table->text('reason');
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('schedule_type', ['single', 'weekly', 'daily'])->default('single');
            $table->json('weekly_days')->nullable(); // [1,2,3,4,5] for Mon-Fri
            $table->date('recurring_start_date')->nullable();
            $table->date('recurring_end_date')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->boolean('is_cancelled')->default(false);
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            
            // Foreign key constraint
            $table->foreign('zone_id')->references('id')->on('parking_zones')->onDelete('cascade');
            
            // Indexes for better performance
            $table->index('zone_id');
            $table->index('date');
            $table->index('is_recurring');
            $table->index(['date', 'start_time', 'end_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('park_manage');
    }
};