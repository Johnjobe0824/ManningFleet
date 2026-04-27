<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exo_reassignments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // Phase 2 — Documents
            $table->foreignId('assignment_orders_id')
                  ->constrained('documents')
                  ->restrictOnDelete();

            $table->foreignId('performance_eval_id')
                  ->constrained('documents')
                  ->restrictOnDelete();

            $table->foreignId('certification_id')     
                  ->constrained('certifications')
                  ->restrictOnDelete();

            // Phase 3 — Board Deliberation
            $table->enum('status', [
                'pending',
                'for_review',
                'scheduled',
                'approved',
                'rejected',
                'completed',
            ])->default('pending');

            $table->timestamp('scheduled_at')->nullable();
            $table->text('remarks')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('user_id');
            $table->index('status');
            $table->index('scheduled_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exo_reassignments');
    }
};