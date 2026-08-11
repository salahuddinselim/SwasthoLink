<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Restrict access to users whose role matches one of the given roles.
     * Also blocks accounts that are pending/rejected from role-gated areas,
     * except patients, whose accounts are always active on registration.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role, $roles, true)) {
            abort(403, 'You are not authorized to access this page.');
        }

        if ($user->role !== 'patient' && ! $user->isActive()) {
            abort(403, 'Your account is awaiting verification. You will get access once an admin approves it.');
        }

        return $next($request);
    }
}
