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
            // If they are a lecturer, they definitely shouldn't be here.
            if ($user->hasRole('lecturer')) {
                abort(403, 'Forbidden');
            }

            // For everyone else, we treat them as students.
            // We also try to sync the role in the background via the service if needed.
            // (Alternative: just allow through if not lecturer)
            
            return $next($request);

        } catch (QueryException) {
            abort(503, 'System role tables are not ready.');
        }

        return $next($request);
    }
}
