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
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\Lecturer\CourseSectionController as LecturerSectionController;
use App\Http\Controllers\Lecturer\LecturerPageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Student\StudentPageController;
use App\Http\Controllers\StudentEnrollmentController;
use App\Http\Controllers\Student\StudentDashboardController;
use App\Http\Controllers\StudentOnboardingController;
use App\Http\Controllers\Lecturer\ExamController;
use App\Http\Controllers\Lecturer\ExamFormApiController;
use App\Http\Controllers\Lecturer\ExamScheduleController;
use App\Http\Controllers\DifficultyController;
use App\Http\Controllers\QuestionTypeController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuestionController;

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

    // Xem ảnh minh chứng xin nghỉ phép qua app route
    Route::get('/leave-requests/{leaveRequest}/proof-image', [\App\Http\Controllers\LeaveRequestProofImageController::class, 'show'])
        ->whereNumber('leaveRequest')
        ->name('leave-requests.proof-image');


    // ----------------------------------------------------------
    // 2b. STUDENT ROUTES
    //     Middleware 'student_role' đảm bảo chỉ sinh viên truy cập được
    // ----------------------------------------------------------

    // Tham gia lớp học bằng mã code
    Route::post('/join-class', [StudentEnrollmentController::class, 'joinClass'])
        ->name('student.join-class');

    // Tham gia lớp học bằng QR code (tự động)
    Route::get('/join-class/qr', [StudentEnrollmentController::class, 'joinClassByQr'])
        ->name('student.join-class.qr');

    // Rời khỏi lớp học (courseSection = ID lớp cần rời)
    Route::delete('/leave-class/{courseSection}', [StudentEnrollmentController::class, 'leaveClass'])
        ->name('student.leave-class');

    // Dashboard của sinh viên – truyền data từ controller (High #9, #10)
    Route::get('/dashboard/student', StudentDashboardController::class)
        ->middleware('student_role')
        ->name('student.dashboard');

    // Danh sách thông báo của sinh viên
    Route::get('/notifications', [NotificationController::class, 'index'])
        ->middleware('student_role')
        ->name('student.notifications.index');

    // Student Exam routes
    Route::middleware('student_role')->prefix('student')->name('student.')->group(function () {
        // Danh sách lớp học phần của sinh viên
        Route::get('/classes', [StudentPageController::class, 'classes'])->name('classes.index');

        // Chi tiết lớp học phần (Class Workspace)
        Route::get('/classes/{section}', [StudentPageController::class, 'classShow'])->name('classes.show');

        // Điểm danh (QR Code) & Xin phép
        Route::post('/classes/{section}/attendance', [\App\Http\Controllers\Student\AttendanceController::class, 'checkIn'])->name('classes.attendance.checkin');
        Route::post('/classes/{section}/leave-requests', [\App\Http\Controllers\Student\LeaveRequestController::class, 'store'])->name('classes.leave-requests.store');

        // Khiếu nại
        Route::get('/complaints', [StudentPageController::class, 'complaints'])->name('complaints.index');
        Route::post('/complaints', [\App\Http\Controllers\Student\ComplaintController::class, 'store'])->name('complaints.store');

        Route::get('/schedules/{schedule}', [\App\Http\Controllers\Student\ExamController::class, 'show'])->name('exams.show');
        Route::post('/schedules/{schedule}/start', [\App\Http\Controllers\Student\ExamController::class, 'start'])->name('exams.start');
        Route::get('/schedules/{schedule}/room', [\App\Http\Controllers\Student\ExamController::class, 'room'])->name('exams.room');
        Route::post('/schedules/{schedule}/save-answer', [\App\Http\Controllers\Student\ExamController::class, 'saveAnswer'])->name('exams.save-answer');
        Route::post('/schedules/{schedule}/submit', [\App\Http\Controllers\Student\ExamController::class, 'submit'])->name('exams.submit');
        Route::get('/schedules/{schedule}/result', [\App\Http\Controllers\Student\ExamController::class, 'result'])->name('exams.result');
        // Routes cho Sinh viên (đã bỏ prefix và name trùng lặp)
        Route::get('/exams', [\App\Http\Controllers\Student\ExamController::class, 'index'])->name('exams.index');

        Route::get('/results', [StudentPageController::class, 'results'])->name('results.index');
        Route::get('/results/{section}/score-slip', [StudentPageController::class, 'exportScoreSlip'])->name('results.score-slip');

        Route::get('/attendance', [StudentPageController::class, 'attendance'])->name('attendance.index');

        // Lịch thi của sinh viên
        Route::get('/schedules', [\App\Http\Controllers\Student\StudentScheduleController::class, 'index'])->name('schedules.index');
    });




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
            Route::get('/dashboard', [LecturerPageController::class, 'dashboard'])
                ->name('dashboard_redirect');

            // ── Quản lý Khiếu nại (Complaints) ─────────────────────
            Route::get('/complaints', [\App\Http\Controllers\Lecturer\ComplaintController::class, 'index'])
                ->name('complaints.index');
            Route::put('/complaints/{complaint}', [\App\Http\Controllers\Lecturer\ComplaintController::class, 'update'])
                ->name('complaints.update');

            // ── Quản lý lớp học (Course Sections) ──────────────────
            // Tự động tạo các route CRUD: index, create, store,
            // show, edit, update, destroy
            // URI param đổi tên thành {section} thay vì {class}
            Route::resource('classes', LecturerSectionController::class)
                ->parameters(['classes' => 'section']);

            // Nhóm route Điểm danh (Attendance Sessions) & Nghỉ phép
            Route::post('/classes/{section}/attendance-sessions', [\App\Http\Controllers\Lecturer\AttendanceSessionController::class, 'store'])
                ->name('classes.attendance.store');
            Route::patch('/classes/{section}/attendance-sessions/{session}/records/{record}', [\App\Http\Controllers\Lecturer\AttendanceSessionController::class, 'updateRecord'])
                ->name('classes.attendance.updateRecord');
            Route::patch('/classes/{section}/attendance-sessions/{session}/toggle-open', [\App\Http\Controllers\Lecturer\AttendanceSessionController::class, 'toggleOpen'])
                ->name('classes.attendance.toggleOpen');
            Route::patch('/classes/{section}/leave-requests/{leaveRequest}', [\App\Http\Controllers\Lecturer\LeaveRequestController::class, 'update'])
                ->name('classes.leave-requests.update');

            // Quản lý điểm (Grade Management)
            Route::post('/classes/{section}/grade-columns', [\App\Http\Controllers\Lecturer\GradeManagerController::class, 'storeColumn'])
                ->name('classes.grade-columns.store');
            Route::put('/classes/{section}/grade-columns/{column}', [\App\Http\Controllers\Lecturer\GradeManagerController::class, 'updateColumn'])
                ->name('classes.grade-columns.update');
            Route::delete('/classes/{section}/grade-columns/{column}', [\App\Http\Controllers\Lecturer\GradeManagerController::class, 'destroyColumn'])
                ->name('classes.grade-columns.destroy');
            Route::post('/classes/{section}/grade-columns/{column}/grades', [\App\Http\Controllers\Lecturer\GradeManagerController::class, 'saveGrades'])
                ->name('classes.grades.save');
            Route::get('/classes/{section}/grades/export', [\App\Http\Controllers\Lecturer\GradeManagerController::class, 'export'])
                ->name('classes.grades.export');

            // Tạo lại mã tham gia lớp học
            Route::post('/classes/{section}/regenerate-code', [LecturerSectionController::class, 'regenerateCode'])
                ->name('classes.regenerate-code');

            // Gửi thông báo đến sinh viên trong lớp
            Route::post('/classes/{section}/notifications', [NotificationController::class, 'store'])
                ->name('classes.notifications.store');


            // ── Quản lý Đề thi (Exams) ─────────────────────────────

            // Ngân hàng câu hỏi
            Route::get('/questions', [QuestionController::class, 'index'])
                ->name('questions.index');
            Route::get('/questions/export', [QuestionController::class, 'export'])
                ->name('questions.export');
            Route::get('/api/questions/add/{subjectId}', [QuestionController::class, 'getChaptersBySubject'])
                ->name('questions.api.chapters');
            Route::get('/questions/create', [QuestionController::class, 'create'])
                ->name('questions.create');
            Route::post('/questions', [QuestionController::class, 'store'])
                ->name('questions.store');
            Route::get('/questions/{question}/edit', [QuestionController::class, 'edit'])
                ->name('questions.edit');
            Route::put('/questions/{question}', [QuestionController::class, 'update'])
                ->name('questions.update');
            Route::delete('/questions/{question}', [QuestionController::class, 'destroy'])
                ->name('questions.destroy');
            Route::prefix('/questions/meta')->name('questions.meta.')->group(function () {
                Route::get('/difficulties', [DifficultyController::class, 'index'])->name('difficulties.index');
                Route::get('/types', [QuestionTypeController::class, 'index'])->name('types.index');
                Route::get('/tags', [TagController::class, 'index'])->name('tags.index');
            });

            // Hiển thị form tạo đề thi mới
            Route::get('/exams/create', [ExamController::class, 'create'])
                ->name('exams.create');

            // Hiển thị form tạo đề thi mới cho một lớp học
            Route::get('/course-sections/{courseSection}/exams/create', [ExamController::class, 'create'])
                ->name('course-sections.exams.create');

            // Lưu thông tin chung của đề thi vừa tạo
            Route::post('/exams', [ExamController::class, 'store'])
                ->name('exams.store');

            // CRUD & Lifecycle cho Exams
            Route::get('/exams/{exam}', [ExamController::class, 'show'])->name('exams.show');
            Route::get('/exams/{exam}/edit', [ExamController::class, 'edit'])->name('exams.edit');
            Route::get('/exams/{exam}/quick-preview', [ExamController::class, 'quickPreview'])->name('exams.quick-preview');
            Route::patch('/exams/{exam}/quick-update', [ExamController::class, 'quickUpdate'])->name('exams.quick-update');
            Route::put('/exams/{exam}', [ExamController::class, 'update'])->name('exams.update');
            Route::delete('/exams/{exam}', [ExamController::class, 'destroy'])->name('exams.destroy');
            Route::patch('/exams/{exam}/publish', [ExamController::class, 'publish'])->name('exams.publish');
            Route::patch('/exams/{exam}/close', [ExamController::class, 'close'])->name('exams.close');
            Route::patch('/exams/{exam}/reopen', [ExamController::class, 'reopen'])->name('exams.reopen');

            Route::get('/exams', [ExamController::class, 'index'])->name('exams.index');

            // ── Lịch thi (Exam Schedules) ─────────────────────────
            Route::get('/schedules', [ExamScheduleController::class, 'index'])->name('schedules.index');
            Route::get('/schedules/{schedule}/monitor', [ExamScheduleController::class, 'monitor'])->name('schedules.monitor');
            Route::get('/schedules/create', [ExamScheduleController::class, 'create'])->name('schedules.create');
            Route::post('/schedules', [ExamScheduleController::class, 'store'])->name('schedules.store');
            Route::get('/schedules/{schedule}/edit', [ExamScheduleController::class, 'edit'])->name('schedules.edit');
            Route::put('/schedules/{schedule}', [ExamScheduleController::class, 'update'])->name('schedules.update');
            Route::patch('/schedules/{schedule}/cancel', [ExamScheduleController::class, 'cancel'])->name('schedules.cancel');
            Route::delete('/schedules/{schedule}', [ExamScheduleController::class, 'destroy'])->name('schedules.destroy');
            Route::post('/schedules/{schedule}/assign-students', [ExamScheduleController::class, 'assignStudents'])->name('schedules.assign-students');
            Route::get('/schedules/{schedule}/students', [ExamScheduleController::class, 'getStudents'])->name('schedules.students');

            Route::get('/attendance', [LecturerPageController::class, 'attendance'])->name('attendance.index');

            // ── API AJAX cho form tạo đề thi ────────────────────────
            Route::prefix('api/exam-form')->name('api.exam-form.')->group(function () {
                Route::get('/questions', [ExamFormApiController::class, 'questions'])->name('questions');
                Route::get('/availability', [ExamFormApiController::class, 'availability'])->name('availability');
                Route::post('/quick-question', [ExamFormApiController::class, 'quickQuestion'])->name('quick-question');
            });
        });

    // Dashboard cũ của giảng viên – giữ lại để tương thích với
    // các đường dẫn cũ trong sidebar (không xoá)
    Route::get('/dashboard/lecturer', [LecturerPageController::class, 'dashboard'])
        ->middleware('lecturer_role')
        ->name('lecturer.dashboard');


    // ----------------------------------------------------------
    // 2d. DASHBOARD CHUNG – Điều hướng theo vai trò
    //     /dashboard → tự động chuyển đến đúng dashboard
    // ----------------------------------------------------------

    Route::get('/dashboard', DashboardController::class)->name('dashboard');
}); // end: protected routes


// ============================================================
// 3. AUTH ROUTES
//    Đăng nhập / đăng ký / đặt lại mật khẩu v.v.
//    (được định nghĩa trong file auth.php)
// ============================================================

require __DIR__ . '/auth.php';
