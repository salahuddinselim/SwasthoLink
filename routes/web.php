<?php

use App\Http\Controllers\Admin\ApprovalController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DocumentController;
use App\Http\Controllers\Doctor\PatientRecordController;
use App\Http\Controllers\Hospital\ShareController as HospitalShareController;
use App\Http\Controllers\HospitalDashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Patient\AccessGrantController;
use App\Http\Controllers\PharmacistDashboardController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\PrescriptionPdfController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TwoFactorController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    $user = auth()->user();

    if ($user->role !== 'patient' && ! $user->isActive()) {
        return redirect()->route('pending-approval');
    }

    return match ($user->role) {
        'admin' => redirect()->route('admin.dashboard'),
        'hospital' => redirect()->route('hospital.dashboard'),
        'doctor' => redirect()->route('doctor.prescriptions.index'),
        'pharmacist' => redirect()->route('pharmacist.dashboard'),
        default => redirect()->route('patient.prescriptions.index'),
    };
})->middleware('auth')->name('dashboard');

Route::get('/pending-approval', function () {
    $user = auth()->user();

    $profile = match ($user->role) {
        'hospital' => $user->hospital,
        'doctor' => $user->doctorProfile,
        'pharmacist' => $user->pharmacistProfile,
        default => null,
    };

    return view('pending-approval', [
        'rejectionReason' => $profile?->rejection_reason,
    ]);
})->middleware('auth')->name('pending-approval');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/two-factor', [TwoFactorController::class, 'show'])->name('two-factor.show');
    Route::post('/two-factor/confirm', [TwoFactorController::class, 'confirm'])->name('two-factor.confirm');
    Route::delete('/two-factor', [TwoFactorController::class, 'disable'])->name('two-factor.disable');

    // Shared across Doctor/Patient/Hospital only — PrescriptionPdfController
    // enforces per-role ownership itself since each role's "owns this
    // prescription" check is different (author vs. subject vs. issuing org).
    Route::get('/prescriptions/{prescription}/pdf', [PrescriptionPdfController::class, 'show'])->name('prescriptions.pdf');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/approvals', [ApprovalController::class, 'index'])->name('approvals');

    Route::post('/hospitals/{hospital}/approve', [ApprovalController::class, 'approveHospital'])->name('hospitals.approve');
    Route::post('/hospitals/{hospital}/reject', [ApprovalController::class, 'rejectHospital'])->name('hospitals.reject');

    Route::post('/doctors/{doctor}/approve', [ApprovalController::class, 'approveDoctor'])->name('doctors.approve');
    Route::post('/doctors/{doctor}/reject', [ApprovalController::class, 'rejectDoctor'])->name('doctors.reject');

    Route::post('/pharmacists/{pharmacist}/approve', [ApprovalController::class, 'approvePharmacist'])->name('pharmacists.approve');
    Route::post('/pharmacists/{pharmacist}/reject', [ApprovalController::class, 'rejectPharmacist'])->name('pharmacists.reject');

    Route::get('/documents', [DocumentController::class, 'show'])->name('documents.show');
    Route::get('/audit-log', [AuditLogController::class, 'index'])->name('audit-log');
    Route::get('/audit-log/export', [AuditLogController::class, 'exportCsv'])->name('audit-log.export');
});

Route::middleware(['auth', 'role:hospital'])->prefix('hospital')->name('hospital.')->group(function () {
    Route::get('/', [HospitalDashboardController::class, 'index'])->name('dashboard');

    Route::get('/shares', [HospitalShareController::class, 'index'])->name('shares.index');
    Route::post('/shares', [HospitalShareController::class, 'store'])->name('shares.store');
    Route::post('/shares/{share}/accept', [HospitalShareController::class, 'accept'])->name('shares.accept');
    Route::post('/shares/{share}/reject', [HospitalShareController::class, 'reject'])->name('shares.reject');
    Route::get('/shares/{share}', [HospitalShareController::class, 'show'])->name('shares.show');
    Route::post('/shares/{share}/revoke', [HospitalShareController::class, 'revoke'])->name('shares.revoke');
    Route::get('/patients/export', [HospitalDashboardController::class, 'exportPatientsCsv'])->name('patients.export');
});

Route::middleware(['auth', 'role:doctor'])->prefix('doctor')->name('doctor.')->group(function () {
    Route::get('/prescriptions', [PrescriptionController::class, 'doctorIndex'])->name('prescriptions.index');
    Route::get('/prescriptions/create', [PrescriptionController::class, 'create'])->name('prescriptions.create');
    Route::post('/prescriptions', [PrescriptionController::class, 'store'])->name('prescriptions.store')->middleware('throttle:30,1');

    Route::get('/patients', [PatientRecordController::class, 'index'])->name('patients.index');
    Route::post('/patients/search', [PatientRecordController::class, 'search'])->name('patients.search')->middleware('throttle:20,1');
    Route::get('/prescriptions/export', [PrescriptionController::class, 'doctorExportCsv'])->name('prescriptions.export');
});

Route::middleware(['auth', 'role:patient'])->prefix('patient')->name('patient.')->group(function () {
    Route::get('/prescriptions', [PrescriptionController::class, 'patientIndex'])->name('prescriptions.index');

    Route::get('/access', [AccessGrantController::class, 'index'])->name('access.index');
    Route::post('/access', [AccessGrantController::class, 'store'])->name('access.store');
    Route::post('/access/{grant}/revoke', [AccessGrantController::class, 'revoke'])->name('access.revoke');
});

Route::middleware(['auth', 'role:pharmacist'])->prefix('pharmacist')->name('pharmacist.')->group(function () {
    Route::get('/', [PharmacistDashboardController::class, 'index'])->name('dashboard');
    Route::get('/lookup', [PrescriptionController::class, 'lookupForm'])->name('lookup');
    Route::post('/lookup', [PrescriptionController::class, 'lookup'])->name('lookup.search')->middleware('throttle:20,1');
    Route::post('/lookup/verify', [PrescriptionController::class, 'verify'])->name('lookup.verify')->middleware('throttle:20,1');
    Route::post('/prescriptions/{prescription}/dispense', [PrescriptionController::class, 'dispense'])->name('prescriptions.dispense');
});

require __DIR__.'/auth.php';
