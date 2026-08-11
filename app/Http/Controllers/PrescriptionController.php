<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PrescriptionController extends Controller
{
    /**
     * Doctor: form to write a new prescription.
     */
    public function create(): View
    {
        return view('doctor.prescriptions.create');
    }

    /**
     * Doctor: store a new prescription. Patients are matched by email if they
     * already have an account; otherwise the prescription is still created
     * and can be claimed by lookup code at the pharmacy.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'patient_name' => ['required', 'string', 'max:255'],
            'patient_email' => ['nullable', 'email', 'max:255'],
            'medicines' => ['required', 'string', 'max:5000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $doctor = $request->user();
        $patient = $request->patient_email
            ? User::where('email', $request->patient_email)->where('role', 'patient')->first()
            : null;

        $prescription = Prescription::create([
            'doctor_id' => $doctor->id,
            'hospital_id' => $doctor->doctorProfile?->hospital_id,
            'patient_id' => $patient?->id,
            'patient_name' => $request->patient_name,
            'patient_email' => $request->patient_email,
            'medicines' => $request->medicines,
            'notes' => $request->notes,
        ]);

        AuditLog::record('prescription.created', $prescription);

        return redirect()->route('doctor.prescriptions.index')
            ->with('status', "Prescription created. Lookup code: {$prescription->lookup_code}");
    }

    /**
     * Doctor: list prescriptions they've written.
     */
    public function doctorIndex(Request $request): View
    {
        $prescriptions = $request->user()->prescriptionsWritten()->latest()->paginate(15);

        return view('doctor.prescriptions.index', ['prescriptions' => $prescriptions]);
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
     * Pharmacist: resolve a lookup code and show the prescription.
     * Signature verification against the doctor's RSA key is added in the
     * next phase; for now this confirms the code exists and isn't already
     * dispensed.
     */
    public function lookup(Request $request): View
    {
        $request->validate(['code' => ['required', 'string', 'max:20']]);

        $prescription = Prescription::with(['doctor.doctorProfile', 'hospital'])
            ->where('lookup_code', strtoupper(trim($request->code)))
            ->first();

        AuditLog::record('prescription.lookup', $prescription, ['code' => $request->code]);

        return view('pharmacist.lookup', [
            'prescription' => $prescription,
            'searched' => true,
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
