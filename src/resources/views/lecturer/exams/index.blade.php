<x-app-layout>
    @section('title', 'Quản lý Đề thi — EMS')
    @section('page-title', 'Tất cả đề thi')

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-[24px] font-bold text-navy-900 leading-tight">Danh sách Đề thi</h2>
                <p class="text-[13px] font-medium text-text-muted mt-1">Quản lý trạng thái và cấu trúc các bài kiểm tra.</p>
            </div>
            <x-button variant="primary" class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                </svg>
                Tạo đề thi mới
            </x-button>
        </div>

        <x-card padding="false" class="overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b-[1.5px] border-border-clean bg-surface-0">
                            <th class="py-3 px-5 text-[12px] font-semibold text-text-muted uppercase tracking-wider">Tên đề thi</th>
                            <th class="py-3 px-5 text-[12px] font-semibold text-text-muted uppercase tracking-wider">Lớp học phần</th>
                            <th class="py-3 px-5 text-[12px] font-semibold text-text-muted uppercase tracking-wider">Thời gian</th>
                            <th class="py-3 px-5 text-[12px] font-semibold text-text-muted uppercase tracking-wider">Trạng thái</th>
                            <th class="py-3 px-5 text-[12px] font-semibold text-text-muted uppercase tracking-wider text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-[0.5px] divide-border-clean">
                        <tr class="hover:bg-surface-0 transition-colors">
                            <td class="py-4 px-5">
                                <p class="text-[14px] font-semibold text-navy-900">Thi giữa kỳ - Lập trình Web</p>
                                <p class="text-[12px] text-text-muted mt-0.5">40 câu hỏi</p>
                            </td>
                            <td class="py-4 px-5 text-[13px] font-medium text-navy-900">INT3306_1</td>
                            <td class="py-4 px-5 text-[13px] text-navy-900">60 phút</td>
                            <td class="py-4 px-5"><x-badge type="success">Đang mở</x-badge></td>
                            <td class="py-4 px-5 text-right">
                                <button class="text-blue-500 hover:text-blue-700 text-[12px] font-semibold">Chi tiết</button>
                            </td>
                        </tr>
                        <tr class="hover:bg-surface-0 transition-colors">
                            <td class="py-4 px-5">
                                <p class="text-[14px] font-semibold text-navy-900">Quiz Tuần 5</p>
                                <p class="text-[12px] text-text-muted mt-0.5">15 câu hỏi</p>
                            </td>
                            <td class="py-4 px-5 text-[13px] font-medium text-navy-900">INT3306_2</td>
                            <td class="py-4 px-5 text-[13px] text-navy-900">15 phút</td>
                            <td class="py-4 px-5"><x-badge type="warning">Bản nháp</x-badge></td>
                            <td class="py-4 px-5 text-right">
                                <button class="text-blue-500 hover:text-blue-700 text-[12px] font-semibold">Chi tiết</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>
</x-app-layout>