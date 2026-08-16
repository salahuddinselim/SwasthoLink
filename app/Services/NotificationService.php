<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

/**
 * Small in-app bell notifications — separate from AuditLog, which is a
 * permanent compliance record nobody "reads"; these are ephemeral, scoped
 * to one recipient, and meant to be seen and dismissed.
 */
class NotificationService
{
    public function notify(User $user, string $type, string $title, ?string $body = null, ?string $url = null): Notification
    {
        return Notification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'url' => $url,
        ]);
    }
}
