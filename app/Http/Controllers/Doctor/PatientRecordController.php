<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\PatientAccessGrant;
use App\Models\Prescription;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Lets a doctor pull up a patient's prescription history before writing a
 * new one. Same-hospital history is visible automatically — the patient
 * already has a relationship with this hospital, and it's the same trust
 * boundary that let the doctor sign prescriptions here in the first place.
 * History from *other* hospitals/doctors stays hidden unless the patient
 * hands the doctor a one-time access code (see PatientAccessGrant) — e.g.
 * when they've switched doctors and want their new one to see prior
 * treatment for the same condition.
 */
class PatientRecordController extends Controller
{
    public function __construct(private NotificationService $notifications) {}

    public function index(): View
    {
        return view('doctor.patients.lookup');
    }

    public function search(Request $request): View
    {
        $request->validate([
            'patient_identifier' => ['required', 'string', 'max:255'],
            'access_code' => ['nullable', 'string', 'max:20'],
        ]);

        $doctor = $request->user();
        $hospitalId = $doctor->doctorProfile?->hospital_id;

        $patient = User::resolvePatient($request->patient_identifier);

        if (! $patient) {
            return view('doctor.patients.lookup', ['searched' => true, 'patient' => null]);
        }

        AuditLog::record('patient_record.searched', $patient);

        $sameHospitalPrescriptions = $hospitalId
            ? Prescription::where('patient_id', $patient->id)
                ->where('hospital_id', $hospitalId)
                ->with('doctor')
                ->latest()
                ->get()
            : collect();

        $grant = null;
        $externalPrescriptions = collect();
        $accessError = null;

        if ($request->filled('access_code')) {
            $grant = PatientAccessGrant::where('patient_id', $patient->id)
                ->where('code', strtoupper(trim($request->access_code)))
                ->first();

            if (! $grant || ! $grant->isActive()) {
                $accessError = 'That access code is invalid, expired, or has been revoked.';
                AuditLog::record('patient_access_grant.rejected', $patient, ['code' => $request->access_code]);
            } else {
                $grant->increment('use_count');
                $grant->update(['last_used_at' => now()]);

                AuditLog::record('patient_access_grant.used', $grant);

                $this->notifications->notify(
                    $patient,
                    'patient_access_grant.used',
                    "Dr. {$doctor->name} viewed your other-hospital records",
                    'Using the access code you shared',
                    route('patient.access.index'),
                );

                $externalPrescriptions = Prescription::where('patient_id', $patient->id)
                    ->when($hospitalId, fn ($q) => $q->where(fn ($q2) => $q2->where('hospital_id', '!=', $hospitalId)->orWhereNull('hospital_id')))
                    ->with(['doctor', 'hospital'])
                    ->latest()
                    ->get();
            }
        }

        return view('doctor.patients.lookup', [
            'searched' => true,
            'patient' => $patient,
            'sameHospitalPrescriptions' => $sameHospitalPrescriptions,
            'externalPrescriptions' => $externalPrescriptions,
            'accessGranted' => (bool) ($grant && $grant->isActive() && ! $accessError),
            'accessError' => $accessError,
        ]);
    }
}
