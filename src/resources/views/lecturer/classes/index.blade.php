<x-app-layout>
    @section('title', 'Không gian lớp học phần — EMS')
    @section('page-title', 'Không gian lớp học')

    @php
    $mainScreenSubjectOptions = collect($subjects ?? []);
    $mainScreenSemesterOptions = collect($semesters ?? []);
    $createSemesterOptions = collect($createSemesters ?? []);
    $shouldOpenCreateClassModal = request()->boolean('open_create_modal') || $errors->any();
    $canOpenCreateClassModal = $mainScreenSubjectOptions->isNotEmpty() && $createSemesterOptions->isNotEmpty();
    $semesterStatusLabel = static fn (?string $status): string => match ($status) {
    \App\Models\Semester::STATUS_CURRENT => 'Đang diễn ra',
    \App\Models\Semester::STATUS_UPCOMING => 'Sắp mở',
    \App\Models\Semester::STATUS_ENDED => 'Đã kết thúc',
    \App\Models\Semester::STATUS_ARCHIVED => 'Lưu trữ',
    default => 'Không xác định',
    };
    @endphp

    <div class="space-y-6" x-data="lecturerClassIndexFiltersState()">

        <x-card padding="true" variant="featured">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                <div>
                    <h2 class="text-2xl md:text-[28px] font-bold text-navy-900 leading-tight">Không gian lớp học phần được phân công</h2>
                    <p class="text-sm text-text-muted mt-2">Bấm trực tiếp vào từng lớp để mở Class Workspace dạng tab. Sidebar bên trái luôn giữ nguyên để bạn chuyển lớp nhanh mà không mất bối cảnh làm việc.</p>
                </div>
                <div class="flex items-center gap-3 flex-shrink-0">
                    <button
                        type="button"
                        @click="$dispatch('open-modal', 'create-class-modal')"
                        @disabled(! $canOpenCreateClassModal)
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-[8px] text-white text-[13px] font-semibold shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 {{ $canOpenCreateClassModal ? 'bg-primary-500 hover:bg-primary-600' : 'bg-slate-400 cursor-not-allowed' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Tạo Lớp Học Mới
                    </button>
                    <div class="px-4 py-3 rounded-[8px] bg-white border border-blue-200 text-[12px] text-navy-900 font-medium">
                        Tổng lớp đang dạy: <span class="font-bold">{{ $sections->total() }}</span>
                    </div>
                </div>
            </div>

            @if(! $canOpenCreateClassModal)
            <div class="mt-4 rounded-[8px] border border-amber-200 bg-amber-50 px-4 py-3 text-[12px] text-amber-800 font-medium">
                Cần có ít nhất một môn học được phân công và một học kỳ còn mở để tạo lớp học phần mới.
            </div>
            @endif
        </x-card>

        <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4 bg-white p-4 rounded-[10px] border border-border-clean/70 shadow-sm">
            <x-search-input x-model="searchQuery" placeholder="Tìm theo tên lớp hoặc mã lớp..." class="!max-w-xl" />

            <div class="flex flex-wrap items-center gap-3">
                <div class="flex items-center gap-2">
                    <label class="text-xs font-bold uppercase tracking-wider text-text-muted" for="semester-filter">Học kỳ</label>
                    <select id="semester-filter" x-model="semesterFilter" @change="persistMainFilters()"
                        class="h-9 min-w-[170px] px-3 rounded-[6px] border border-border-clean text-[13px] font-semibold text-navy-900 focus:border-blue-400 focus:ring-2 focus:ring-blue-50 outline-none">
                        <option value="all">Tất cả học kỳ</option>
                        @foreach($mainScreenSemesterOptions as $semester)
                        <option value="{{ $semester->id }}">{{ $semester->name }} — {{ $semesterStatusLabel($semester->lifecycle_status) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <label class="text-xs font-bold uppercase tracking-wider text-text-muted" for="subject-filter">Môn học</label>
                    <select id="subject-filter" x-model="subjectFilter" @change="persistMainFilters()"
                        class="h-9 min-w-[200px] px-3 rounded-[6px] border border-border-clean text-[13px] font-semibold text-navy-900 focus:border-blue-400 focus:ring-2 focus:ring-blue-50 outline-none">
                        <option value="all">Tất cả môn học</option>
                        @foreach($mainScreenSubjectOptions as $subject)
                        <option value="{{ $subject->id }}">{{ $subject->code }} - {{ $subject->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <label class="text-xs font-bold uppercase tracking-wider text-text-muted" for="status-filter">Trạng thái</label>
                    <select id="status-filter" x-model="statusFilter"
                        class="h-9 px-3 rounded-[6px] border border-border-clean text-[13px] font-semibold text-navy-900 focus:border-blue-400 focus:ring-2 focus:ring-blue-50 outline-none">
                        <option value="all">Tất cả</option>
                        <option value="active">Đang mở</option>
                        <option value="archived">Lưu trữ</option>
                        <option value="cancelled">Đã huỷ</option>
                    </select>
                </div>
            </div>
        </div>

        @if($sections->isEmpty())
        <div class="text-center py-20 bg-surface-0 border-[0.5px] border-border-clean border-dashed rounded-[10px]">
            <x-ui-icon name="rectangle-group" class="mx-auto w-12 h-12 text-blue-100 mb-4" />
            <p class="font-bold text-navy-900 text-lg">Chưa có lớp học phần được phân công</p>
            <p class="text-sm text-text-muted mt-2">Khi có lớp học phần mới được giao, chúng sẽ tự động xuất hiện tại đây và trên Sidebar.</p>
        </div>
        @else
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6" id="sections-grid">
            @foreach($sections as $section)
            @include('lecturer.classes.partials._section_card', ['section' => $section])
            @endforeach
        </div>

        <div class="mt-6">{{ $sections->links() }}</div>
        @endif

        <x-modal name="create-class-modal" maxWidth="2xl" :show="$shouldOpenCreateClassModal" focusable centered>
            <div class="px-6 py-4 border-b border-border-clean flex items-center justify-between bg-surface-0">
                <div>
                    <h3 class="text-[18px] font-bold text-navy-900">Khởi tạo lớp học phần mới</h3>
                    <p class="text-[12px] text-text-muted mt-1">Tạo lớp ngay tại trang quản lý để không mất bối cảnh làm việc.</p>
                </div>
                <button @click="$dispatch('close-modal', 'create-class-modal')" class="text-text-muted hover:text-navy-900 transition-colors" type="button">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="p-6 overflow-y-auto max-h-[85vh]">
                @if ($errors->any())
                <div class="rounded-[8px] border border-red-200 bg-red-50 p-4 mb-5">
                    <div class="flex items-center gap-2 mb-2">
                        <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-[13px] font-semibold text-red-700">Vui lòng kiểm tra lại thông tin</span>
                    </div>
                    <ul class="list-disc list-inside text-[12px] text-red-600 space-y-1">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form action="{{ route('lecturer.classes.store') }}" method="POST" class="space-y-5">
                    @csrf

                    <div class="flex flex-col gap-1.5">
                        <label for="class-name" class="text-[12px] font-medium text-navy-900">
                            Tên lớp học phần <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="class-name"
                            name="name"
                            value="{{ old('name') }}"
                            placeholder="VD: Toán cao cấp — Nhóm 3"
                            required
                            class="w-full border-[1.5px] rounded-[6px] px-3 py-2.5 text-[13px] text-navy-900 bg-white placeholder:text-text-muted/50 focus:border-primary-500 focus:ring-2 focus:ring-blue-50 outline-none transition-colors {{ $errors->has('name') ? 'border-red-300' : 'border-border-clean' }}">
                        @error('name')
                        <p class="text-[11px] font-medium text-red-600">{{ $message }}</p>
                        @else
                        <p class="text-[11px] text-text-muted">Đặt tên rõ ràng giúp sinh viên dễ nhận diện lớp.</p>
                        @enderror
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label for="subject-select" class="text-[12px] font-medium text-navy-900">
                            Môn học <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="subject-select"
                            name="subject_id"
                            required
                            class="w-full border-[1.5px] rounded-[6px] px-3 py-2.5 text-[13px] text-navy-900 bg-white focus:border-primary-500 focus:ring-2 focus:ring-blue-50 outline-none transition-colors {{ $errors->has('subject_id') ? 'border-red-300' : 'border-border-clean' }}">
                            <option value="" disabled {{ old('subject_id') ? '' : 'selected' }}>— Chọn môn học —</option>
                            @foreach($mainScreenSubjectOptions as $subject)
                            <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                                {{ $subject->code }} — {{ $subject->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('subject_id')
                        <p class="text-[11px] font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label for="semester-select" class="text-[12px] font-medium text-navy-900">
                            Học kỳ <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="semester-select"
                            name="semester_id"
                            required
                            class="w-full border-[1.5px] rounded-[6px] px-3 py-2.5 text-[13px] text-navy-900 bg-white focus:border-primary-500 focus:ring-2 focus:ring-blue-50 outline-none transition-colors {{ $errors->has('semester_id') ? 'border-red-300' : 'border-border-clean' }}">
                            <option value="" disabled {{ old('semester_id') ? '' : 'selected' }}>— Chọn học kỳ —</option>
                            @foreach($createSemesterOptions as $semester)
                            <option value="{{ $semester->id }}" {{ old('semester_id') == $semester->id ? 'selected' : '' }}>
                                {{ $semester->name }} — {{ $semesterStatusLabel($semester->lifecycle_status) }}
                            </option>
                            @endforeach
                        </select>
                        @error('semester_id')
                        <p class="text-[11px] font-medium text-red-600">{{ $message }}</p>
                        @else
                        <p class="text-[11px] text-text-muted">Trạng thái học kỳ được hiển thị để bạn biết lớp sẽ mở ngay hay ở kỳ sắp tới.</p>
                        @enderror
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label for="max-students" class="text-[12px] font-medium text-navy-900">
                            Sĩ số tối đa
                        </label>
                        <input
                            type="number"
                            id="max-students"
                            name="max_students"
                            min="1"
                            max="500"
                            value="{{ old('max_students', 100) }}"
                            class="w-full border-[1.5px] rounded-[6px] px-3 py-2.5 text-[13px] text-navy-900 bg-white focus:border-primary-500 focus:ring-2 focus:ring-blue-50 outline-none transition-colors {{ $errors->has('max_students') ? 'border-red-300' : 'border-border-clean' }}">
                        @error('max_students')
                        <p class="text-[11px] font-medium text-red-600">{{ $message }}</p>
                        @else
                        <p class="text-[11px] text-text-muted">Mặc định 100 sinh viên nếu bạn không thay đổi.</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-border-clean/60">
                        <button
                            type="button"
                            @click="$dispatch('close-modal', 'create-class-modal')"
                            class="inline-flex items-center px-4 py-2.5 rounded-[6px] border border-border-clean text-[13px] font-medium text-navy-900 hover:bg-surface-1 transition-colors">
                            Huỷ bỏ
                        </button>
                        <button
                            type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-[6px] bg-primary-500 hover:bg-primary-600 text-white text-[13px] font-semibold shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Khởi tạo lớp học
                        </button>
                    </div>
                </form>
            </div>
        </x-modal>
    </div>
</x-app-layout>