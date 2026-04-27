<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('junior_higher_billets', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // documents table
            $table->foreignId('sea_duty_record_id')
                  ->constrained('documents')
                  ->cascadeOnDelete();

            // chips_reports table (used for both ships_order & endorsement)
            $table->foreignId('ships_order_id')
                  ->constrained('chips_reports')
                  ->cascadeOnDelete();

            $table->foreignId('endorsement_id')
                  ->constrained('chips_reports')
                  ->cascadeOnDelete();

            // certifications table
            $table->foreignId('duty_performance_cert_id')
                  ->constrained('certifications')
                  ->cascadeOnDelete();

            $table->foreignId('jsolc_cert_id')
                  ->constrained('certifications')
                  ->cascadeOnDelete();

            // optional
            $table->foreignId('watchstander_cert_id')
                  ->nullable()
                  ->constrained('certifications')
                  ->nullOnDelete();

            $table->enum('status', ['pending', 'approved', 'rejected'])
                  ->default('pending');

            $table->text('remarks')->nullable();

            $table->timestamps();
             $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('junior_higher_billets');
    }
};