<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    /**
     * Stream a verification document from private storage.
     * Every access is audit-logged since these are sensitive identity/license documents.
     */
    public function show(Request $request): StreamedResponse
    {
        $path = $request->query('path');

        // Reject traversal sequences ourselves rather than letting Flysystem's
        // internal PathTraversalDetected exception bubble up as an unhandled
        // 500 (which, with APP_DEBUG on, leaks a full stack trace and the
        // attempted payload). No legitimate document path ever contains "..".
        abort_unless(
            is_string($path)
                && str_starts_with($path, 'verification-documents/')
                && ! str_contains($path, '..')
                && Storage::disk('local')->exists($path),
            404
        );

        AuditLog::record('document.viewed', null, ['path' => $path]);

        return Storage::disk('local')->response($path);
    }
}
