<x-app-layout>
    @section('title', 'Điểm danh — EMS')
    @section('page-title', 'Quản lý điểm danh')

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
            <div>
                <h2 class="text-2xl font-bold text-navy-900 leading-tight">Điểm danh Sinh viên</h2>
                <p class="text-sm font-medium text-text-muted mt-1">Theo dõi chuyên cần các lớp học phần bạn đang phụ trách.</p>
            </div>
            </div>
        </div>

        <x-card padding="true">
            <div class="text-center py-12 bg-surface-0 border-[0.5px] border-border-clean border-dashed rounded-xl">
                <x-ui-icon name="check-badge" class="mx-auto w-12 h-12 text-blue-100 mb-4" />
                <p class="text-sm font-bold text-navy-900 mb-1">Chưa có danh sách điểm danh nào hôm nay.</p>
                <p class="text-xs text-text-muted">Chọn lớp học phần để bắt đầu phiên điểm danh mới.</p>

                <div class="mt-6">
                    <x-button variant="primary">Chọn lớp điểm danh</x-button>
                </div>
            </div>
        </x-card>
    </div>
</x-app-layout>