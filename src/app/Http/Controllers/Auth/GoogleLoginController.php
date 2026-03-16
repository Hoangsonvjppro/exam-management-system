<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserStateService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Throwable;
use Illuminate\Support\Carbon;

class GoogleLoginController extends Controller
{
    public function __construct(private readonly UserStateService $userStateService)
    {
    }

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

        $googleId = $googleUser->getId();

        $user = User::query()
            ->where('google_id', $googleId)
            ->orWhere('email', $email)
            ->first();

        if (! $user) {
            $user = new User();
            $user->name = $googleUser->getName() ?: 'Google User';
            $user->email = $email;
            $user->google_id = $googleId;
            $user->google_avatar = $googleUser->getAvatar();
            $user->password = null;
            $user->email_verified_at = Carbon::now();
            $user->is_active = true;
            $user->save();
        } else {
            $user->google_id = $user->google_id ?: $googleId;
            $user->name = $googleUser->getName() ?: $user->name;
            $user->google_avatar = $googleUser->getAvatar();
            $user->save();
        }

        Auth::login($user);

        return redirect($this->userStateService->determineHomeRoute($user));
    }
}
