<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'pending_hospitals' => User::where('role', 'hospital')->where('status', 'pending')->count(),
            'pending_doctors' => User::where('role', 'doctor')->where('status', 'pending')->count(),
            'pending_pharmacists' => User::where('role', 'pharmacist')->where('status', 'pending')->count(),
            'active_hospitals' => User::where('role', 'hospital')->where('status', 'active')->count(),
            'active_doctors' => User::where('role', 'doctor')->where('status', 'active')->count(),
            'active_pharmacists' => User::where('role', 'pharmacist')->where('status', 'active')->count(),
            'total_patients' => User::where('role', 'patient')->count(),
            'total_prescriptions' => Prescription::count(),
        ];

        $recentActivity = AuditLog::with('user')->latest()->limit(10)->get();

        return view('admin.dashboard', [
            'stats' => $stats,
            'recentActivity' => $recentActivity,
        ]);
    }
}
