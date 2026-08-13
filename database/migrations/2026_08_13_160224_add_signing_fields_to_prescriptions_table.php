<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            // Base64-encoded RSA signature over a canonical representation of the
            // prescription, produced with the doctor's private key at creation time.
            $table->text('signature')->nullable()->after('notes');

            // Patient's phone number as given at prescription time, used as an
            // out-of-band second factor at pharmacy lookup (last 4 digits only).
            $table->string('patient_phone', 32)->nullable()->after('patient_email');

            // Lookup codes are only valid for a limited window after issue.
            $table->timestamp('expires_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->dropColumn(['signature', 'patient_phone', 'expires_at']);
        });
    }
};
