<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMustChangePasswordHandled
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        $isBypassRoute = $request->routeIs([
            'profile.edit',
            'profile.update',
            'password.update',
            'logout',
        ]);

        if (! $isBypassRoute && $user->hasRole('lecturer') && $user->must_change_password) {
            return redirect()->route('profile.edit')
                ->with('warning', 'Ban can doi mat khau tam truoc khi tiep tuc.');
        }

        return $next($request);
    }
}
