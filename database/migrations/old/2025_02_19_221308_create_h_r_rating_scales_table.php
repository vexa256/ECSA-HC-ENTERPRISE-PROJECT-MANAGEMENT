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
        Schema::create('h_r_rating_scales', function (Blueprint $table) {
            $table->id();
            $table->string('scale_name'); // e.g. "360 Rating Scale", "Annual Performance Scale"
            $table->string('scale_code'); // e.g. "Y", "M", "A", "B"
            $table->string('scale_value')->nullable();
            // Optional numeric or string representation (e.g. "1", "2", or "Exceeds", etc.)

            $table->text('scale_description')->nullable();
            // e.g. "Yes" / "Most of the Time" or "Outstanding" / "Above Average"
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('h_r_rating_scales');
    }
};