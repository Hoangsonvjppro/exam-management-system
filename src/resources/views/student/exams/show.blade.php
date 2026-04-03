<x-app-layout>
    @section('title', $exam->title)
    @section('page-title', 'Thông tin đề thi')

    @php
    $isPractice = $exam->isPractice();
    $questionCount = $exam->questions->count();
    $latestAttempt = $pastAttempts->first();
    $statusText = match ($schedule->runtime_status) {
    'scheduled' => 'Chưa bắt đầu',
    'in_progress' => 'Đang mở',
    'completed' => 'Đã kết thúc',
    'cancelled' => 'Đã hủy',
    default => 'Không xác định',
    };
    $statusClass = match ($schedule->runtime_status) {
    'scheduled' => 'bg-blue-50 text-blue-700 border-blue-200',
    'in_progress' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
    'completed' => 'bg-slate-100 text-slate-700 border-slate-200',
    'cancelled' => 'bg-red-50 text-red-700 border-red-200',
    default => 'bg-slate-100 text-slate-700 border-slate-200',
    };
    @endphp

    <div class="space-y-6">
        <x-card padding="true" variant="featured" class="overflow-hidden">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">
                <div class="lg:col-span-2 space-y-5">
                    <div class="space-y-3">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 border rounded-full text-xs font-semibold {{ $isPractice ? 'bg-navy-50 text-navy-600 border-blue-200' : 'bg-amber-50 text-amber-700 border-amber-200' }}">
                                <x-ui-icon :name="$isPractice ? 'star' : 'document-text'" class="w-3.5 h-3.5" />
                                {{ $isPractice ? 'Đề luyện tập' : 'Đề chính thức' }}
                            </span>
                            <span class="inline-flex items-center px-3 py-1 border rounded-full text-xs font-semibold {{ $statusClass }}">
                                {{ $statusText }}
                            </span>
                        </div>

                        <h2 class="text-2xl md:text-3xl font-bold text-navy-900 leading-tight">{{ $exam->title }}</h2>

                        @if(filled($exam->description))
                        <p class="text-sm md:text-base text-text-muted leading-relaxed">{{ $exam->description }}</p>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <div class="rounded-xl border border-border-clean bg-surface-0 p-4">
                            <p class="text-xs text-text-muted mb-1">Thời lượng</p>
                            <p class="text-lg font-bold text-navy-900">
                                @if($exam->duration_minutes >= 60)
                                {{ floor($exam->duration_minutes / 60) }} giờ {{ $exam->duration_minutes % 60 }} phút
                                @else
                                {{ $exam->duration_minutes }} phút
                                @endif
                            </p>
                        </div>

                        <div class="rounded-xl border border-border-clean bg-surface-0 p-4">
                            <p class="text-xs text-text-muted mb-1">Số câu hỏi</p>
                            <p class="text-lg font-bold text-navy-900">{{ $questionCount }} câu</p>
                        </div>

                        <div class="rounded-xl border border-border-clean bg-surface-0 p-4">
                            <p class="text-xs text-text-muted mb-1">Mở đề</p>
                            <p class="text-sm font-bold text-navy-900">{{ $schedule->start_datetime->format('H:i - d/m/Y') }}</p>
                        </div>

                        <div class="rounded-xl border border-border-clean bg-surface-0 p-4">
                            <p class="text-xs text-text-muted mb-1">Đóng đề</p>
                            <p class="text-sm font-bold text-navy-900">{{ $schedule->end_datetime->format('H:i - d/m/Y') }}</p>
                        </div>
                    </div>

                    <div class="rounded-xl border border-blue-100 bg-blue-50/60 p-4">
                        <p class="text-sm font-semibold text-navy-900 mb-3">Thông tin trước khi vào thi</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                            <p class="text-text-muted"><span class="font-semibold text-navy-900">Lớp học phần:</span> {{ $schedule->courseSection->name ?? 'Chưa cập nhật' }}</p>
                            <p class="text-text-muted"><span class="font-semibold text-navy-900">Mã lớp:</span> {{ $schedule->courseSection->code ?? 'Chưa cập nhật' }}</p>
                            <p class="text-text-muted"><span class="font-semibold text-navy-900">Khung giờ thi:</span> {{ $schedule->time_range_text }}</p>
                            <p class="text-text-muted"><span class="font-semibold text-navy-900">Ngày thi:</span> {{ $schedule->date_range_text }}</p>
                            <p class="text-text-muted"><span class="font-semibold text-navy-900">Điểm đạt:</span>
                                @if($exam->pass_points !== null)
                                {{ number_format((float) $exam->pass_points, 1) }} / 10
                                @else
                                Chưa cấu hình
                                @endif
                            </p>
                            <p class="text-text-muted"><span class="font-semibold text-navy-900">Số bài đã nộp:</span> {{ $pastAttempts->count() }}</p>
                        </div>
                    </div>

                    <div class="px-4 py-3 bg-red-50 border border-red-100 rounded-xl flex items-start gap-3 shadow-sm">
                        <div class="p-1.5 bg-red-100 rounded-lg text-red-600 shrink-0">
                            <x-ui-icon name="wifi" class="w-5 h-5 text-red-600" />
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-red-900 mb-0.5">Lưu ý quan trọng</h4>
                            <p class="text-xs text-red-800 leading-relaxed">
                                Đảm bảo kết nối internet và thiết bị ổn định. Hệ thống tính giờ theo máy chủ, sự cố từ thiết bị hoặc mạng cá nhân có thể ảnh hưởng trực tiếp đến kết quả nộp bài.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-1">
                    <div class="rounded-2xl border border-border-clean bg-surface-0 p-5 lg:sticky lg:top-24 space-y-4">
                        <h3 class="text-base font-bold text-navy-900">Sẵn sàng vào làm bài</h3>

                        @if($schedule->runtime_status === 'scheduled')
                        <p class="text-xs text-blue-700 bg-blue-50 border border-blue-100 rounded-lg px-3 py-2">
                            Bài thi chưa mở. Vui lòng quay lại khi đến giờ bắt đầu.
                        </p>
                        @elseif($schedule->runtime_status === 'in_progress')
                        <p class="text-xs text-emerald-700 bg-emerald-50 border border-emerald-100 rounded-lg px-3 py-2">
                            Ca thi đang mở. {{ $schedule->time_left_text }} để tham gia.
                        </p>
                        @else
                        <p class="text-xs text-slate-700 bg-slate-100 border border-slate-200 rounded-lg px-3 py-2">
                            Ca thi đã đóng. Bạn chỉ có thể xem lại kết quả (nếu có).
                        </p>
                        @endif

                        <div class="space-y-3">
                            @if($inProgressAttempt)
                            <p class="text-sm font-medium text-amber-700 flex items-center gap-1.5">
                                <x-ui-icon name="exclamation-triangle" class="w-4 h-4" />
                                Bạn đang có bài thi chưa hoàn thành{{ $isPractice ? ' (Lần ' . $inProgressAttempt->attempt_number . ')' : '' }}.
                            </p>

                            <a href="{{ route('student.exams.room', $schedule->id) }}" class="w-full inline-flex justify-center items-center gap-2 px-5 py-3 bg-amber-500 border border-transparent rounded-[8px] font-semibold text-sm text-white tracking-wide hover:bg-amber-600 transition shadow-sm">
                                <x-ui-icon name="play" class="w-4 h-4" />
                                Tiếp tục làm bài
                            </a>
                            @elseif($canStartNew)
                            <div x-data="simpleLoadingState()" class="w-full">
                                <form action="{{ route('student.exams.start', $schedule->id) }}"
                                    method="POST"
                                    @submit="loading = true">
                                    @csrf
                                    <button type="submit"
                                        :disabled="loading"
                                        :class="{ 'opacity-70 cursor-not-allowed': loading }"
                                        class="w-full inline-flex justify-center items-center gap-2 px-5 py-3 bg-navy-900 border border-transparent rounded-[8px] font-semibold text-sm text-white tracking-wide hover:bg-navy-950 transition shadow-sm">

                                        <template x-if="loading">
                                            <svg class="animate-spin -ml-1 mr-1 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </template>

                                        <template x-if="!loading">
                                            <x-ui-icon :name="$isPractice && $pastAttempts->isNotEmpty() ? 'arrow-path' : 'play'" class="w-4 h-4" />
                                        </template>

                                        <span x-text="loading ? 'Đang khởi tạo...' : '{{ $isPractice && $pastAttempts->isNotEmpty() ? 'Thi lại' : 'Bắt đầu làm bài' }}'">
                                            {{ $isPractice && $pastAttempts->isNotEmpty() ? 'Thi lại' : 'Bắt đầu làm bài' }}
                                        </span>
                                    </button>
                                </form>
                            </div>
                            @else
                            <p class="text-teal-600 font-bold text-sm flex items-center gap-1.5">
                                <x-ui-icon name="check-circle" class="w-5 h-5" />
                                Bạn đã hoàn thành bài thi này.
                            </p>
                            @endif

                            <a href="{{ route('student.dashboard') }}" class="inline-flex items-center justify-center w-full text-sm text-blue-600 hover:underline">
                                Về trang chủ
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </x-card>

        {{-- Lịch sử làm bài --}}
        @if($pastAttempts->isNotEmpty())
        <x-card padding="true">
            <x-slot name="header">
                <h3 class="text-lg font-semibold text-navy-900">Lịch sử làm bài của bạn</h3>
            </x-slot>

            {{-- Table View (Desktop) --}}
            <div class="hidden md:block overflow-x-auto mt-4">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border-clean">
                            @if($isPractice)
                            <th class="text-center py-2 px-3 font-semibold text-text-muted">Lần thi</th>
                            @endif
                            <th class="text-center py-2 px-3 font-semibold text-text-muted">Điểm (hệ 10)</th>
                            <th class="text-center py-2 px-3 font-semibold text-text-muted">Nộp bài lúc</th>
                            <th class="text-center py-2 px-3 font-semibold text-text-muted"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pastAttempts as $past)
                        <tr class="border-b border-border-clean hover:bg-surface-0 transition-colors">
                            @if($isPractice)
                            <td class="py-3 px-3 text-center font-medium text-navy-900">Lần {{ $past->attempt_number }}</td>
                            @endif
                            <td class="py-3 px-3 text-center font-bold text-navy-900">
                                {{ number_format($past->total_score, 1) }}/10
                            </td>
                            <td class="py-3 px-3 text-center text-text-muted">{{ $past->completed_at?->format('H:i - d/m/Y') }}</td>
                            <td class="py-3 px-3 text-center">
                                @if($latestAttempt && $past->attempt_number === $latestAttempt->attempt_number)
                                {{-- Chỉ cho xem kết quả chi tiết của lần thi MỚI NHẤT (tránh nhầm lẫn do updateOrCreate answers ko lưu version attempt lịch sử) 
                                         * Note: Do cấu trúc DB StudentAnswer đang overwrite nếu user thi lại * --}}
                                <a href="{{ route('student.exams.result', $schedule->id) }}" class="text-blue-600 hover:underline text-xs font-medium">Chi tiết</a>
                                @else
                                <span class="text-blue-200 text-xs italic">Lưu trữ</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Card View (Mobile) --}}
            <div class="md:hidden space-y-4 mt-4">
                @foreach($pastAttempts as $past)
                <div class="bg-surface-0 rounded-xl border border-border-clean p-4 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            @if($isPractice)
                            <span class="block text-xs font-medium text-text-muted mb-0.5 italic">Lần thi</span>
                            <span class="text-sm font-bold text-navy-900">Lần {{ $past->attempt_number }}</span>
                            @else
                            <span class="block text-xs font-medium text-text-muted mb-0.5 italic">Bài thi chính thức</span>
                            <span class="text-sm font-bold text-navy-900">Kết quả bài làm</span>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 py-3 border-y border-dashed border-border-clean">
                        <div>
                            <span class="block text-[10px] font-medium text-text-muted mb-1 uppercase tracking-wider">Điểm số</span>
                            <span class="text-xl font-black text-navy-900">
                                {{ number_format($past->total_score, 1) }}/10
                            </span>
                        </div>
                        <div>
                            <span class="block text-[10px] font-medium text-text-muted mb-1 uppercase tracking-wider">Hoàn thành lúc</span>
                            <span class="text-xs text-navy-900 font-bold block">{{ $past->completed_at?->format('H:i') }}</span>
                            <span class="text-[10px] text-text-muted">{{ $past->completed_at?->format('d/m/Y') }}</span>
                        </div>
                    </div>

                    <div class="mt-3 flex justify-between items-center">
                        <span class="text-[10px] text-text-muted">Chi tiết bài làm</span>
                        @if($latestAttempt && $past->attempt_number === $latestAttempt->attempt_number)
                        <a href="{{ route('student.exams.result', $schedule->id) }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-50 text-blue-700 rounded-lg font-bold text-xs hover:bg-blue-100 transition">
                            Xem kết quả
                            <x-ui-icon name="arrow-right" class="w-3 h-3" />
                        </a>
                        @else
                        <span class="text-blue-300 text-[10px] italic font-medium px-2 py-1 bg-surface-50 rounded">Lưu trữ hệ thống</span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>

            @if($exam->isPractice())
            <p class="text-xs text-text-muted mt-4 italic">Lưu ý: Bạn có thể thi lại nhiều lần. Kết quả chi tiết (đáp án đúng/sai) chỉ hiển thị cho lần làm bài gần nhất của bạn.</p>
            @endif
        </x-card>
        @endif

    </div>
</x-app-layout>