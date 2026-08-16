<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HospitalShare extends Model
{
    protected $fillable = [
        'initiator_hospital_id',
        'recipient_hospital_id',
        'prescription_id',
        'initiated_by',
        'accepted_by',
        'dh_prime',
        'dh_generator',
        'initiator_public_value',
        'recipient_public_value',
        'initiator_private_exponent_encrypted',
        'shared_secret_fingerprint',
        'ciphertext',
        'iv',
        'auth_tag',
        'key_wrapped_for_initiator',
        'key_wrapped_for_recipient',
        'status',
        'accepted_at',
        'revoked_by',
        'revoked_at',
    ];

    protected function casts(): array
    {
        return [
            'accepted_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function initiatorHospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class, 'initiator_hospital_id');
    }

    public function recipientHospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class, 'recipient_hospital_id');
    }

    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class);
    }

    public function initiatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    public function acceptedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accepted_by');
    }

    public function revokedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revoked_by');
    }
}
