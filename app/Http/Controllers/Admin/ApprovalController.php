<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\DoctorProfile;
use App\Models\Hospital;
use App\Models\PharmacistProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApprovalController extends Controller
{
    public function index(): View
    {
        $hospitals = Hospital::with('user')->whereHas('user', fn ($q) => $q->where('status', 'pending'))->latest()->get();
        $doctors = DoctorProfile::with(['user', 'hospital'])->whereHas('user', fn ($q) => $q->where('status', 'pending'))->latest()->get();
        $pharmacists = PharmacistProfile::with('user')->whereHas('user', fn ($q) => $q->where('status', 'pending'))->latest()->get();

        return view('admin.approvals', [
            'hospitals' => $hospitals,
            'doctors' => $doctors,
            'pharmacists' => $pharmacists,
        ]);
    }

    public function approveHospital(Hospital $hospital): RedirectResponse
    {
        $hospital->update([
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);
        $hospital->user->update(['status' => 'active']);

        AuditLog::record('hospital.approved', $hospital);

        return back()->with('status', "Hospital \"{$hospital->name}\" approved.");
    }

    public function rejectHospital(Request $request, Hospital $hospital): RedirectResponse
    {
        $request->validate(['reason' => ['required', 'string', 'max:255']]);

        $hospital->update(['rejection_reason' => $request->reason]);
        $hospital->user->update(['status' => 'rejected']);

        AuditLog::record('hospital.rejected', $hospital, ['reason' => $request->reason]);

        return back()->with('status', "Hospital \"{$hospital->name}\" rejected.");
    }

    public function approveDoctor(DoctorProfile $doctor): RedirectResponse
    {
        $doctor->update([
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);
        $doctor->user->update(['status' => 'active']);

        AuditLog::record('doctor.approved', $doctor);

        return back()->with('status', "{$doctor->user->name} approved.");
    }

    public function rejectDoctor(Request $request, DoctorProfile $doctor): RedirectResponse
    {
        $request->validate(['reason' => ['required', 'string', 'max:255']]);

        $doctor->update(['rejection_reason' => $request->reason]);
        $doctor->user->update(['status' => 'rejected']);

        AuditLog::record('doctor.rejected', $doctor, ['reason' => $request->reason]);

        return back()->with('status', "{$doctor->user->name} rejected.");
    }

    public function approvePharmacist(PharmacistProfile $pharmacist): RedirectResponse
    {
        $pharmacist->update([
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);
        $pharmacist->user->update(['status' => 'active']);

        AuditLog::record('pharmacist.approved', $pharmacist);

        return back()->with('status', "Pharmacist \"{$pharmacist->pharmacy_name}\" approved.");
    }

    public function rejectPharmacist(Request $request, PharmacistProfile $pharmacist): RedirectResponse
    {
        $request->validate(['reason' => ['required', 'string', 'max:255']]);

        $pharmacist->update(['rejection_reason' => $request->reason]);
        $pharmacist->user->update(['status' => 'rejected']);

        AuditLog::record('pharmacist.rejected', $pharmacist, ['reason' => $request->reason]);

        return back()->with('status', "Pharmacist \"{$pharmacist->pharmacy_name}\" rejected.");
    }
}
