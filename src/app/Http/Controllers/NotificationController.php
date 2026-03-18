<?php

namespace App\Http\Controllers;

use App\Models\CourseSection;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    /**
     * Display a listing of notifications for the currently authenticated student.
     */
    public function index()
    {
        $notifications = auth()->user()->notifications()->latest()->paginate(10);
        
        // Mark all unread as read when they visit the page? 
        // Or wait, the requirement says "khi ấn vào nút chi tiết mới hiện toàn bộ thông báo" 
        // We can just keep it simple and mark them read when viewing the page, 
        // Or mark them read via a dedicated endpoint. Let's mark them read on index for simplicity,
        // or just let them stay unread until a specific action if needed. 
        // If we want the red dot to disappear when they visit the page:
        auth()->user()->notifications()->unread()->update(['read_at' => now()]);

        return view('student.notifications.index', compact('notifications'));
    }

    /**
     * Store a newly created notification sent by a lecturer to a specific class.
     */
    public function store(Request $request, CourseSection $section)
    {
        // Add authorization check: only the lecturer of this section can send notifications
        if (auth()->id() !== (int) $section->lecturer_id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'title'   => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $students = $section->students;

        if ($students->isEmpty()) {
            return back()->with('error', 'Lớp học phần này chưa có sinh viên nào để gửi thông báo.');
        }

        $now = now();
        $notificationsToInsert = [];

        foreach ($students as $student) {
            $notificationsToInsert[] = [
                'id'         => (string) \Illuminate\Support\Str::uuid(),
                'user_id'    => $student->id,
                'type'       => 'course_announcement',
                'title'      => $validated['title'],
                'message'    => $validated['message'],
                'data'       => json_encode([
                    'course_section_id'   => $section->id,
                    'course_section_name' => $section->name ?? $section->code,
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Bulk insert for performance
        Notification::insert($notificationsToInsert);

        return back()->with('success', 'Đã gửi thông báo đến lớp học phần thành công.');
    }
}
