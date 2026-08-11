<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'hospital', 'doctor', 'patient', 'pharmacist'])
                ->default('patient')
                ->after('email');

            $table->enum('status', ['pending', 'active', 'rejected'])
                ->default('active')
                ->after('role');

            $table->string('phone')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'status', 'phone']);
        });
    }
};
