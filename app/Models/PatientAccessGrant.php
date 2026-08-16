<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * A patient-issued, time-limited code that unlocks their prescription
 * history from hospitals *other than* the one a doctor already belongs to.
 * Same-hospital history is visible to a treating doctor automatically
 * (see PatientRecordController); this grant is what lets a patient bring
 * their older, other-hospital records to a new doctor without that doctor
 * needing any pre-existing relationship to them.
 */
class PatientAccessGrant extends Model
{
    protected $fillable = [
        'patient_id',
        'code',
        'expires_at',
        'revoked_at',
        'last_used_at',
        'use_count',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'revoked_at' => 'datetime',
            'last_used_at' => 'datetime',
        ];
    }

    /** Codes are valid for this many hours after issue, unless revoked first. */
    public const VALIDITY_HOURS = 24;

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function isActive(): bool
    {
        return $this->revoked_at === null && $this->expires_at->isFuture();
    }

    public static function generateUniqueCode(): string
    {
        do {
            $code = 'PA-'.strtoupper(Str::random(6));
        } while (static::where('code', $code)->exists());

        return $code;
    }
}
