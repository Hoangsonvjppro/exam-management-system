<x-app-layout>
    @section('title', ($section->name ?? $section->code) . ' — EMS')
    @section('page-title', 'Chi tiết lớp học phần')

    <div class="space-y-6">

        <div class="flex items-center justify-between">
            <a href="{{ route('lecturer.classes.index') }}"
                class="inline-flex items-center gap-1.5 text-[13px] font-medium text-text-muted hover:text-navy-900 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Danh sách lớp
            </a>
            <div class="flex items-center gap-3">
                @can('manage', $section)
                <x-button variant="outline" onclick="document.getElementById('create-notification-modal').classList.remove('hidden')">
                    Tạo thông báo
                </x-button>
                <x-button variant="secondary" href="{{ route('lecturer.exams.create') }}">
                    Tạo bài kiểm tra
                </x-button>
                <x-button variant="primary" href="{{ route('lecturer.classes.edit', $section) }}">
                    Chỉnh sửa
                </x-button>
                @endcan
            </div>
        </div>



        {{-- Class Info Card --}}
        <x-card padding="true">
            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-6">
                <div>
                    <p class="text-[12px] font-semibold uppercase tracking-wider text-text-muted mb-1">{{ $section->code }}</p>
                    <h2 class="text-[26px] font-bold text-navy-900 leading-tight mb-3">{{ $section->name ?? $section->code }}</h2>
                    <span class="inline-block uppercase text-[11px] font-bold px-2.5 py-1 rounded-[4px]
                                    @if($section->status === 'active') bg-teal-50 text-teal-800 border-[0.5px] border-teal-200
                                    @elseif($section->status === 'archived') bg-surface-1 text-text-muted border-[0.5px] border-border-clean
                                    @else bg-red-50 text-red-700 border-[0.5px] border-red-200 @endif">
                        {{ match($section->status) {
                            'active'   => 'Đang mở',
                            'archived' => 'Đã lưu trữ',
                            default    => 'Đã huỷ',
                        } }}
                    </span>
                </div>

                {{-- Invite Code Box --}}
                <div class="border-[0.5px] border-border-clean bg-surface-0 rounded-[8px] p-5 text-center min-w-[220px]">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-text-muted mb-2">Mã tham gia lớp</p>
                    <p class="text-[32px] font-bold text-navy-900 tracking-[0.2em] uppercase font-mono mb-1">
                        {{ $section->invite_code ?? '——' }}
                    </p>
                    <p class="text-[12px] text-text-muted font-medium">Chia sẻ mã này cho sinh viên</p>

                    @can('manage', $section)
                    <form method="POST" action="{{ route('lecturer.classes.regenerate-code', $section) }}" class="mt-4">
                        @csrf
                        <button type="submit"
                            onclick="return confirm('Tạo mã mới? Mã cũ sẽ không còn hoạt động.')"
                            class="text-[12px] font-semibold text-blue-500 hover:text-blue-700 transition-colors">
                            Tạo mã mới
                        </button>
                    </form>
                    @endcan
                </div>
            </div>

            <div class="mt-8 grid grid-cols-2 md:grid-cols-3 gap-6 border-t-[0.5px] border-border-clean pt-6">
                <div>
                    <p class="text-[12px] font-medium text-text-muted mb-1">Sĩ số tối đa</p>
                    <p class="text-[20px] font-bold text-navy-900 leading-none">{{ $section->max_students }}</p>
                </div>
                <div>
                    <p class="text-[12px] font-medium text-text-muted mb-1">Đang theo học</p>
                    <p class="text-[20px] font-bold text-navy-900 leading-none">{{ $section->students->count() }}</p>
                </div>
                <div>
                    <p class="text-[12px] font-medium text-text-muted mb-1">Ngày tạo</p>
                    <p class="text-[20px] font-bold text-navy-900 leading-none">{{ $section->created_at->format('d/m/Y') }}</p>
                </div>
            </div>
        </x-card>

        {{-- Student List --}}
        <x-card padding="true">
            <h3 class="text-[18px] font-bold text-navy-900 mb-6">Danh sách sinh viên ({{ $section->students->count() }})</h3>

            @if($section->students->isEmpty())
            <div class="text-center py-12 bg-surface-0 border-[0.5px] border-border-clean border-dashed rounded-[8px]">
                <p class="text-[13px] font-medium text-text-muted mb-1">Chưa có sinh viên nào tham gia lớp này.</p>
                <p class="text-[12px] text-text-muted">Chia sẻ mã tham gia phía trên cho sinh viên của bạn.</p>
            </div>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b-[1.5px] border-border-clean">
                            <th class="py-3 px-4 text-[12px] font-semibold text-text-muted uppercase tracking-wider">Họ tên</th>
                            <th class="py-3 px-4 text-[12px] font-semibold text-text-muted uppercase tracking-wider">Email</th>
                            <th class="py-3 px-4 text-[12px] font-semibold text-text-muted uppercase tracking-wider">MSSV</th>
                            <th class="py-3 px-4 text-[12px] font-semibold text-text-muted uppercase tracking-wider">Ngày tham gia</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-[0.5px] divide-border-clean">
                        @foreach($section->students->sortBy('name') as $student)
                        <tr class="hover:bg-surface-0 transition-colors">
                            <td class="py-4 px-4 text-[14px] font-medium text-navy-900">{{ $student->name }}</td>
                            <td class="py-4 px-4 text-[13px] text-text-muted">{{ $student->email }}</td>
                            <td class="py-4 px-4 text-[13px] font-mono font-medium text-navy-600">{{ $student->student_code ?? '—' }}</td>
                            <td class="py-4 px-4 text-[13px] text-text-muted">{{ $student->pivot->enrolled_at ? \Carbon\Carbon::parse($student->pivot->enrolled_at)->format('d/m/Y') : '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </x-card>

        {{-- Exam Schedule List --}}
        <x-card padding="true">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-[18px] font-bold text-navy-900">
                    Lịch thi
                    <span class="ml-2 text-[13px] font-semibold text-text-muted">({{ $section->examSchedules->count() }})</span>
                </h3>
                <x-button variant="secondary" href="{{ route('lecturer.schedules.create', ['course_section_id' => $section->id]) }}">
                    + Lên lịch thi
                </x-button>
            </div>

            @if($section->examSchedules->isEmpty())
            <div class="text-center py-12 bg-surface-0 border-[0.5px] border-border-clean border-dashed rounded-[8px]">
                <p class="text-[13px] font-medium text-text-muted mb-1">Chưa có đề thi nào trong lớp này.</p>
                <p class="text-[12px] text-text-muted">Nhấn "Tạo đề thi" để bắt đầu.</p>
            </div>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b-[1.5px] border-border-clean">
                            <th class="py-3 px-4 text-[12px] font-semibold text-text-muted uppercase tracking-wider">Tên đề thi</th>
                            <th class="py-3 px-4 text-[12px] font-semibold text-text-muted uppercase tracking-wider">Thời gian</th>
                            <th class="py-3 px-4 text-[12px] font-semibold text-text-muted uppercase tracking-wider">Trạng thái ca thi</th>
                            <th class="py-3 px-4 text-[12px] font-semibold text-text-muted uppercase tracking-wider">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-[0.5px] divide-border-clean">
                        @foreach($section->examSchedules->sortByDesc('created_at') as $schedule)
                        <tr class="hover:bg-surface-0 transition-colors">
                            <td class="py-4 px-4">
                                <p class="text-[14px] font-semibold text-navy-900">{{ $schedule->exam->title }}</p>
                                @if($schedule->start_time)
                                <p class="text-[12px] text-text-muted mt-0.5">
                                    Mở: {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} - Đóng: {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
                                    (Ngày thi: {{ $schedule->exam_date->format('d/m/Y') }})
                                </p>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-[13px] font-medium text-navy-900">
                                {{ $schedule->exam->duration_minutes }} phút / {{ $schedule->exam->questions_count ?? 0 }} câu
                            </td>
                            <td class="py-4 px-4">
                                <span class="inline-block uppercase text-[10px] font-bold px-2 py-1 rounded-[4px]
                                        @if($schedule->status === 'in_progress') bg-teal-50 text-teal-800 border-[0.5px] border-teal-200
                                        @elseif($schedule->status === 'scheduled') bg-blue-50 text-blue-700 border-[0.5px] border-blue-200
                                        @elseif($schedule->status === 'completed') bg-gray-50 text-gray-700 border-[0.5px] border-gray-200
                                        @else bg-surface-1 text-text-muted border-[0.5px] border-border-clean @endif">
                                    {{ match($schedule->status) {
                                            'in_progress' => 'Đang thi',
                                            'scheduled'   => 'Đã lên lịch',
                                            'completed'   => 'Đã hoàn thành',
                                            default       => 'Đã huỷ',
                                        } }}
                                </span>
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-3">
                                    @can('manageLecturer', $schedule->exam)
                                    <a href="{{ route('lecturer.schedules.edit', $schedule) }}"
                                        class="text-[12px] font-semibold text-blue-500 hover:text-blue-700 transition-colors">
                                        Sửa lịch thi
                                    </a>

                                    <form method="POST" action="{{ route('lecturer.schedules.destroy', $schedule) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="text-[12px] font-semibold text-red-500 hover:text-red-700 transition-colors"
                                            onclick="return confirm('Xoá lịch thi này?')">
                                            Xoá
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </x-card>

        {{-- Danger zone --}}
        @can('manage', $section)
        @if($section->students->isEmpty())
        <div class="bg-red-50 border-[0.5px] border-red-200 rounded-[10px] p-6">
            <h3 class="text-[16px] font-bold text-red-700 mb-1">Xoá lớp học phần</h3>
            <p class="text-[13px] text-red-600/80 font-medium mb-4">Hành động này không thể hoàn tác. Lớp sẽ bị xoá vĩnh viễn khỏi hệ thống.</p>
            <form method="POST" action="{{ route('lecturer.classes.destroy', $section) }}">
                @csrf
                @method('DELETE')
                <button type="submit"
                    onclick="return confirm('Bạn có chắc muốn xoá lớp này không?')"
                    class="h-9 px-5 bg-red-600 text-white font-medium text-[13px] rounded-[6px] hover:bg-red-700 transition-colors">
                    Xoá lớp vĩnh viễn
                </button>
            </form>
        </div>
        @endif
        @endcan

    </div>

    {{-- Notification Modal --}}
    @can('manage', $section)
    <div id="create-notification-modal" class="hidden fixed inset-0 bg-navy-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white border-[0.5px] border-border-clean rounded-[10px] shadow-sm w-full max-w-lg p-6 md:p-8">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-[20px] font-bold text-navy-900">Tạo thông báo</h3>
                <button onclick="document.getElementById('create-notification-modal').classList.add('hidden')" class="text-text-muted hover:text-navy-900 transition-colors">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ route('lecturer.classes.notifications.store', $section) }}" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-[12px] font-medium text-navy-900 mb-1.5">Tiêu đề</label>
                    <x-text-input name="title" type="text" :value="old('title')" required placeholder="Nhập tiêu đề thông báo mới" />
                    @error('title')
                    <p class="mt-1 text-[11px] font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-[12px] font-medium text-navy-900 mb-1.5">Nội dung chi tiết</label>
                    <textarea name="message" required rows="5" placeholder="Viết nội dung thông báo gửi đến sinh viên..." class="w-full p-4 bg-white border-[1.5px] border-border-clean rounded-[6px] text-[14px] text-navy-900 placeholder:text-text-muted focus:border-blue-400 focus:ring-4 focus:ring-blue-100/50 transition-all outline-none resize-y">{{ old('message') }}</textarea>
                    @error('message')
                    <p class="mt-1 text-[11px] font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="pt-2 flex justify-end">
                    <x-button type="submit" variant="primary">
                        Đăng thông báo
                    </x-button>
                </div>
            </form>
        </div>
    </div>
    @endcan
</x-app-layout>