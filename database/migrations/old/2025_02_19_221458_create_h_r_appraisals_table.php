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
        Schema::create('h_r_appraisals', function (Blueprint $table) {
            $table->id();
                                                        // References to other core tables
            $table->unsignedBigInteger('user_id');      // The person being reviewed
            $table->unsignedBigInteger('reviewer_id');  // The person completing the form
            $table->unsignedBigInteger('cycle_id');     // The appraisal cycle
            $table->unsignedBigInteger('form_type_id'); // Which form is being used

            // Dates and Status
            $table->dateTime('creation_date')->nullable();
            $table->dateTime('due_date')->nullable();
            $table->dateTime('completion_date')->nullable();
            $table->enum('status', ['Draft', 'Submitted', 'Reviewed', 'Finalized', 'Canceled'])
                ->default('Draft');

            // Overall rating (could be 'A','B','C','D','E', or 'Y','M','S','N','D', etc.)
            $table->string('overall_rating')->nullable();

            // Free-text comments/summary
            $table->text('comments')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('h_r_appraisals');
    }
};