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
        Schema::create('cluster_performance_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('ClusterID', 255);
            $table->string('ReportingID', 255);
            $table->string('SO_ID', 255);
            $table->string('UserID', 255);
            $table->string('IndicatorID', 255);
            $table->string('Response', 255);
            $table->text('ReportingComment'); // Removed invalid length parameter
            $table->enum('ResponseType', ['Text', 'Number', 'Boolean', 'Yes/No']);
            $table->timestamps();

            // Removed columns:
            // - Baseline_2023_2024
            // - Target_Year1
            // - Target_Year2
            // - Target_Year3
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cluster_performance_mappings');
    }
};