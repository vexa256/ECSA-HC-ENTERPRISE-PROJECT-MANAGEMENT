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
        Schema::create('cluster_indicator_targets', function (Blueprint $table) {
            $table->id();
            $table->string('ClusterTargetID', 255)->unique();
            $table->string('Baseline2024', 255);
            $table->string('ClusterID', 255);   // Matches clusters.ClusterID type
            $table->string('IndicatorID', 255); // Matches performance_mappings.IndicatorID
            $table->string('Target_Year');      // Year value (e.g., 2024)
            $table->string('Target_Value');     // Numeric target
            $table->enum('ResponseType', ['Text', 'Number', 'Boolean', 'Yes/No']);
            $table->timestamps();

            // Composite index for frequent queries
            $table->index(['ClusterID', 'IndicatorID']);

            // Prevent duplicate targets for same cluster+indicator+year
            $table->unique(['IndicatorID', 'Target_Year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cluster_indicator_targets');
    }
};