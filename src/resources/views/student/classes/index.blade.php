<x-app-layout>
    @section('title', 'Học phần của tôi — EMS')
    @section('page-title', 'Học phần của tôi')

    <div class="space-y-6" x-data="{ searchQuery: '' }">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-3xl font-bold text-navy-900 leading-tight">Học phần của tôi</h2>
                <p class="text-sm font-medium text-text-muted mt-1">Danh sách các lớp học phần bạn đang theo học trong học kỳ hiện tại.</p>
            </div>
        </div>

        {{-- Filters & Search --}}
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white p-4 rounded-[10px] border-[0.5px] border-border-clean shadow-sm">
            <x-search-input x-model="searchQuery" placeholder="Tìm kiếm theo tên hoặc mã lớp..." class="!max-w-md" />
            <div class="flex items-center gap-2 text-[11px] text-text-muted font-medium">
                <span>{{ $enrolledSections->count() }} lớp</span>
            </div>
        </div>

        {{-- Class Grid --}}
        @if($enrolledSections->isEmpty())
        <div class="text-center py-20 bg-surface-0 border-[0.5px] border-border-clean border-dashed rounded-[10px]">
            <x-ui-icon name="academic-cap" class="mx-auto w-12 h-12 text-blue-200 mb-4" />
            <p class="font-semibold text-navy-900 text-lg">Chưa có lớp học phần nào</p>
            <p class="text-sm text-text-muted mt-2">Lớp học phần sẽ được hiển thị khi quản trị viên phân công.</p>
        </div>
        @else
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            @foreach($enrolledSections as $section)
            @php
            $searchableText = strtolower(($section->name ?? '') . ' ' . $section->code);
            @endphp
            <a href="{{ route('student.classes.show', $section) }}"
               class="block group"
               x-show="searchQuery === '' || '{{ $searchableText }}'.includes(searchQuery.toLowerCase())">
                <x-card class="flex flex-col h-full overflow-hidden hover:border-blue-400 hover:shadow-md transition-all">
                    {{-- Card Top --}}
                    <div class="px-5 py-4 border-b-[0.5px] border-border-clean bg-surface-1">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-semibold text-[10px] uppercase tracking-wider text-text-muted">{{ $section->code }}</p>
                                <h3 class="font-bold text-lg text-navy-900 leading-tight mt-1 group-hover:text-blue-600 transition-colors">{{ $section->name ?? $section->code }}</h3>
                            </div>
                            <span class="uppercase text-[10px] font-bold px-2 py-1 rounded-[4px] shrink-0
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
                    </div>

                    {{-- Card Body --}}
                    <div class="p-5 flex-1 space-y-3">
                        @if($section->lecturer)
                        <div class="flex items-center justify-between text-sm">
                            <span class="font-medium text-text-muted">Giảng viên</span>
                            <span class="font-semibold text-navy-900">{{ $section->lecturer->name }}</span>
                        </div>
                        @endif
                        <div class="flex items-center justify-between text-sm">
                            <span class="font-medium text-text-muted">Sinh viên</span>
                            <span class="font-bold text-navy-900">{{ $section->students_count ?? 0 }} <span class="text-text-muted font-medium">/ {{ $section->max_students }}</span></span>
                        </div>
                    </div>

                    {{-- Card Footer --}}
                    <div class="px-5 pb-4 pt-2">
                        <div class="flex items-center justify-between text-[12px]">
                            <span class="text-text-muted font-medium">Xem chi tiết lớp</span>
                            <svg class="w-4 h-4 text-text-muted group-hover:text-blue-600 group-hover:translate-x-1 transition-all" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </x-card>
            </a>
            @endforeach
        </div>
        @endif
    </div>
</x-app-layout>