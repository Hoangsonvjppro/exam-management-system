<x-app-layout>
    @section('title', 'Tạo lớp học phần — Đã ẩn')
    @section('page-title', 'Lớp học phần')

    <div class="max-w-xl space-y-6">
        <x-card padding="true" variant="featured">
            <h2 class="text-[22px] font-bold text-navy-900 leading-tight">Tính năng tạo lớp học phần đã được ẩn</h2>
            <p class="text-[13px] text-text-muted mt-2 leading-relaxed">
                Theo quy trình mới, lớp học phần được phân công từ hệ thống quản trị đào tạo.
                Giảng viên tập trung vào tổ chức kiểm tra, quản lý sinh viên và vận hành lớp trong Class Workspace.
            </p>

            <div class="mt-5 flex flex-wrap items-center gap-3">
                <x-button variant="primary" href="{{ route('lecturer.classes.index') }}">
                    Đi tới không gian lớp học
                </x-button>
                <x-button variant="outline" href="{{ route('lecturer.schedules.index') }}">
                    Quản lý lịch thi
                </x-button>
            </div>
        </x-card>
    </div>
</x-app-layout>
