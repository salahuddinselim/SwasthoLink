<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Prescription;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PharmacistDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $pharmacistId = $request->user()->id;

        $stats = [
            'lookups' => AuditLog::where('user_id', $pharmacistId)->where('action', 'prescription.lookup')->count(),
            'verified' => AuditLog::where('user_id', $pharmacistId)->where('action', 'prescription.verified')->count(),
            'dispensed' => Prescription::where('dispensed_by', $pharmacistId)->count(),
            'failed_verifications' => AuditLog::where('user_id', $pharmacistId)->where('action', 'prescription.verify_failed')->count(),
        ];

        $recentActivity = AuditLog::where('user_id', $pharmacistId)
            ->whereIn('action', ['prescription.lookup', 'prescription.verified', 'prescription.verify_failed', 'prescription.dispensed'])
            ->latest()
            ->limit(10)
            ->get();

        $recentDispensed = Prescription::where('dispensed_by', $pharmacistId)
            ->latest('dispensed_at')
            ->limit(5)
            ->get();

        return view('pharmacist.dashboard', [
            'stats' => $stats,
            'recentActivity' => $recentActivity,
            'recentDispensed' => $recentDispensed,
        ]);
    }
}
