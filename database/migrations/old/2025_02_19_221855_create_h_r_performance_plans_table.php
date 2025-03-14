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
        Schema::create('h_r_performance_plans', function (Blueprint $table) {
            $table->id();
            // Link to the appraisal record this performance plan belongs to
            $table->unsignedBigInteger('appraisal_id');

            // Accountability area (e.g., "Deployment of ECSA-HC Portal/Intranet")
            $table->string('accountability_area');

            // Detailed objective text (e.g., "Fully deploy Intranet by Q2 2025")
            $table->text('objective');

            // Measures of success (e.g., "Intranet accessible organization-wide, documented usage stats")
            $table->text('measure_of_success')->nullable();

            // Status of the performance plan
            $table->enum('status', ['Open', 'Completed'])->default('Open');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('h_r_performance_plans');
    }
};