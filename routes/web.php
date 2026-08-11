<?php

use App\Http\Controllers\Admin\ApprovalController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DocumentController;
use App\Http\Controllers\HospitalDashboardController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\ProfileController;
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
        'pharmacist' => redirect()->route('pharmacist.lookup'),
        default => redirect()->route('patient.prescriptions.index'),
    };
})->middleware(['auth', 'verified'])->name('dashboard');

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
});

Route::middleware(['auth', 'role:hospital'])->prefix('hospital')->name('hospital.')->group(function () {
    Route::get('/', [HospitalDashboardController::class, 'index'])->name('dashboard');
});

Route::middleware(['auth', 'role:doctor'])->prefix('doctor')->name('doctor.')->group(function () {
    Route::get('/prescriptions', [PrescriptionController::class, 'doctorIndex'])->name('prescriptions.index');
    Route::get('/prescriptions/create', [PrescriptionController::class, 'create'])->name('prescriptions.create');
    Route::post('/prescriptions', [PrescriptionController::class, 'store'])->name('prescriptions.store');
});

Route::middleware(['auth', 'role:patient'])->prefix('patient')->name('patient.')->group(function () {
    Route::get('/prescriptions', [PrescriptionController::class, 'patientIndex'])->name('prescriptions.index');
});

Route::middleware(['auth', 'role:pharmacist'])->prefix('pharmacist')->name('pharmacist.')->group(function () {
    Route::get('/lookup', [PrescriptionController::class, 'lookupForm'])->name('lookup');
    Route::post('/lookup', [PrescriptionController::class, 'lookup'])->name('lookup.search');
    Route::post('/prescriptions/{prescription}/dispense', [PrescriptionController::class, 'dispense'])->name('prescriptions.dispense');
});

require __DIR__.'/auth.php';
