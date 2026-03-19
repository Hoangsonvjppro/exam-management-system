<x-app-layout>
    @section('title', 'Điểm danh — EMS')
    @section('page-title', 'Quản lý điểm danh')

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-[24px] font-bold text-navy-900 leading-tight">Điểm danh Sinh viên</h2>
                <p class="text-[13px] font-medium text-text-muted mt-1">Theo dõi chuyên cần các lớp học phần bạn đang phụ trách.</p>
            </div>
        </div>

        <x-card padding="true">
            <div class="text-center py-12 bg-surface-0 border-[0.5px] border-border-clean border-dashed rounded-[8px]">
                <svg class="mx-auto w-12 h-12 text-blue-200 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-[13px] font-medium text-text-muted mb-1">Chưa có danh sách điểm danh nào hôm nay.</p>
                <p class="text-[12px] text-text-muted">Chọn lớp học phần để bắt đầu phiên điểm danh mới.</p>

                <div class="mt-6">
                    <x-button variant="primary">Chọn lớp điểm danh</x-button>
                </div>
            </div>
        </x-card>
    </div>
</x-app-layout>