<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Hospital;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredHospitalController extends Controller
{
    public function create(): View
    {
        return view('auth.register-hospital');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'hospital_name' => ['required', 'string', 'max:255'],
            'registration_number' => ['required', 'string', 'max:100', 'unique:hospitals'],
            'address' => ['nullable', 'string', 'max:255'],
            'license_document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $user = DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'hospital',
                'status' => 'pending',
            ]);

            $path = $request->file('license_document')->store('verification-documents/hospitals', 'local');

            Hospital::create([
                'user_id' => $user->id,
                'name' => $request->hospital_name,
                'registration_number' => $request->registration_number,
                'address' => $request->address,
                'license_document_path' => $path,
            ]);

            return $user;
        });

        Auth::login($user);

        return redirect()->route('pending-approval');
    }
}
