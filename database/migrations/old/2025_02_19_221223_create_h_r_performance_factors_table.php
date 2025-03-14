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
        Schema::create('h_r_performance_factors', function (Blueprint $table) {
            $table->id();
            $table->string('factor_category');              // e.g. "Adaptability", "Communication"
            $table->text('factor_description')->nullable(); // optional detailed explanation
            $table->boolean('is_supervisory_factor')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('h_r_performance_factors');
    }
};