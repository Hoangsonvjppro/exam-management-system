<x-app-layout>
    @section('title', 'Tổng quan — Sinh viên')
    @section('page-title', 'Tổng quan học tập')

    <div class="space-y-6">

        {{-- Hero Section --}}
        <x-card padding="true" variant="featured">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <h2 class="text-2xl md:text-3xl font-bold text-navy-900 leading-tight">Xin chào, {{ auth()->user()->name }}!</h2>
                    <p class="mt-2 text-sm text-text-muted">
                        @if (auth()->user()->student_code)
                        Mã sinh viên: <span class="font-semibold text-navy-900">{{ auth()->user()->student_code }}</span>
                        @if(auth()->user()->class_name)
                        — Lớp: <span class="font-semibold text-navy-900">{{ auth()->user()->class_name }}</span>
                        @endif
                        @else
                        Bạn chưa cập nhật thông tin sinh viên.
                        <a href="{{ route('onboarding.show') }}" class="text-blue-400 font-medium hover:underline">Hoàn tất hồ sơ ngay</a>
                        @endif
                    </p>
                </div>

                <div class="flex items-center gap-4 text-sm">
                    <div class="flex items-center gap-2 px-3 py-2 bg-white border border-border-clean rounded-lg">
                        <svg class="w-4 h-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" /></svg>
                        <span class="font-bold text-navy-900">{{ $enrolledSections->count() }}</span>
                        <span class="text-text-muted">lớp</span>
                    </div>
                    <div class="flex items-center gap-2 px-3 py-2 bg-white border border-border-clean rounded-lg">
                        <svg class="w-4 h-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
                        <span class="font-bold text-navy-900">{{ $upcomingExams->count() }}</span>
                        <span class="text-text-muted">bài thi sắp tới</span>
                    </div>
                </div>
            </div>
        </x-card>

        {{-- ═══ URGENCY: Kỳ thi sắp tới ═══ --}}
        <x-card padding="true">
            <x-slot name="header">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <h3 class="text-lg font-semibold text-navy-900">Kỳ thi sắp tới</h3>
                </div>
            </x-slot>

            @if($upcomingExams->isEmpty())
            <div class="text-center py-10 bg-surface-0 border-[0.5px] border-border-clean border-dashed rounded-[8px]">
                <x-ui-icon name="check-circle" class="w-10 h-10 text-teal-300 mx-auto mb-3" />
                <p class="text-text-muted text-sm font-medium">Không có kỳ thi nào sắp tới. Yên tâm!</p>
            </div>
            @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($upcomingExams as $schedule)
                @php
                    $now = now();
                    $examDateStr = $schedule->exam_date->format('Y-m-d');
                    $startDt = \Carbon\Carbon::parse($examDateStr . ' ' . $schedule->start_time);
                    $endDt = \Carbon\Carbon::parse($examDateStr . ' ' . $schedule->end_time);

                    $isOpen = $now->between($startDt, $endDt);
                    $isUpcoming = $now->lt($startDt);
                    $hoursUntil = $now->diffInHours($startDt, false);
                    $isUrgent = $isUpcoming && $hoursUntil >= 0 && $hoursUntil <= 24;
                    $isCompleted = $schedule->isCompletedBy(auth()->id());
                @endphp
                <div class="border-[0.5px] rounded-[10px] p-5 bg-white flex flex-col justify-between transition-all hover:shadow-md
                    {{ $isUrgent && !$isCompleted ? 'border-red-300 bg-red-50/30' : ($isOpen && !$isCompleted ? 'border-teal-300 bg-teal-50/20' : 'border-border-clean') }}">
                    <div class="mb-4">
                        <div class="flex items-center justify-between mb-2">
                            @if($isCompleted)
                            <span class="inline-flex items-center gap-1 text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-teal-50 text-teal-700 border border-teal-200">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                Đã nộp
                            </span>
                            @elseif($isOpen)
                            <span class="inline-flex items-center gap-1 text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-teal-50 text-teal-700 border border-teal-200 animate-pulse">
                                <span class="w-1.5 h-1.5 rounded-full bg-teal-500"></span>
                                Đang mở
                            </span>
                            @elseif($isUrgent)
                            <span class="inline-flex items-center gap-1 text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-red-50 text-red-700 border border-red-200">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                                Sắp tới
                            </span>
                            @else
                            <span class="inline-flex items-center text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200">
                                Sắp tới
                            </span>
                            @endif
                            <span class="text-[11px] text-text-muted font-semibold">{{ $schedule->exam_date->format('d/m/Y') }}</span>
                        </div>
                        <h4 class="font-bold text-base text-navy-900 leading-snug mb-1">{{ $schedule->exam->title }}</h4>
                        <p class="text-xs text-text-muted">{{ $schedule->courseSection->name ?? '—' }}</p>
                        <div class="flex items-center gap-3 mt-2 text-[11px] text-text-muted">
                            <span class="font-semibold">{{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} — {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}</span>
                            <span>·</span>
                            <span>{{ $schedule->exam->duration_minutes }} phút</span>
                        </div>
                    </div>

                    @if($isOpen && !$isCompleted)
                    <a href="{{ route('student.exams.show', $schedule->id) }}"
                       class="inline-flex items-center justify-center gap-2 w-full px-4 py-2.5 bg-navy-900 text-white text-sm font-semibold rounded-[6px] hover:bg-navy-950 transition-colors shadow-sm">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Vào phòng thi
                    </a>
                    @elseif($isCompleted)
                    <a href="{{ route('student.exams.result', $schedule->id) }}"
                       class="inline-flex items-center justify-center gap-2 w-full px-4 py-2 bg-surface-1 text-navy-900 text-sm font-semibold rounded-[6px] hover:bg-blue-50 border border-border-clean transition-colors">
                        Xem kết quả
                    </a>
                    @else
                    <div class="text-center text-xs text-text-muted font-medium py-2 bg-surface-0 rounded-[6px] border border-border-clean border-dashed">
                        Chưa đến giờ thi
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
            @endif
        </x-card>

        {{-- ═══ Thông báo mới nhất ═══ --}}
        <x-card padding="true">
            <x-slot name="header">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                        <h3 class="text-lg font-semibold text-navy-900">Thông báo mới nhất</h3>
                    </div>
                    <a href="{{ route('student.notifications.index') }}" class="text-xs font-bold text-blue-600 hover:text-navy-900 uppercase tracking-wider">Xem tất cả →</a>
                </div>
            </x-slot>

            @if($recentNotifications->isEmpty())
            <div class="text-center py-8 bg-surface-0 border-[0.5px] border-border-clean border-dashed rounded-[8px]">
                <p class="text-text-muted text-sm font-medium">Chưa có thông báo nào.</p>
            </div>
            @else
            <div class="divide-y-[0.5px] divide-border-clean">
                @foreach($recentNotifications as $notification)
                @php
                    $data = is_array($notification->data) ? $notification->data : json_decode($notification->data, true);
                    $className = $data['course_section_name'] ?? 'Hệ thống';
                @endphp
                <div class="py-3.5 flex items-start gap-3">
                    <div class="w-2 h-2 rounded-full bg-blue-400 mt-2 flex-shrink-0"></div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="inline-block px-1.5 py-0.5 bg-blue-50 text-blue-700 border-[0.5px] border-blue-200 rounded text-[9px] font-bold uppercase tracking-wider">{{ $className }}</span>
                            <span class="text-[10px] text-text-muted">{{ $notification->created_at->diffForHumans() }}</span>
                        </div>
                        <h4 class="text-sm font-semibold text-navy-900 truncate">{{ $notification->title }}</h4>
                        <p class="text-xs text-text-muted line-clamp-1 mt-0.5">{{ $notification->message }}</p>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </x-card>

    </div>
</x-app-layout>