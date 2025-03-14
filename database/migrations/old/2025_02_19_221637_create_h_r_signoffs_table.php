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
        Schema::create('h_r_signoffs', function (Blueprint $table) {
            $table->id();

            // References the appraisal being signed off
            $table->unsignedBigInteger('appraisal_id');

            // Role of the signatory (e.g., "Reviewer", "Supervisor", "Employee", "HR")
            $table->string('signoff_role');

            // The user who provided the sign-off
            $table->unsignedBigInteger('signed_by_user_id');

            // Date and time when the sign-off occurred
            $table->dateTime('signoff_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('h_r_signoffs');
    }
};