<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class HospitalDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $hospital = $request->user()->hospital()->with([
            'doctorProfiles.user',
            'prescriptions' => fn ($q) => $q->latest()->limit(10),
        ])->firstOrFail();

        return view('hospital.dashboard', ['hospital' => $hospital]);
    }
}
