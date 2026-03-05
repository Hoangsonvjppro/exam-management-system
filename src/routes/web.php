<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CourseSectionController;
use Illuminate\Support\Facades\Route;

// ─── Trang chủ → redirect thông minh ─────────────────────────────────────────
Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();
        if ($user->hasRole(['admin', 'department_admin'])) {
            return redirect()->route('admin.dashboard');
        }
        if ($user->hasRole(['lecturer', 'teaching_assistant'])) {
            return redirect()->route('lecturer.dashboard');
        }
        if ($user->hasRole('student')) {
            return redirect()->route('student.dashboard');
        }
    }
    return redirect()->route('login');
});

// ─── Fallback dashboard (cho Breeze compatibility) ────────────────────────────
Route::get('/dashboard', function () {
    return redirect('/');
})->middleware(['auth', 'active'])->name('dashboard');

// ─── Profile & File ──────────────────────────────────────────────────────────
Route::middleware(['auth', 'active'])->group(function () {
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // File Upload / Download / Delete
    Route::post('/files', [FileController::class, 'store'])->name('files.store');
    Route::get('/files/{file}', [FileController::class, 'show'])->name('files.show');
    Route::delete('/files/{file}', [FileController::class, 'destroy'])->name('files.destroy');
});

// ─── ADMIN Routes ─────────────────────────────────────────────────────────────
Route::prefix('admin')
    ->middleware(['auth', 'active', 'role:admin|department_admin'])
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('dashboard');

        Route::resource('course-sections', CourseSectionController::class)
            ->except('show');

        // P2: Semester, Subject, Chapter routes
        Route::resource('semesters', \App\Http\Controllers\SemesterController::class)->except('show');
        Route::resource('subjects', \App\Http\Controllers\SubjectController::class)->except('show');
        Route::resource('chapters', \App\Http\Controllers\ChapterController::class)->except('show');

        // P1 sẽ thêm: User CRUD routes tại đây (Tuần 2)
        // Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
    });

// ─── LECTURER Routes ──────────────────────────────────────────────────────────
Route::prefix('lecturer')
    ->middleware(['auth', 'active', 'role:lecturer|teaching_assistant'])
    ->name('lecturer.')
    ->group(function () {
        Route::get('/dashboard', function () {
            return view('lecturer.dashboard');
        })->name('dashboard');

        // P2: Semester, Subject, Chapter routes
        // P3: Course Section routes
        // P2: Question Bank routes
        // P2: Exam Paper routes
        // P5: Attendance routes
        // P5: Document routes
    });

// ─── STUDENT Routes ───────────────────────────────────────────────────────────
Route::prefix('student')
    ->middleware(['auth', 'active', 'role:student'])
    ->name('student.')
    ->group(function () {
        Route::get('/dashboard', function () {
            return view('student.dashboard');
        })->name('dashboard');

        // P3: Exam taking routes
        // P5: Attendance view routes
        // P5: Document download routes
    });

require __DIR__ . '/auth.php';
