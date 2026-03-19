<x-app-layout>
    @section('title', 'Ngân hàng câu hỏi — EMS')
    @section('page-title', 'Ngân hàng câu hỏi')

    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-[24px] font-bold text-navy-900 leading-tight">Quản lý câu hỏi</h2>
                <p class="text-[13px] font-medium text-text-muted mt-1">Danh sách toàn bộ câu hỏi trắc nghiệm bạn đã tạo.</p>
            </div>
            <x-button variant="primary" class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                </svg>
                Thêm câu hỏi
            </x-button>
        </div>

        {{-- Bảng danh sách câu hỏi --}}
        <x-card padding="false" class="overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b-[1.5px] border-border-clean bg-surface-0">
                            <th class="py-3 px-5 text-[12px] font-semibold text-text-muted uppercase tracking-wider w-16">ID</th>
                            <th class="py-3 px-5 text-[12px] font-semibold text-text-muted uppercase tracking-wider">Nội dung câu hỏi</th>
                            <th class="py-3 px-5 text-[12px] font-semibold text-text-muted uppercase tracking-wider w-32">Môn học</th>
                            <th class="py-3 px-5 text-[12px] font-semibold text-text-muted uppercase tracking-wider w-24">Độ khó</th>
                            <th class="py-3 px-5 text-[12px] font-semibold text-text-muted uppercase tracking-wider text-right w-24">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-[0.5px] divide-border-clean">
                        {{-- Dữ liệu mẫu (Mockup) --}}
                        <tr class="hover:bg-surface-0 transition-colors">
                            <td class="py-4 px-5 text-[13px] font-mono font-medium text-navy-600">#1024</td>
                            <td class="py-4 px-5">
                                <p class="text-[14px] font-medium text-navy-900 line-clamp-2">Trong mô hình MVC, thành phần nào chịu trách nhiệm xử lý logic nghiệp vụ và truy xuất cơ sở dữ liệu?</p>
                            </td>
                            <td class="py-4 px-5 text-[13px] text-text-muted">CNPM</td>
                            <td class="py-4 px-5"><x-badge type="info">Trung bình</x-badge></td>
                            <td class="py-4 px-5 text-right">
                                <button class="text-blue-500 hover:text-blue-700 text-[12px] font-semibold">Sửa</button>
                            </td>
                        </tr>
                        <tr class="hover:bg-surface-0 transition-colors">
                            <td class="py-4 px-5 text-[13px] font-mono font-medium text-navy-600">#1025</td>
                            <td class="py-4 px-5">
                                <p class="text-[14px] font-medium text-navy-900 line-clamp-2">Thuật toán Dijkstra dùng để giải quyết bài toán gì?</p>
                            </td>
                            <td class="py-4 px-5 text-[13px] text-text-muted">CTDL & GT</td>
                            <td class="py-4 px-5"><x-badge type="danger">Khó</x-badge></td>
                            <td class="py-4 px-5 text-right">
                                <button class="text-blue-500 hover:text-blue-700 text-[12px] font-semibold">Sửa</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>
</x-app-layout>