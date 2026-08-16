<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $logs = AuditLog::with('user')
            ->when($request->filled('action'), fn ($q) => $q->where('action', 'like', '%'.$request->action.'%'))
            ->when($request->filled('user'), fn ($q) => $q->whereHas('user', fn ($q2) => $q2->where('name', 'like', '%'.$request->user.'%')))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        $actions = AuditLog::select('action')->distinct()->orderBy('action')->pluck('action');

        return view('admin.audit-log', ['logs' => $logs, 'actions' => $actions]);
    }

    public function exportCsv(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $logs = AuditLog::with('user')
            ->when($request->filled('action'), fn ($q) => $q->where('action', 'like', '%'.$request->action.'%'))
            ->when($request->filled('user'), fn ($q) => $q->whereHas('user', fn ($q2) => $q2->where('name', 'like', '%'.$request->user.'%')))
            ->latest()
            ->get();

        return response()->streamDownload(function () use ($logs) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['When', 'User', 'Action', 'Target Type', 'Target ID', 'IP']);
            foreach ($logs as $log) {
                fputcsv($out, [
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->user?->name ?? 'System',
                    $log->action,
                    $log->target_type ? class_basename($log->target_type) : '',
                    $log->target_id,
                    $log->ip_address,
                ]);
            }
            fclose($out);
        }, 'audit-log-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }
}
