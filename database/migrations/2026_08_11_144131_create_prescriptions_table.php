<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->string('lookup_code', 12)->unique();

            $table->foreignId('doctor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('hospital_id')->nullable()->constrained()->nullOnDelete();

            // Patients are matched by email at creation time; patient_id is filled
            // in when the email matches a registered patient account, but the
            // prescription is still valid (and lookup-able) even if it doesn't.
            $table->foreignId('patient_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('patient_name');
            $table->string('patient_email')->nullable();

            $table->text('medicines');
            $table->text('notes')->nullable();

            $table->enum('status', ['active', 'dispensed'])->default('active');
            $table->foreignId('dispensed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('dispensed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
    }
};
