<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Throwable;
use Illuminate\Support\Carbon;

class GoogleLoginController extends Controller
{
    public function redirect(): RedirectResponse
    {
        try {
            return Socialite::driver('google')->redirect();
        } catch (Throwable $e) {
            Log::warning('Google OAuth redirect failed', [
                'message' => $e->getMessage(),
            ]);

            return redirect()->route('login')->withErrors([
                'google_auth' => 'Không thể kết nối Google. Vui lòng thử lại.',
            ]);
        }
    }

    public function callback(Request $request): RedirectResponse
    {
        if ($request->filled('error')) {
            return redirect()->route('login')->withErrors([
                'google_auth' => 'Đăng nhập Google đã bị hủy.',
            ]);
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $e) {
            Log::warning('Google OAuth callback failed', [
                'message' => $e->getMessage(),
            ]);

            return redirect()->route('login')->withErrors([
                'google_auth' => 'Xác thực Google thất bại. Vui lòng thử lại.',
            ]);
        }

        $email = $googleUser->getEmail();

        if (! $email) {
            return redirect()->route('login')->withErrors([
                'google_auth' => 'Tài khoản Google không cung cấp email hợp lệ.',
            ]);
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            $user = new User();
            $user->name = $googleUser->getName() ?: 'Google User';
            $user->email = $email;
            $user->google_id = $googleUser->getId();
            $user->password = null;
            $user->email_verified_at = Carbon::now();
            $user->save();
        } elseif (! $user->google_id) {
            $user->google_id = $googleUser->getId();
            $user->save();
        }

        Auth::login($user);

        return redirect()->route('dashboard');
    }
}
