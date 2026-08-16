<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $notifications = $request->user()->notifications()->latest()->paginate(20);

        return view('notifications.index', ['notifications' => $notifications]);
    }

    public function read(Request $request, Notification $notification): RedirectResponse
    {
        abort_unless($notification->user_id === $request->user()->id, 404);

        if (! $notification->isRead()) {
            $notification->update(['read_at' => now()]);
        }

        return $notification->url ? redirect($notification->url) : back();
    }

    public function readAll(Request $request): RedirectResponse
    {
        $request->user()->notifications()->whereNull('read_at')->update(['read_at' => now()]);

        return back();
    }
}
