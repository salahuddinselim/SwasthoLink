<?php

namespace App\Http\Controllers;

use App\Models\HospitalShare;
use App\Models\Prescription;
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

        $stats = [
            'total_prescriptions' => Prescription::where('hospital_id', $hospital->id)->count(),
            'total_patients' => Prescription::where('hospital_id', $hospital->id)->distinct('patient_phone')->count('patient_phone'),
            'shares_pending' => HospitalShare::where(fn ($q) => $q->where('initiator_hospital_id', $hospital->id)->orWhere('recipient_hospital_id', $hospital->id))
                ->where('status', 'pending')->count(),
            'shares_completed' => HospitalShare::where(fn ($q) => $q->where('initiator_hospital_id', $hospital->id)->orWhere('recipient_hospital_id', $hospital->id))
                ->where('status', 'completed')->count(),
        ];

        // One row per patient this hospital's doctors have ever prescribed
        // for, most recently active first — this is the "patient details"
        // view for hospital staff: who's been treated here and how often.
        $patients = Prescription::where('hospital_id', $hospital->id)
            ->whereNotNull('patient_name')
            ->selectRaw('patient_phone, MAX(patient_id) as patient_id, MAX(patient_name) as patient_name, MAX(patient_email) as patient_email, COUNT(*) as prescription_count, MAX(created_at) as last_prescribed_at')
            ->groupBy('patient_phone')
            ->orderByDesc('last_prescribed_at')
            ->get();

        return view('hospital.dashboard', ['hospital' => $hospital, 'patients' => $patients, 'stats' => $stats]);
    }

    public function exportPatientsCsv(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $hospital = $request->user()->hospital;

        $patients = Prescription::where('hospital_id', $hospital->id)
            ->whereNotNull('patient_name')
            ->selectRaw('patient_phone, MAX(patient_id) as patient_id, MAX(patient_name) as patient_name, MAX(patient_email) as patient_email, COUNT(*) as prescription_count, MAX(created_at) as last_prescribed_at')
            ->groupBy('patient_phone')
            ->orderByDesc('last_prescribed_at')
            ->get();

        return response()->streamDownload(function () use ($patients) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Patient ID', 'Name', 'Phone', 'Email', 'Prescriptions', 'Last Visit']);
            foreach ($patients as $p) {
                fputcsv($out, [
                    $p->patient_id ? sprintf('PT-%06d', $p->patient_id) : '',
                    $p->patient_name, $p->patient_phone, $p->patient_email,
                    $p->prescription_count, $p->last_prescribed_at,
                ]);
            }
            fclose($out);
        }, 'patients-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }
}
