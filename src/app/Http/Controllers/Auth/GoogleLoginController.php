<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\GoogleAuthService;
use App\Services\UserStateService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleLoginController extends Controller
{
    public function __construct(
        private readonly UserStateService $userStateService,
        private readonly GoogleAuthService $googleAuthService,
    ) {}

    public function redirect(): RedirectResponse
    {
        try {
            return Socialite::driver('google')->redirect();
        } catch (Throwable $e) {
            Log::warning('Google OAuth redirect failed', [
                'message' => $e->getMessage(),
            ]);

            return redirect()->route('landing')->withErrors([
                'google_auth' => 'Không thể kết nối Google. Vui lòng thử lại.',
            ]);
        }
    }

    public function callback(Request $request): RedirectResponse
    {
        if ($request->filled('error')) {
            return redirect()->route('landing')->withErrors([
                'google_auth' => 'Đăng nhập Google đã bị hủy.',
            ]);
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $e) {
            Log::warning('Google OAuth callback failed', [
                'message' => $e->getMessage(),
            ]);

            return redirect()->route('landing')->withErrors([
                'google_auth' => 'Xác thực Google thất bại. Vui lòng thử lại.',
            ]);
        }

        $email = $googleUser->getEmail();

        if (! $email) {
            return redirect()->route('landing')->withErrors([
                'google_auth' => 'Tài khoản Google không cung cấp email hợp lệ.',
            ]);
        }

        $user = $this->googleAuthService->findOrCreateFromGoogleUser($googleUser);

        // Lecturers must not log in via Google.
        if ($user->hasRole('lecturer')) {
            return redirect()->route('login')->withErrors([
                'google_auth' => 'Giảng viên không thể đăng nhập bằng Google. Vui lòng dùng email và mật khẩu.',
            ]);
        }

        Auth::login($user);

        return redirect($this->userStateService->determineHomeRoute($user));
    }
}
