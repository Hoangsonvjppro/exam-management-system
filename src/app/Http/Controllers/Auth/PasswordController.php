<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\UserStateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    public function __construct(private readonly UserStateService $userStateService)
    {
    }
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $rules = [
            'password' => ['required', Password::defaults(), 'confirmed'],
        ];

        if (! $user->must_change_password) {
            $rules['current_password'] = ['required', 'current_password'];
        }
        
        $validated = $request->validateWithBag('updatePassword', $rules);

        $user->update([
            'password' => Hash::make($validated['password']),
            'must_change_password' => false,
            'password_changed_at' => now(),
        ]);

        return redirect()->intended($this->userStateService->determineHomeRoute($user))->with('status', 'password-update');
    }
}
