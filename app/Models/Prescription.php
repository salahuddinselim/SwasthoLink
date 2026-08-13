<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Prescription extends Model
{
    use HasFactory;

    protected $fillable = [
        'lookup_code',
        'doctor_id',
        'hospital_id',
        'patient_id',
        'patient_name',
        'patient_email',
        'patient_phone',
        'medicines',
        'notes',
        'signature',
        'status',
        'expires_at',
        'dispensed_by',
        'dispensed_at',
    ];

    protected function casts(): array
    {
        return [
            'dispensed_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    /** Lookup codes are valid for this many days after issue. */
    public const LOOKUP_CODE_VALIDITY_DAYS = 30;

    protected static function booted(): void
    {
        static::creating(function (Prescription $prescription) {
            $prescription->lookup_code ??= static::generateUniqueLookupCode();
            $prescription->expires_at ??= now()->addDays(self::LOOKUP_CODE_VALIDITY_DAYS);
        });
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public static function generateUniqueLookupCode(): string
    {
        do {
            $code = 'RX-'.strtoupper(Str::random(6));
        } while (static::where('lookup_code', $code)->exists());

        return $code;
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function dispensedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispensed_by');
    }
}
