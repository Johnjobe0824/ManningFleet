<?php

use App\Models\Vessel;
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
        Schema::create('vessels', function (Blueprint $table) {
            $table->id();
            $table->string('vessel_name');
            $table->string('vessel_bow_no')->unique();
            $table->string('category')->default(Vessel::SMALL_CRAFT);
            $table->enum('status', [Vessel::RFS, Vessel::NRFS])->default(Vessel::NRFS);
            $table->string('size');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vessels');
    }
};
