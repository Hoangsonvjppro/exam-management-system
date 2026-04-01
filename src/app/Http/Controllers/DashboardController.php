<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('landing');
        }

        if (! Schema::hasTable('roles') || ! Schema::hasTable('model_has_roles')) {
            return redirect()->route('landing');
        }

        try {
            if ($user->hasRole('lecturer')) {
                return redirect()->route('lecturer.dashboard');
            }

            if ($user->hasRole('student')) {
                return redirect()->route('student.dashboard');
            }
        } catch (QueryException) {
            return redirect()->route('landing');
        }

        return redirect()->route('landing');
    }
}
