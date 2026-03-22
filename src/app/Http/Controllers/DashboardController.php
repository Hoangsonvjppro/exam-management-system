<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        /** @var User|null $user */
        $user = Auth::user();

        if ($user?->hasRole('lecturer')) {
            return redirect()->route('lecturer.dashboard');
        }

        if ($user?->hasRole('student')) {
            return redirect()->route('student.dashboard');
        }

        return redirect()->route('landing');
    }
}
