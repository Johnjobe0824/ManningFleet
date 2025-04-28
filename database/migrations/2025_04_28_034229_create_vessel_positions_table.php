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
        Schema::create('vessel_positions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vessel_id');
            $table->unsignedBigInteger('position_id');
            $table->unsignedBigInteger('user_id')->nullable(); // Nullable kasi baka vacant pa
            $table->enum('status', ['available', 'occupied'])->default('available');
            $table->timestamps();

            // Foreign keys
            $table->foreign('vessel_id')->references('id')->on('vessels')->onDelete('cascade');
            $table->foreign('position_id')->references('id')->on('positions')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vessel_positions');
    }
};
