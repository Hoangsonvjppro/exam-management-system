<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            abort(403, 'Forbidden');
        }

        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (! $user) {
            abort(403, 'Forbidden');
        }

        if (! Schema::hasTable('roles') || ! Schema::hasTable('model_has_roles')) {
            abort(503, 'System role tables are not ready.');
        }

        try {
            if (! $user->hasAnyRole(['admin', 'department_admin'])) {
                abort(403, 'Forbidden');
            }
        } catch (QueryException) {
            abort(503, 'System role tables are not ready.');
        }

        return $next($request);
    }
}
