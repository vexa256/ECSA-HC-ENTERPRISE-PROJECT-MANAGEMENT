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
        Schema::create('h_r_appraisal_items', function (Blueprint $table) {
            $table->id();

                                                           // References
            $table->unsignedBigInteger('appraisal_id');    // Link to the 'appraisals' table
            $table->unsignedBigInteger('factor_id');       // Link to the 'performance_factors' table
            $table->unsignedBigInteger('rating_scale_id'); // Chosen rating from the 'rating_scales' table

            // Comments for each factor (e.g. reviewer justification)
            $table->text('comments')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('h_r_appraisal_items');
    }
};