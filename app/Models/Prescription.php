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
        'medicines',
        'notes',
        'status',
        'dispensed_by',
        'dispensed_at',
    ];

    protected function casts(): array
    {
        return [
            'dispensed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Prescription $prescription) {
            $prescription->lookup_code ??= static::generateUniqueLookupCode();
        });
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
