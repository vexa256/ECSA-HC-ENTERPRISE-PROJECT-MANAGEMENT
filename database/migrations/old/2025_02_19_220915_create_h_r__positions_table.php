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
        Schema::create('h_r__positions', function (Blueprint $table) {
            $table->id();
            $table->string('position_name'); // e.g. "Senior Systems Design Officer"
            $table->boolean('is_supervisory')->default(false);
            $table->text('description')->nullable(); // Longer description if needed
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('h_r__positions');
    }
};