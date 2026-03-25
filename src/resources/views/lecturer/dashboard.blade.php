<x-app-layout>
    @section('title', 'Dashboard - Giảng viên')
    @section('page-title', 'Tổng quan giảng dạy')

    <div class="space-y-6" x-data="{ searchQuery: '' }">


        <x-card padding="true" variant="featured">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <div>
                    <h2 class="text-[22px] md:text-[28px] font-bold text-navy-900 leading-tight">Xin chào, {{ auth()->user()->name }}!</h2>
                    <p class="mt-2 text-[13px] text-text-muted">
                        @if($lecturer->lecturer_code)
                        Mã GV: <span class="font-semibold text-navy-900">{{ $lecturer->lecturer_code }}</span>
                        @endif
                        @if($lecturer->department)
                        — Lĩnh vực: <span class="font-semibold text-navy-900">{{ $lecturer->department }}</span>
                        @endif
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <x-button variant="outline" href="{{ route('lecturer.classes.create') }}">
                        + Tạo lớp mới
                    </x-button>
                    <x-button variant="secondary" href="{{ route('lecturer.classes.index') }}">
                        Quản lý lớp
                    </x-button>
                </div>
            </div>
        </x-card>

        <section class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <x-card padding="true">
                <p class="text-[12px] font-medium text-text-muted mb-1">Số lớp đang mở</p>
                <div class="flex items-baseline gap-2">
                    <p class="text-[28px] font-bold text-navy-900 leading-none">{{ $activeCount }}</p>
                    <span class="text-[12px] text-text-muted">lớp</span>
                </div>
            </x-card>

            <x-card padding="true" variant="accent">
                <p class="text-[12px] font-medium text-text-muted mb-1">Sinh viên theo học</p>
                <div class="flex items-baseline gap-2">
                    <p class="text-[28px] font-bold text-navy-900 leading-none">{{ $studentTotal }}</p>
                    <span class="text-[12px] text-text-muted">sinh viên</span>
                </div>
            </x-card>

            <x-card padding="true">
                <p class="text-[12px] font-medium text-text-muted mb-1">Ngân hàng câu hỏi</p>
                <div class="flex items-baseline gap-2">
                    <p class="text-[28px] font-bold text-navy-900 leading-none">{{ $questionCount }}</p>
                    <span class="text-[12px] text-text-muted">câu</span>
                </div>
            </x-card>
        </section>

        {{-- My class list preview --}}
        <x-card padding="true">
            <x-slot name="header">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 py-1">
                    <h3 class="text-[17px] font-semibold text-navy-900">Lớp học phần của tôi</h3>
                    <div class="flex items-center gap-3 shrink-0">
                        <x-search-input x-model="searchQuery" placeholder="Tìm kiếm nhanh..." class="!max-w-[200px]" />
                        <a href="{{ route('lecturer.classes.index') }}" class="text-[13px] font-medium text-blue-400 hover:text-navy-900 hover:underline">Xem tất cả →</a>
                    </div>
                </div>
            </x-slot>

            @if($mySections->isEmpty())
            <div class="text-center py-10 bg-surface-0 border-[0.5px] border-border-clean border-dashed rounded-[8px]">
                <p class="text-[13px] text-text-muted font-medium mb-4">Chưa có lớp học phần nào.</p>
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
                    class="border-[0.5px] border-border-clean bg-surface-0 rounded-[8px] p-4 flex items-start justify-between gap-3 hover:border-blue-200 transition-colors"
                    x-show="searchQuery === '' || '{{ $searchableText }}'.includes(searchQuery.toLowerCase())">
                    <div>
                        <p class="font-semibold text-[14px] text-navy-900">{{ $section->name ?? $section->code }}</p>
                        <p class="text-[12px] text-text-muted font-medium mt-0.5">{{ $section->students_count }} sinh viên</p>
                    </div>
                    <span class="font-mono text-[11px] font-medium text-navy-600 bg-white border-[0.5px] border-border-clean px-2 py-0.5 rounded-[4px] uppercase">
                        {{ $section->invite_code ?? '—' }}
                    </span>
                </a>
                @endforeach
            </div>
            @endif
        </x-card>
    </div>
</x-app-layout>