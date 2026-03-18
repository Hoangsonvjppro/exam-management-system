<?php

/**
 * ============================================================
 *  ROUTES / WEB.PHP
 *  Định nghĩa toàn bộ các route cho ứng dụng web
 * ============================================================
 *
 *  Cấu trúc tổng quan:
 *  1. Public routes      – Trang chủ, đăng nhập Google
 *  2. Protected routes   – Yêu cầu đăng nhập & đổi mật khẩu
 *     2a. Onboarding     – Sinh viên mới hoàn thiện hồ sơ
 *     2b. Student        – Trang dành cho sinh viên
 *     2c. Lecturer       – Trang dành cho giảng viên
 *     2d. Dashboard      – Điều hướng chung theo vai trò
 * ============================================================
 */

use App\Http\Controllers\Auth\GoogleLoginController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\Lecturer\CourseSectionController as LecturerSectionController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\StudentEnrollmentController;
use App\Http\Controllers\StudentOnboardingController;
use App\Http\Controllers\Lecturer\ExamController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// ============================================================
// 1. PUBLIC ROUTES
//    Không yêu cầu đăng nhập
// ============================================================

// Trang chủ – middleware tự động chuyển hướng theo trạng thái user
// (chưa đăng nhập → landing, đã đăng nhập → dashboard tương ứng)
Route::get('/', LandingController::class)
    ->middleware('redirect_by_user_state')
    ->name('landing');

// Khởi tạo luồng đăng nhập bằng Google OAuth
Route::get('/auth/google', [GoogleLoginController::class, 'redirect'])
    ->name('google.redirect');

// Google OAuth callback – nhận token và tạo/đăng nhập tài khoản
Route::get('/auth/google/callback', [GoogleLoginController::class, 'callback'])
    ->name('google.callback');


// ============================================================
// 2. PROTECTED ROUTES
//    Yêu cầu:
//    - 'auth'                       : Đã đăng nhập
//    - 'must_change_password_handled': Đã xử lý bước đổi mật khẩu bắt buộc
// ============================================================

Route::middleware(['auth', 'must_change_password_handled'])->group(function () {

    // ----------------------------------------------------------
    // 2a. ONBOARDING – Sinh viên mới hoàn thiện thông tin lần đầu
    // ----------------------------------------------------------

    // Hiển thị form onboarding
    Route::get('/onboarding', [StudentOnboardingController::class, 'show'])
        ->name('onboarding.show');

    // Lưu thông tin onboarding
    Route::post('/onboarding', [StudentOnboardingController::class, 'store'])
        ->name('onboarding.store');


    // ----------------------------------------------------------
    // 2b. STUDENT ROUTES
    //     Middleware 'student_role' đảm bảo chỉ sinh viên truy cập được
    // ----------------------------------------------------------

    // Tham gia lớp học bằng mã code
    Route::post('/join-class', [StudentEnrollmentController::class, 'joinClass'])
        ->name('student.join-class');

    // Rời khỏi lớp học (courseSection = ID lớp cần rời)
    Route::delete('/leave-class/{courseSection}', [StudentEnrollmentController::class, 'leaveClass'])
        ->name('student.leave-class');

    // Dashboard của sinh viên
    Route::view('/dashboard/student', 'student.dashboard')
        ->middleware('student_role')
        ->name('student.dashboard');

    // Danh sách thông báo của sinh viên
    Route::get('/notifications', [NotificationController::class, 'index'])
        ->middleware('student_role')
        ->name('student.notifications.index');


    // ----------------------------------------------------------
    // 2c. LECTURER ROUTES
    //     Prefix  : /lecturer/...
    //     Name    : lecturer....
    //     Middleware 'lecturer_role' đảm bảo chỉ giảng viên truy cập được
    // ----------------------------------------------------------

    Route::middleware('lecturer_role')
        ->prefix('lecturer')
        ->name('lecturer.')
        ->group(function () {

            // Dashboard giảng viên (route mới, dùng closure)
            Route::get('/dashboard', fn () => view('lecturer.dashboard'))
                ->name('dashboard_redirect');

            // ── Quản lý lớp học (Course Sections) ──────────────────
            // Tự động tạo các route CRUD: index, create, store,
            // show, edit, update, destroy
            // URI param đổi tên thành {section} thay vì {class}
            Route::resource('classes', LecturerSectionController::class)
                ->parameters(['classes' => 'section']);

            // Tạo lại mã tham gia lớp học
            Route::post('/classes/{section}/regenerate-code', [LecturerSectionController::class, 'regenerateCode'])
                ->name('classes.regenerate-code');

            // Gửi thông báo đến sinh viên trong lớp
            Route::post('/classes/{section}/notifications', [NotificationController::class, 'store'])
                ->name('classes.notifications.store');


            // ── Quản lý Đề thi (Exams) ─────────────────────────────

            // Hiển thị form tạo đề thi mới cho một lớp học
            Route::get('/course-sections/{courseSection}/exams/create', [ExamController::class, 'create'])
                ->name('course-sections.exams.create');

            // Lưu thông tin chung của đề thi vừa tạo
            Route::post('/course-sections/{courseSection}/exams', [ExamController::class, 'store'])
                ->name('course-sections.exams.store');

            // Màn hình quản lý câu hỏi trong đề thi
            Route::get('/exams/{exam}/questions', [ExamController::class, 'manageQuestions'])
                ->name('exams.questions.manage');

            // Thêm / cập nhật danh sách câu hỏi của đề thi
            Route::post('/exams/{exam}/questions', [ExamController::class, 'storeQuestions'])
                ->name('exams.questions.store');
        });

    // Dashboard cũ của giảng viên – giữ lại để tương thích với
    // các đường dẫn cũ trong sidebar (không xoá)
    Route::view('/dashboard/lecturer', 'lecturer.dashboard')
        ->middleware('lecturer_role')
        ->name('lecturer.dashboard');


    // ----------------------------------------------------------
    // 2d. DASHBOARD CHUNG – Điều hướng theo vai trò
    //     /dashboard → tự động chuyển đến đúng dashboard
    // ----------------------------------------------------------

    Route::get('/dashboard', function () {
        /** @var User|null $user */
        $user = Auth::user();

        if ($user?->hasRole('lecturer')) {
            return redirect()->route('lecturer.dashboard');
        }

        if ($user?->hasRole('student')) {
            return redirect()->route('student.dashboard');
        }

        // Fallback: chưa có vai trò → về trang chủ
        return redirect()->route('landing');
    })->name('dashboard');

}); // end: protected routes


// ============================================================
// 3. AUTH ROUTES
//    Đăng nhập / đăng ký / đặt lại mật khẩu v.v.
//    (được định nghĩa trong file auth.php)
// ============================================================

require __DIR__ . '/auth.php';