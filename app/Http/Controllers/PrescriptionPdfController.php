<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Prescription;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Printable PDF of a prescription — the "physical slip" the offline
 * pharmacy flow (see README) assumes a patient can carry in without an
 * app. Deliberately restricted to the three parties who already have full,
 * unrestricted access to this prescription's contents elsewhere in the
 * app (the doctor who wrote it, the patient it belongs to, the hospital
 * it was written under) — pharmacists and admins go through the
 * code-lookup / audit-log paths instead, never a direct PDF link.
 */
class PrescriptionPdfController extends Controller
{
    public function show(Request $request, Prescription $prescription): Response|StreamedResponse
    {
        $user = $request->user();

        $authorized = match ($user->role) {
            'doctor' => $prescription->doctor_id === $user->id,
            'patient' => $prescription->patient_id === $user->id,
            'hospital' => $prescription->hospital_id === $user->hospital?->id,
            default => false,
        };

        abort_unless($authorized, 403);

        $prescription->load(['doctor.doctorProfile', 'hospital']);

        AuditLog::record('prescription.pdf_downloaded', $prescription);

        $pdf = Pdf::loadView('pdf.prescription', ['prescription' => $prescription]);

        return $pdf->download("prescription-{$prescription->lookup_code}.pdf");
    }
}
