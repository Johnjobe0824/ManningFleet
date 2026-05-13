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
        Schema::create('exo_icas_completions', function (Blueprint $table) {

            $table->id();

            /* ── Owner ─────────────────────────────────────────── */
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            /* ── Required documents ────────────────────────────── */
            $table->foreignId('sea_duty_record_id')
                  ->constrained('documents')
                  ->restrictOnDelete();

            $table->foreignId('icas_exam_proof_id')
                  ->constrained('documents')
                  ->restrictOnDelete();

            /* ── Optional document ─────────────────────────────── */
            $table->foreignId('supporting_doc_id')
                  ->nullable()
                  ->constrained('documents')
                  ->nullOnDelete();

            /* ── Workflow ──────────────────────────────────────── */
            $table->enum('status', [
                'pending',
                'for_review',
                'scheduled',
                'approved',
                'rejected',
                'icas_awarded',
                'slot_available',
                'waiting_list',
                'completed',
            ])->default('pending');

            $table->text('remarks')->nullable();
            $table->timestamp('scheduled_at')->nullable();

            /* ── Step 1 checklist flags ────────────────────────── */
            $table->boolean('checklist_sea_duty_records')->default(false);
            $table->boolean('checklist_min_residency')->default(false);
            $table->boolean('checklist_steaming_hours')->default(false);
            $table->boolean('checklist_icas_exam')->default(false);

            /* ── Timestamps & soft deletes ─────────────────────── */
            $table->timestamps();
            $table->softDeletes();

            /* ── Indexes ───────────────────────────────────────── */
            $table->index('user_id');
            $table->index('status');
            $table->index('scheduled_at');
        });
    }

    /* ============================================================
       DOWN
    ============================================================ */
    public function down(): void
    {
        Schema::dropIfExists('exo_icas_completions');
    }
};