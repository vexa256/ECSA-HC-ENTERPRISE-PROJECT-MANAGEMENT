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
        Schema::create('h_r_appraisal_cycles', function (Blueprint $table) {
            $table->id();
            $table->string('cycle_name'); // e.g. "FY 2024", "Mid-Year 2024"
            $table->date('start_date');   // start of the appraisal cycle
            $table->date('end_date');     // end of the appraisal cycle
            $table->enum('status', ['Open', 'Closed', 'Draft', 'Archived'])
                ->default('Draft');                      // cycle state
            $table->text('description')->nullable(); // additional notes about this cycle
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('h_r_appraisal_cycles');
    }
};