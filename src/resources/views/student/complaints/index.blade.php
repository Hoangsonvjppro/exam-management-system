<x-app-layout>
    @section('title', 'Khiếu nại — EMS')
    @section('page-title', 'Khiếu nại')

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-3xl font-bold text-navy-900 leading-tight">Khiếu nại của tôi</h2>
                <p class="text-sm font-medium text-text-muted mt-1">Theo dõi tiến trình xử lý các đơn khiếu nại về điểm thi.</p>
            </div>
        </div>

        <x-card padding="true">
            <div class="text-center py-16 bg-surface-0 border-[0.5px] border-border-clean border-dashed rounded-[8px]">
                <svg class="w-14 h-14 text-blue-100 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                </svg>
                <p class="text-navy-900 font-semibold text-base mb-2">Tính năng đang được phát triển</p>
                <p class="text-sm text-text-muted max-w-md mx-auto">
                    Bạn có thể gửi khiếu nại nhanh từ Tab <strong>"Điểm số"</strong> trong trang chi tiết lớp học phần.
                    Hệ thống quản lý khiếu nại đầy đủ sẽ sớm ra mắt.
                </p>
            </div>
        </x-card>
    </div>
</x-app-layout>
