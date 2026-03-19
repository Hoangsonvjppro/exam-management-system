<x-app-layout>
    @section('title', 'Lịch thi — EMS')
    @section('page-title', 'Lịch thi & Ca thi')

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-[24px] font-bold text-navy-900 leading-tight">Lịch thi của tôi</h2>
                <p class="text-[13px] font-medium text-text-muted mt-1">Xem danh sách các ca thi sắp diễn ra.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {{-- Card Lịch thi mẫu --}}
            <x-card hoverable="true" padding="true" class="border-l-[4px] border-l-blue-500">
                <div class="flex items-start justify-between mb-3">
                    <x-badge type="info">Sắp diễn ra</x-badge>
                    <span class="text-[12px] font-semibold text-text-muted">15/11/2023</span>
                </div>
                <h3 class="text-[16px] font-bold text-navy-900 mb-1">Thi cuối kỳ Hệ điều hành</h3>
                <p class="text-[13px] text-text-muted mb-4">Lớp: INT3204_1 • Phòng: Máy tính 302</p>

                <div class="flex items-center gap-2 text-[13px] font-medium text-navy-900 bg-surface-0 p-2 rounded-[6px]">
                    <svg class="w-4 h-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    08:00 AM - 09:30 AM
                </div>
            </x-card>

            <x-card hoverable="true" padding="true" class="border-l-[4px] border-l-teal-500 opacity-75">
                <div class="flex items-start justify-between mb-3">
                    <x-badge type="success">Đã hoàn thành</x-badge>
                    <span class="text-[12px] font-semibold text-text-muted">10/11/2023</span>
                </div>
                <h3 class="text-[16px] font-bold text-navy-900 mb-1">Quiz Giữa kỳ Mạng máy tính</h3>
                <p class="text-[13px] text-text-muted mb-4">Lớp: INT3302_1 • Thi trực tuyến</p>

                <div class="flex items-center gap-2 text-[13px] font-medium text-navy-900 bg-surface-0 p-2 rounded-[6px]">
                    <svg class="w-4 h-4 text-teal-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    13:00 PM - 13:45 PM
                </div>
            </x-card>
        </div>
    </div>
</x-app-layout>