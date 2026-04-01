<?php

namespace App\Http\Controllers;

use App\Http\Requests\Notification\StoreNotificationRequest;
use App\Models\CourseSection;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

class NotificationController extends Controller
{
    public function __construct(private readonly NotificationService $notificationService) {}

    /**
     * Display a listing of notifications for the currently authenticated student.
     */
    public function index(): View
    {
        /** @var User $user */
        $user = request()->user();

        // $notifications = $user->notifications()->latest()->paginate(10);
        $notifications = $user->userNotifications()->latest()->paginate(10);

        // Đánh dấu tất cả thông báo chưa đọc thành đã đọc khi họ truy cập trang?
        // Hoặc chờ đã, yêu cầu nói là "khi ấn vào nút chi tiết mới hiện toàn bộ thông báo"
        // Chúng ta có thể làm đơn giản bằng cách đánh dấu đã đọc khi xem trang,
        // Hoặc đánh dấu đã đọc qua một endpoint riêng. Hãy đánh dấu đã đọc tại hàm index cho đơn giản,
        // hoặc cứ để chúng ở trạng thái chưa đọc cho đến khi có hành động cụ thể nếu cần.
        // Nếu chúng ta muốn dấu chấm đỏ biến mất khi họ truy cập trang:

        // $user->notifications()->unread()->update(['read_at' => now()]);
        $user->userNotifications()->unread()->update(['read_at' => now()]);

        return view('student.notifications.index', compact('notifications'));
    }

    /**
     * Store a newly created notification sent by a lecturer to a specific class.
     */
    public function store(StoreNotificationRequest $request, CourseSection $section)
    {
        Gate::authorize('sendNotification', $section);

        try {
            $this->notificationService->sendToSection($section, $request->validated());
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Đã gửi thông báo đến lớp học phần thành công.');
    }
}
