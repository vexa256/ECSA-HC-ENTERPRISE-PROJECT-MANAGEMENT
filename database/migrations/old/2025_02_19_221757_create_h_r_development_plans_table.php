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
        Schema::create('h_r_development_plans', function (Blueprint $table) {
            $table->id();
            // Link to the appraisal record this development plan belongs to
            $table->unsignedBigInteger('appraisal_id');

            // Objective or description of the development goal (e.g., "Obtain advanced certification in Azure DevOps Expert")
            $table->text('objective_description');

            // Text describing how success will be measured (e.g., "Obtain at least one advanced cloud certification")
            $table->text('measure_of_success')->nullable();

            // Status of the development plan: default is 'Open'
            $table->enum('status', ['Open', 'In Progress', 'Completed'])->default('Open');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('h_r_development_plans');
    }
};