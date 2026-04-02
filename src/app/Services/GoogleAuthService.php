<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class GoogleAuthService
{
    public function findOrCreateFromGoogleUser(SocialiteUser $googleUser): User
    {
        $email = $googleUser->getEmail();
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

            // Gán role student ngay lập tức cho user mới đăng ký qua Google.
            if (Schema::hasTable('roles') && Schema::hasTable('model_has_roles')) {
                try {
                    $user->assignRole('student');
                } catch (\Throwable) {
                    // Fallback: UserStateService sẽ sync role sau.
                }
            }

            return $user;
        }

        $user->google_id = $user->google_id ?: $googleId;
        $user->name = $googleUser->getName() ?: $user->name;
        $user->google_avatar = $googleUser->getAvatar();
        $user->save();

        return $user;
    }
}
