<x-app-layout>
    @section('title', 'Ngân hàng câu hỏi — EMS')
    @section('page-title', 'Ngân hàng câu hỏi')

    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-navy-900 leading-tight">Quản lý câu hỏi</h2>
                <p class="text-sm font-medium text-text-muted mt-1">Danh sách toàn bộ câu hỏi trắc nghiệm bạn đã tạo.</p>
            </div>
            <x-button variant="primary" class="flex items-center gap-2 text-sm">
                <x-ui-icon name="plus" class="w-4 h-4" />
                Thêm câu hỏi
            </x-button>
        </div>

        {{-- Bảng danh sách câu hỏi --}}
        <x-card padding="false" class="overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b-[1.5px] border-border-clean bg-surface-0">
                            <th class="py-4 px-5 text-[10px] font-bold text-text-muted uppercase tracking-wider w-16">ID</th>
                            <th class="py-4 px-5 text-[10px] font-bold text-text-muted uppercase tracking-wider">Nội dung câu hỏi</th>
                            <th class="py-4 px-5 text-[10px] font-bold text-text-muted uppercase tracking-wider w-32">Môn học</th>
                            <th class="py-4 px-5 text-[10px] font-bold text-text-muted uppercase tracking-wider w-24 text-center">Độ khó</th>
                            <th class="py-4 px-5 text-[10px] font-bold text-text-muted uppercase tracking-wider text-right w-24">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-[0.5px] divide-border-clean">
                        {{-- Dữ liệu mẫu (Mockup) --}}
                        <tr class="hover:bg-surface-0 transition-colors group">
                            <td class="py-4 px-5 text-sm font-mono font-bold text-navy-400">#1024</td>
                            <td class="py-4 px-5">
                                <p class="text-sm font-medium text-navy-900 line-clamp-2 group-hover:text-blue-600 transition-colors">Trong mô hình MVC, thành phần nào chịu trách nhiệm xử lý logic nghiệp vụ và truy xuất cơ sở dữ liệu?</p>
                            </td>
                            <td class="py-4 px-5 text-xs font-bold text-text-muted uppercase tracking-wider">CNPM</td>
                            <td class="py-4 px-5 text-center"><x-badge type="info">Trung bình</x-badge></td>
                            <td class="py-4 px-5 text-right">
                                <button class="text-blue-600 hover:text-navy-900 text-xs font-bold uppercase tracking-wider">Sửa</button>
                            </td>
                        </tr>
                        <tr class="hover:bg-surface-0 transition-colors group">
                            <td class="py-4 px-5 text-sm font-mono font-bold text-navy-400">#1025</td>
                            <td class="py-4 px-5">
                                <p class="text-sm font-medium text-navy-900 line-clamp-2 group-hover:text-blue-600 transition-colors">Thuật toán Dijkstra dùng để giải quyết bài toán gì?</p>
                            </td>
                            <td class="py-4 px-5 text-xs font-bold text-text-muted uppercase tracking-wider">CTDL & GT</td>
                            <td class="py-4 px-5 text-center"><x-badge type="danger">Khó</x-badge></td>
                            <td class="py-4 px-5 text-right">
                                <button class="text-blue-600 hover:text-navy-900 text-xs font-bold uppercase tracking-wider">Sửa</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>
</x-app-layout>