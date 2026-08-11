<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\DoctorProfile;
use App\Models\Hospital;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredDoctorController extends Controller
{
    public function create(): View
    {
        $hospitals = Hospital::whereNotNull('verified_at')->orderBy('name')->get();

        return view('auth.register-doctor', ['hospitals' => $hospitals]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'bmdc_number' => ['required', 'string', 'max:100', 'unique:doctor_profiles'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'hospital_id' => ['nullable', 'exists:hospitals,id'],
            'bmdc_certificate' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'nid_document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $user = DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'doctor',
                'status' => 'pending',
            ]);

            $certPath = $request->file('bmdc_certificate')->store('verification-documents/doctors', 'local');
            $nidPath = $request->file('nid_document')->store('verification-documents/doctors', 'local');

            DoctorProfile::create([
                'user_id' => $user->id,
                'hospital_id' => $request->hospital_id,
                'bmdc_number' => $request->bmdc_number,
                'specialization' => $request->specialization,
                'bmdc_certificate_path' => $certPath,
                'nid_document_path' => $nidPath,
            ]);

            return $user;
        });

        Auth::login($user);

        return redirect()->route('pending-approval');
    }
}
