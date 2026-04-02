<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class EnsureStudentRole
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = $request->user();

        if (! $user) {
            abort(403, 'Forbidden');
        }

        if (! Schema::hasTable('roles') || ! Schema::hasTable('model_has_roles')) {
            abort(503, 'System role tables are not ready.');
        }

        try {
            if (! $user->hasRole('student')) {
                abort(403, 'Forbidden');
            }
        } catch (QueryException) {
            abort(503, 'System role tables are not ready.');
        }

        return $next($request);
    }
}
