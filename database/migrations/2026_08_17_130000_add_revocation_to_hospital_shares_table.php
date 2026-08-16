<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hospital_shares', function (Blueprint $table) {
            $table->foreignId('revoked_by')->nullable()->after('accepted_at')->constrained('users')->nullOnDelete();
            $table->timestamp('revoked_at')->nullable()->after('revoked_by');
        });

        Schema::table('hospital_shares', function (Blueprint $table) {
            $table->enum('status', ['pending', 'completed', 'rejected', 'revoked'])->default('pending')->change();
        });
    }

    public function down(): void
    {
        Schema::table('hospital_shares', function (Blueprint $table) {
            $table->dropConstrainedForeignId('revoked_by');
            $table->dropColumn('revoked_at');
        });

        Schema::table('hospital_shares', function (Blueprint $table) {
            $table->enum('status', ['pending', 'completed', 'rejected'])->default('pending')->change();
        });
    }
};
