<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cat2_exo_promotions', function (Blueprint $table) {
            $table->id();

            // Applicant
            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // Required documents
            $table->foreignId('assignment_orders_id')   // Sea Duty Records
                  ->constrained('documents')
                  ->restrictOnDelete();

            $table->foreignId('performance_eval_id')    // Formal Promotion Request Letter
                  ->constrained('documents')
                  ->restrictOnDelete();

            // Optional document
            $table->foreignId('certification_id')       // Supporting Certifications
                  ->nullable()
                  ->constrained('certifications')
                  ->nullOnDelete();

            // Step 1 checklist confirmation (stored as boolean flags)
            $table->boolean('checklist_sea_duty_records')->default(false);
            $table->boolean('checklist_min_residency')->default(false);
            $table->boolean('checklist_steaming_hours')->default(false);
            $table->boolean('checklist_cat1_compliance')->default(false);

            // Status workflow
            $table->string('status')->default('pending');
            // pending → for_review → scheduled → approved / rejected / completed

            // Admin-managed fields
            $table->text('remarks')->nullable();
            $table->dateTime('scheduled_at')->nullable();

            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('status');
            $table->index('scheduled_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cat2_exo_promotions');
    }
};