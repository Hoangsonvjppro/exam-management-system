<?php

use App\Http\Controllers\Auth\GoogleLoginController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\StudentEnrollmentController;
use App\Http\Controllers\StudentOnboardingController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->middleware('redirect_by_user_state')->name('landing');

Route::get('/auth/google', [GoogleLoginController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [GoogleLoginController::class, 'callback'])->name('google.callback');

Route::middleware(['auth', 'must_change_password_handled'])->group(function () {
    Route::get('/onboarding', [StudentOnboardingController::class, 'show'])->name('onboarding.show');
    Route::post('/onboarding', [StudentOnboardingController::class, 'store'])->name('onboarding.store');

    Route::post('/join-class', [StudentEnrollmentController::class, 'joinClass'])->name('student.join-class');
    Route::delete('/leave-class/{courseSection}', [StudentEnrollmentController::class, 'leaveClass'])->name('student.leave-class');

    Route::view('/dashboard/student', 'student.dashboard')
        ->middleware('student_role')
        ->name('student.dashboard');

    Route::view('/dashboard/lecturer', 'lecturer.dashboard')
        ->middleware('lecturer_role')
        ->name('lecturer.dashboard');

    // Backward compatible fallback for existing links.
    Route::get('/dashboard', function () {
        /** @var User|null $user */
        $user = Auth::user();

        if ($user?->hasRole('lecturer')) {
            return redirect()->route('lecturer.dashboard');
        }

        if ($user?->hasRole('student')) {
            return redirect()->route('student.dashboard');
        }

        return redirect()->route('landing');
    })->name('dashboard');
});

require __DIR__.'/auth.php';
