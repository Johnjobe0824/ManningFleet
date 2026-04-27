<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('junior_vessel_assignments', function (Blueprint $table) {
            $table->id();

            // Applicant
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // Step 1 — Primary Documents
            // Carry Morning Report → chips_reports
            $table->foreignId('carry_morning_report_id')
                  ->nullable()
                  ->constrained('chips_reports')
                  ->nullOnDelete();

            // MSO / DCO Certificate → certifications
            $table->foreignId('certificate_mso_dco_id')
                  ->nullable()
                  ->constrained('certifications')
                  ->nullOnDelete();

            // JSOLC Certificate → certifications
            $table->foreignId('certificate_jsolc_id')
                  ->nullable()
                  ->constrained('certifications')
                  ->nullOnDelete();

            // Step 2 — Vessel & Position
            $table->foreignId('vessel_id')
                  ->nullable()
                  ->constrained('vessels')
                  ->nullOnDelete();

            $table->foreignId('position_id')
                  ->nullable()
                  ->constrained('positions')
                  ->nullOnDelete();

            // Status & Remarks
            $table->enum('status', ['pending', 'approved', 'rejected'])
                  ->default('pending');

            $table->text('remarks')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('junior_vessel_assignments');
    }
};