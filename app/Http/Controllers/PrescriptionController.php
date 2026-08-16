<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Prescription;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\PrescriptionSigningService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PrescriptionController extends Controller
{
    public function __construct(
        private PrescriptionSigningService $signing,
        private NotificationService $notifications,
    ) {}

    /**
     * Doctor: form to write a new prescription.
     */
    public function create(): View
    {
        return view('doctor.prescriptions.create');
    }

    /**
     * Doctor: store a new prescription, signed with the doctor's RSA private
     * key. Patients are matched by email if they already have an account;
     * otherwise the prescription is still created and can be claimed by
     * lookup code at the pharmacy.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'patient_name' => ['required', 'string', 'max:255'],
            'patient_email' => ['nullable', 'email', 'max:255'],
            'patient_phone' => ['required', 'string', 'max:32', 'min:4'],
            'medicines' => ['required', 'string', 'max:5000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $doctor = $request->user();
        $doctorProfile = $doctor->doctorProfile;

        if (! $doctorProfile?->rsa_private_key_encrypted) {
            return back()->withInput()->with('error',
                'Your account does not have a signing key yet. Contact an administrator.');
        }

        $patient = $request->patient_email
            ? User::where('email', $request->patient_email)->where('role', 'patient')->first()
            : null;

        $prescription = Prescription::create([
            'doctor_id' => $doctor->id,
            'hospital_id' => $doctorProfile->hospital_id,
            'patient_id' => $patient?->id,
            'patient_name' => $request->patient_name,
            'patient_email' => $request->patient_email,
            'patient_phone' => $request->patient_phone,
            'medicines' => $request->medicines,
            'notes' => $request->notes,
        ]);

        $prescription->update([
            'signature' => $this->signing->sign($prescription, $doctorProfile->rsa_private_key_encrypted),
        ]);

        AuditLog::record('prescription.created', $prescription);

        if ($patient) {
            $this->notifications->notify(
                $patient,
                'prescription.created',
                "New prescription from {$doctor->name}",
                "Lookup code {$prescription->lookup_code}",
                route('patient.prescriptions.index'),
            );
        }

        return redirect()->route('doctor.prescriptions.index')
            ->with('status', "Prescription created. Lookup code: {$prescription->lookup_code}")
            ->with('new_lookup_code', $prescription->lookup_code);
    }

    /**
     * Doctor: list prescriptions they've written, with an at-a-glance
     * summary (how many patients, how many still active) and optional
     * search by patient name / lookup code and a date range.
     */
    public function doctorIndex(Request $request): View
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        $base = $request->user()->prescriptionsWritten();

        $stats = [
            'total' => (clone $base)->count(),
            'active' => (clone $base)->where('status', 'active')->count(),
            'dispensed' => (clone $base)->where('status', 'dispensed')->count(),
            'unique_patients' => (clone $base)->distinct('patient_phone')->count('patient_phone'),
        ];

        $prescriptions = $base
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = $request->search;
                $q->where(fn ($q2) => $q2->where('patient_name', 'like', "%{$term}%")
                    ->orWhere('lookup_code', 'like', '%'.strtoupper($term).'%'));
            })
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->to))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('doctor.prescriptions.index', ['prescriptions' => $prescriptions, 'stats' => $stats]);
    }

    /**
     * Doctor: export their own prescription history as CSV (respects the
     * same search/date filters as the list view).
     */
    public function doctorExportCsv(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $prescriptions = $request->user()->prescriptionsWritten()
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = $request->search;
                $q->where(fn ($q2) => $q2->where('patient_name', 'like', "%{$term}%")
                    ->orWhere('lookup_code', 'like', '%'.strtoupper($term).'%'));
            })
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->to))
            ->latest()
            ->get();

        return response()->streamDownload(function () use ($prescriptions) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Lookup Code', 'Patient Name', 'Patient Phone', 'Medicines', 'Status', 'Issued', 'Expires']);
            foreach ($prescriptions as $p) {
                fputcsv($out, [
                    $p->lookup_code, $p->patient_name, $p->patient_phone, $p->medicines,
                    $p->status, $p->created_at->format('Y-m-d H:i'), $p->expires_at?->format('Y-m-d'),
                ]);
            }
            fclose($out);
        }, 'prescriptions-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }

    /**
     * Patient: list prescriptions matched to their account.
     */
    public function patientIndex(Request $request): View
    {
        $prescriptions = $request->user()->prescriptionsReceived()->with('doctor')->latest()->paginate(15);

        return view('patient.prescriptions.index', ['prescriptions' => $prescriptions]);
    }

    /**
     * Pharmacist: lookup form.
     */
    public function lookupForm(): View
    {
        return view('pharmacist.lookup');
    }

    /**
     * Pharmacist: stage 1 — resolve a lookup code. If found, valid, and not
     * expired, this does NOT reveal medicines/notes yet; it asks for the
     * patient's phone last 4 digits as a second factor before revealing
     * anything sensitive.
     */
    public function lookup(Request $request): View
    {
        $request->validate(['code' => ['required', 'string', 'max:20']]);

        $prescription = Prescription::with(['doctor.doctorProfile', 'hospital'])
            ->where('lookup_code', strtoupper(trim($request->code)))
            ->first();

        AuditLog::record('prescription.lookup', $prescription, ['code' => $request->code]);

        $expired = $prescription?->isExpired() ?? false;

        return view('pharmacist.lookup', [
            'prescription' => $expired ? null : $prescription,
            'expired' => $expired,
            'searched' => true,
            'awaitingSecondFactor' => $prescription && ! $expired,
        ]);
    }

    /**
     * Pharmacist: stage 2 — confirm the patient's phone last 4 digits, then
     * reveal the full prescription along with RSA signature verification
     * against the issuing doctor's public key.
     */
    public function verify(Request $request): View
    {
        $request->validate([
            'code' => ['required', 'string', 'max:20'],
            'phone_last4' => ['required', 'string', 'max:4'],
        ]);

        $prescription = Prescription::with(['doctor.doctorProfile', 'hospital'])
            ->where('lookup_code', strtoupper(trim($request->code)))
            ->first();

        if (! $prescription || $prescription->isExpired()) {
            AuditLog::record('prescription.verify_failed', $prescription, ['reason' => 'not_found_or_expired']);

            return view('pharmacist.lookup', ['prescription' => null, 'searched' => true]);
        }

        $expectedLast4 = substr(preg_replace('/\D/', '', (string) $prescription->patient_phone), -4);
        $givenLast4 = preg_replace('/\D/', '', $request->phone_last4);

        if ($expectedLast4 === '' || $givenLast4 !== $expectedLast4) {
            AuditLog::record('prescription.verify_failed', $prescription, ['reason' => 'phone_mismatch']);

            return view('pharmacist.lookup', [
                'prescription' => $prescription,
                'searched' => true,
                'awaitingSecondFactor' => true,
                'secondFactorError' => 'Phone digits did not match our records.',
            ]);
        }

        $signatureValid = $prescription->doctor->doctorProfile?->rsa_public_key
            && $this->signing->verify($prescription, $prescription->doctor->doctorProfile->rsa_public_key);

        AuditLog::record('prescription.verified', $prescription, ['signature_valid' => (bool) $signatureValid]);

        return view('pharmacist.lookup', [
            'prescription' => $prescription,
            'searched' => true,
            'revealed' => true,
            'signatureValid' => $signatureValid,
        ]);
    }

    /**
     * Pharmacist: mark a prescription as dispensed so its code can't be reused.
     */
    public function dispense(Request $request, Prescription $prescription): RedirectResponse
    {
        if ($prescription->status === 'dispensed') {
            return back()->with('error', 'This prescription has already been dispensed.');
        }

        if ($prescription->isExpired()) {
            return back()->with('error', 'This prescription code has expired and cannot be dispensed.');
        }

        $prescription->update([
            'status' => 'dispensed',
            'dispensed_by' => $request->user()->id,
            'dispensed_at' => now(),
        ]);

        AuditLog::record('prescription.dispensed', $prescription);

        return redirect()->route('pharmacist.lookup')
            ->with('status', "Prescription {$prescription->lookup_code} marked as dispensed.");
    }
}
