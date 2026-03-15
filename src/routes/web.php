<?php

use App\Http\Controllers\Auth\GoogleLoginController;
use App\Http\Controllers\StudentEnrollmentController;
use App\Http\Controllers\StudentOnboardingController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/auth/google', [GoogleLoginController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [GoogleLoginController::class, 'callback'])->name('google.callback');

Route::middleware('auth')->group(function () {
    Route::get('/onboarding', [StudentOnboardingController::class, 'show'])->name('onboarding.show');
    Route::post('/onboarding', [StudentOnboardingController::class, 'store'])->name('onboarding.store');
});

Route::middleware(['auth', 'ensure_student_code'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::post('/join-class', [StudentEnrollmentController::class, 'joinClass'])->name('student.join-class');
});

require __DIR__.'/auth.php';
