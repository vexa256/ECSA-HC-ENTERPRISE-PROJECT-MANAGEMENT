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
        Schema::create('h_r_appraisal_reviewers', function (Blueprint $table) {
            $table->id();

            // References the appraisal record (subject being reviewed)
            $table->unsignedBigInteger('appraisal_id');

            // References the user who is a reviewer for that appraisal
            $table->unsignedBigInteger('reviewer_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('h_r_appraisal_reviewers');
    }
};