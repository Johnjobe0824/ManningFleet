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
        Schema::create('exo_deliberations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // Step 1 — Junior Billet
            $table->foreignId('junior_billet_completion_id')
                  ->constrained('certifications')
                  ->restrictOnDelete();

            // Step 2 — SLMC Endorsement & Orders
            $table->foreignId('slmc_endorsement_id')
                  ->constrained('certifications')
                  ->restrictOnDelete();

            $table->foreignId('slmc_orders_id')
                  ->constrained('certifications')
                  ->restrictOnDelete();

            // Step 3 — EXO Application
            $table->foreignId('slmc_graduation_cert_id')
                  ->constrained('certifications')
                  ->restrictOnDelete();

            // ✅ UPDATED STATUS (added 'scheduled')
            $table->enum('status', ['pending', 'approved', 'rejected', 'scheduled'])
                  ->default('pending');

            $table->text('remarks')->nullable();

            // ✅ NEW FIELD
            $table->timestamp('scheduled_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exo_deliberations');
    }
};