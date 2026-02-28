<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended($this->roleBasedRedirect());
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Determine the redirect URL based on the authenticated user's role.
     */
    protected function roleBasedRedirect(): string
    {
        $user = Auth::user();

        if ($user->hasRole('admin')) {
            return route('admin.dashboard');
        }

        if ($user->hasRole('department_admin')) {
            return route('admin.dashboard');
        }

        if ($user->hasRole('lecturer') || $user->hasRole('teaching_assistant')) {
            return route('lecturer.dashboard');
        }

        if ($user->hasRole('student')) {
            return route('student.dashboard');
        }

        // Fallback nếu user chưa được gán role
        return route('dashboard');
    }
}
