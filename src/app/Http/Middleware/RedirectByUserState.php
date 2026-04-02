<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class RedirectByUserState
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return $next($request);
        }

        /** @var User $user */
        $user = Auth::user();

        if (! Schema::hasTable('roles') || ! Schema::hasTable('model_has_roles')) {
            return $next($request);
        }

        try {
            if ($user->hasRole('lecturer')) {
                return redirect()->route('lecturer.dashboard');
            }

            if ($user->hasRole('student')) {
                return redirect()->route('student.dashboard');
            }
        } catch (QueryException) {
            return $next($request);
        }

        return $next($request);
    }
}
