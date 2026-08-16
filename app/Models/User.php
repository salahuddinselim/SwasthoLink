<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'phone',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_secret' => 'encrypted',
            'two_factor_recovery_codes' => 'encrypted:array',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function hasTwoFactorEnabled(): bool
    {
        return $this->two_factor_confirmed_at !== null;
    }

    /**
     * Only Admin and Hospital accounts can enable 2FA — they hold the
     * platform's root of trust and hospital RSA private keys respectively,
     * so a compromised password there has far more reach than a
     * compromised Doctor/Patient/Pharmacist account.
     */
    public function canUseTwoFactor(): bool
    {
        return in_array($this->role, ['admin', 'hospital'], true);
    }

    public function hospital(): HasOne
    {
        return $this->hasOne(Hospital::class);
    }

    public function doctorProfile(): HasOne
    {
        return $this->hasOne(DoctorProfile::class);
    }

    public function pharmacistProfile(): HasOne
    {
        return $this->hasOne(PharmacistProfile::class);
    }

    public function prescriptionsWritten(): HasMany
    {
        return $this->hasMany(Prescription::class, 'doctor_id');
    }

    public function prescriptionsReceived(): HasMany
    {
        return $this->hasMany(Prescription::class, 'patient_id');
    }

    public function accessGrants(): HasMany
    {
        return $this->hasMany(PatientAccessGrant::class, 'patient_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Short, human-readable identifier a patient can read out to a doctor
     * (e.g. over the phone or at a desk) instead of their raw numeric id.
     */
    public function getPatientCodeAttribute(): ?string
    {
        return $this->role === 'patient' ? sprintf('PT-%06d', $this->id) : null;
    }

    /**
     * Resolve a doctor's search input back to a patient account. Accepts
     * either a "PT-000004"-style code or the patient's email address.
     */
    public static function resolvePatient(string $identifier): ?self
    {
        $identifier = trim($identifier);

        if (preg_match('/^PT-0*(\d+)$/i', $identifier, $matches)) {
            return static::where('id', (int) $matches[1])->where('role', 'patient')->first();
        }

        return static::where('email', $identifier)->where('role', 'patient')->first();
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isHospital(): bool
    {
        return $this->role === 'hospital';
    }

    public function isDoctor(): bool
    {
        return $this->role === 'doctor';
    }

    public function isPatient(): bool
    {
        return $this->role === 'patient';
    }

    public function isPharmacist(): bool
    {
        return $this->role === 'pharmacist';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
