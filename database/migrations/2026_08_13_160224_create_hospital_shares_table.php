<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hospital_shares', function (Blueprint $table) {
            $table->id();

            $table->foreignId('initiator_hospital_id')->constrained('hospitals')->cascadeOnDelete();
            $table->foreignId('recipient_hospital_id')->constrained('hospitals')->cascadeOnDelete();
            $table->foreignId('prescription_id')->constrained()->cascadeOnDelete();

            $table->foreignId('initiated_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('accepted_by')->nullable()->constrained('users')->nullOnDelete();

            // Diffie-Hellman public parameters and public values. Private exponents
            // are never persisted — they exist only for the lifetime of the request
            // that generates them and are discarded immediately after the shared
            // secret is derived.
            $table->text('dh_prime');
            $table->text('dh_generator');
            $table->text('initiator_public_value');
            $table->text('recipient_public_value')->nullable();

            // The initiator's DH private exponent, held encrypted-at-rest only for
            // the window between initiating and the recipient accepting (this is a
            // concession to the two-actor, two-HTTP-request nature of a human
            // approval workflow). It is permanently wiped the moment the shared
            // secret is derived and used to encrypt the payload — see security
            // report for the forward-secrecy discussion.
            $table->text('initiator_private_exponent_encrypted')->nullable();

            // SHA-256 fingerprint of the derived shared secret, stored only so both
            // sides can display/audit that they derived the same key. Not reversible
            // to the secret or the AES key.
            $table->string('shared_secret_fingerprint', 64)->nullable();

            // AES-256-GCM envelope: the prescription payload is encrypted once with a
            // key derived from the DH shared secret, then that raw AES key is
            // immediately discarded. Each hospital gets its own RSA-wrapped copy of
            // the AES key so it can unwrap and decrypt with its own private key.
            $table->text('ciphertext')->nullable();
            $table->string('iv', 32)->nullable();
            $table->string('auth_tag', 32)->nullable();
            $table->text('key_wrapped_for_initiator')->nullable();
            $table->text('key_wrapped_for_recipient')->nullable();

            $table->enum('status', ['pending', 'completed', 'rejected'])->default('pending');
            $table->timestamp('accepted_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hospital_shares');
    }
};
