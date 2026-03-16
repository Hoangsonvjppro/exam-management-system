<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $admin = Auth::guard('admin')->user();

        if ($admin && ! $admin->is_active) {
            Auth::guard('admin')->logout();

            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            return redirect()->route('filament.admin.auth.login')
                ->with('error', 'Tai khoan admin da bi khoa.');
        }

        if ($admin && $admin->must_change_password) {
            $isOwnEditRoute = $request->routeIs('filament.admin.resources.admins.edit')
                && (string) $request->route('record') === (string) $admin->id;

            $isLogoutRoute = $request->routeIs('filament.admin.auth.logout');

            if (! $isOwnEditRoute && ! $isLogoutRoute) {
                return redirect()->route('filament.admin.resources.admins.edit', [
                    'record' => $admin->id,
                ])->with('warning', 'Ban phai doi mat khau quan tri truoc khi tiep tuc.');
            }
        }

        return $next($request);
    }
}
