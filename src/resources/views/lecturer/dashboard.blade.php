<x-app-layout>
    @section('title', 'Dashboard - Giảng viên')
    @section('page-title', 'Tổng quan giảng dạy')

    <div class="space-y-6" x-data="{ searchQuery: '' }">


        <x-card padding="true" variant="featured">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <div>
                    <h2 class="text-2xl md:text-3xl font-bold text-navy-900 leading-tight">Xin chào, {{ auth()->user()->name }}!</h2>
                    <p class="mt-2 text-sm text-text-muted">
                        @if($lecturer->lecturer_code)
                        Mã GV: <span class="font-semibold text-navy-900">{{ $lecturer->lecturer_code }}</span>
                        @endif
                        @if($lecturer->department)
                        — Lĩnh vực: <span class="font-semibold text-navy-900">{{ $lecturer->department }}</span>
                        @endif
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <x-button variant="outline" href="{{ route('lecturer.classes.create') }}" class="text-sm">
                        <x-ui-icon name="plus" class="w-4 h-4 mr-1" />
                        Tạo lớp mới
                    </x-button>
                    <x-button variant="secondary" href="{{ route('lecturer.classes.index') }}" class="text-sm">
                        Quản lý lớp
                    </x-button>
                </div>
            </div>
        </x-card>

        <section class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <x-card padding="true" class="shadow-sm border-border-clean/50">
                <p class="text-xs font-bold text-text-muted mb-2 uppercase tracking-wider">Số lớp đang mở</p>
                <div class="flex items-baseline gap-2">
                    <p class="text-3xl font-bold text-navy-900 leading-none">{{ $activeCount }}</p>
                    <span class="text-xs font-medium text-text-muted">lớp đang dạy</span>
                </div>
            </x-card>

            <x-card padding="true" variant="accent" class="shadow-sm border-border-clean/50">
                <p class="text-xs font-bold text-text-muted mb-2 uppercase tracking-wider">Sinh viên theo học</p>
                <div class="flex items-baseline gap-2">
                    <p class="text-3xl font-bold text-navy-900 leading-none">{{ $studentTotal }}</p>
                    <span class="text-xs font-medium text-text-muted">sinh viên</span>
                </div>
            </x-card>

            <x-card padding="true" class="shadow-sm border-border-clean/50">
                <p class="text-xs font-bold text-text-muted mb-2 uppercase tracking-wider">Ngân hàng câu hỏi</p>
                <div class="flex items-baseline gap-2">
                    <p class="text-3xl font-bold text-navy-900 leading-none">{{ $questionCount }}</p>
                    <span class="text-xs font-medium text-text-muted">câu hỏi</span>
                </div>
            </x-card>
        </section>

        {{-- My class list preview --}}
        <x-card padding="true" class="shadow-sm border-border-clean/50">
            <x-slot name="header">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 py-2 border-b border-border-clean/50 mb-6">
                    <div class="flex items-center gap-2">
                        <x-ui-icon name="academic-cap" class="w-5 h-5 text-navy-900" />
                        <h3 class="text-lg font-bold text-navy-900">Lớp học phần của tôi</h3>
                    </div>
                    <div class="flex items-center gap-3 shrink-0">
                        <x-search-input x-model="searchQuery" placeholder="Tìm kiếm nhanh..." class="!max-w-[200px]" />
                        <a href="{{ route('lecturer.classes.index') }}" class="text-xs font-bold text-blue-600 hover:text-navy-900 uppercase tracking-wider">Xem tất cả →</a>
                    </div>
                </div>
            </x-slot>

            @if($mySections->isEmpty())
            <div class="text-center py-12 bg-surface-0 border-[0.5px] border-border-clean border-dashed rounded-[8px]">
                <x-ui-icon name="rectangle-group" class="w-12 h-12 text-blue-100 mx-auto mb-4" />
                <p class="text-sm text-text-muted font-medium mb-4">Chưa có lớp học phần nào.</p>
                <x-button variant="primary" href="{{ route('lecturer.classes.create') }}">
                    Tạo lớp ngay
                </x-button>
            </div>
            @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($mySections->take(6) as $section)
                @php
                $searchableText = strtolower(($section->name ?? '') . ' ' . $section->code);
                @endphp
                <a href="{{ route('lecturer.classes.show', $section) }}"
                    class="border-[0.5px] border-border-clean bg-surface-0 rounded-[12px] p-5 flex items-start justify-between gap-3 hover:border-blue-400 hover:shadow-md transition-all group"
                    x-show="searchQuery === '' || '{{ $searchableText }}'.includes(searchQuery.toLowerCase())">
                    <div>
                        <p class="font-bold text-base text-navy-900 group-hover:text-blue-600 transition-colors">{{ $section->name ?? $section->code }}</p>
                        <p class="text-xs text-text-muted font-bold uppercase tracking-wider mt-1">{{ $section->students_count }} sinh viên</p>
                    </div>
                    <span class="font-mono text-[10px] font-bold text-navy-600 bg-blue-50 border border-blue-100 px-2.5 py-1 rounded-full uppercase tracking-widest">
                        {{ $section->invite_code ?? '—' }}
                    </span>
                </a>
                @endforeach
            </div>
            @endif
        </x-card>
    </div>
</x-app-layout>