<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\PatientAccessGrant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccessGrantController extends Controller
{
    public function index(Request $request): View
    {
        $grants = $request->user()->accessGrants()->latest()->get();

        return view('patient.access.index', ['grants' => $grants]);
    }

    /**
     * Generate a new code the patient can read out to a new doctor to
     * unlock their history from other hospitals for a limited time.
     */
    public function store(Request $request): RedirectResponse
    {
        $grant = PatientAccessGrant::create([
            'patient_id' => $request->user()->id,
            'code' => PatientAccessGrant::generateUniqueCode(),
            'expires_at' => now()->addHours(PatientAccessGrant::VALIDITY_HOURS),
        ]);

        AuditLog::record('patient_access_grant.created', $grant);

        return redirect()->route('patient.access.index')
            ->with('status', "Access code generated: {$grant->code}. Share it with your doctor — it expires in ".PatientAccessGrant::VALIDITY_HOURS.' hours.');
    }

    public function revoke(Request $request, PatientAccessGrant $grant): RedirectResponse
    {
        if ($grant->patient_id !== $request->user()->id) {
            abort(404);
        }

        $grant->update(['revoked_at' => now()]);

        AuditLog::record('patient_access_grant.revoked', $grant);

        return redirect()->route('patient.access.index')->with('status', 'Access code revoked.');
    }
}
