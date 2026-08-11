<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('hospital_id')->nullable()->constrained()->nullOnDelete();

            $table->string('bmdc_number')->unique();
            $table->string('specialization')->nullable();
            $table->string('bmdc_certificate_path')->nullable();
            $table->string('nid_document_path')->nullable();

            // RSA keypair used to sign prescriptions. Generated only after approval.
            $table->text('rsa_public_key')->nullable();
            $table->text('rsa_private_key_encrypted')->nullable();

            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->string('rejection_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_profiles');
    }
};
