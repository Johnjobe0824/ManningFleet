<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /* ============================================================
       UP
    ============================================================ */
    public function up(): void
    {
        Schema::create('cat2_co_promotion_reassigns', function (Blueprint $table) {
            $table->id();

            // Owner
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // Required documents
            $table->foreignId('icas_award_order_id')
                  ->constrained('documents')
                  ->cascadeOnDelete();

            $table->foreignId('sea_duty_record_id')
                  ->constrained('documents')
                  ->cascadeOnDelete();

            $table->foreignId('performance_eval_id')
                  ->constrained('documents')
                  ->cascadeOnDelete();

            $table->foreignId('medical_certificate_id')
                  ->constrained('documents')
                  ->cascadeOnDelete();

            // Optional document
            $table->foreignId('supporting_doc_id')
                  ->nullable()
                  ->constrained('documents')
                  ->nullOnDelete();

            // Status & admin fields
            $table->string('status')->default('pending');
            $table->text('remarks')->nullable();
            $table->timestamp('scheduled_at')->nullable();

            // Step 1 checklist flags
            $table->boolean('checklist_icas_award_orders')->default(false);
            $table->boolean('checklist_sea_duty_compliance')->default(false);
            $table->boolean('checklist_performance_eval')->default(false);
            $table->boolean('checklist_medical_fitness')->default(false);

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /* ============================================================
       DOWN
    ============================================================ */
    public function down(): void
    {
        Schema::dropIfExists('cat2_co_promotion_reassigns');
    }
};