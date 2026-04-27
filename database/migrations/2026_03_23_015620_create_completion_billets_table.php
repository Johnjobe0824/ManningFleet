<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
       Schema::create('completion_billets', function (Blueprint $table) {
    $table->id();

    $table->foreignId('user_id')
          ->constrained('users')
          ->cascadeOnDelete();

    // Sea Duty Record → documents table
    $table->foreignId('sea_duty_record_id')
          ->constrained('documents')
          ->restrictOnDelete();

    // Ships Orders → chips_reports table
    $table->foreignId('ships_order_id')
          ->constrained('chips_reports')
          ->restrictOnDelete();

    // ENGRO / DGO Cert → certifications table  ← was "certificates"
    $table->foreignId('engro_dgo_cert_id')
          ->constrained('certifications')
          ->restrictOnDelete();

    // Watch Stander Cert → certifications table  ← was "certificates"
    $table->foreignId('watchstander_cert_id')
          ->constrained('certifications')
          ->restrictOnDelete();

    $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
    $table->text('remarks')->nullable();

    $table->timestamps();
    $table->softDeletes();

    $table->unique(['user_id', 'deleted_at'], 'unique_active_completion_billet');
});
    }

    public function down(): void
    {
        Schema::dropIfExists('completion_billets');
    }
};