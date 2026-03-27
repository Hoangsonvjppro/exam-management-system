<x-app-layout>
    @section('title', 'Không gian lớp học phần — EMS')
    @section('page-title', 'Không gian lớp học')

    <div class="space-y-6" x-data="{ searchQuery: '', statusFilter: 'all' }">

        <x-card padding="true" variant="featured">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                <div>
                    <h2 class="text-2xl md:text-[28px] font-bold text-navy-900 leading-tight">Không gian lớp học phần được phân công</h2>
                    <p class="text-sm text-text-muted mt-2">Bấm trực tiếp vào từng lớp để mở Class Workspace dạng tab. Sidebar bên trái luôn giữ nguyên để bạn chuyển lớp nhanh mà không mất bối cảnh làm việc.</p>
                </div>
                <div class="px-4 py-3 rounded-[8px] bg-white border border-blue-200 text-[12px] text-navy-900 font-medium">
                    Tổng lớp đang dạy: <span class="font-bold">{{ $sections->total() }}</span>
                </div>
            </div>
        </x-card>

        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white p-4 rounded-[10px] border border-border-clean/70 shadow-sm">
            <x-search-input x-model="searchQuery" placeholder="Tìm theo tên lớp hoặc mã lớp..." class="!max-w-xl" />

            <div class="flex items-center gap-2">
                <label class="text-xs font-bold uppercase tracking-wider text-text-muted" for="status-filter">Trạng thái</label>
                <select id="status-filter" x-model="statusFilter"
                        class="h-9 px-3 rounded-[6px] border border-border-clean text-[13px] font-semibold text-navy-900 focus:border-blue-400 focus:ring-2 focus:ring-blue-50 outline-none">
                    <option value="all">Tất cả</option>
                    <option value="active">Đang mở</option>
                    <option value="archived">Lưu trữ</option>
                    <option value="cancelled">Đã huỷ</option>
                </select>
            </div>
        </div>

        @if($sections->isEmpty())
            <div class="text-center py-20 bg-surface-0 border-[0.5px] border-border-clean border-dashed rounded-[10px]">
                <x-ui-icon name="rectangle-group" class="mx-auto w-12 h-12 text-blue-100 mb-4" />
                <p class="font-bold text-navy-900 text-lg">Chưa có lớp học phần được phân công</p>
                <p class="text-sm text-text-muted mt-2">Khi có lớp học phần mới được giao, chúng sẽ tự động xuất hiện tại đây và trên Sidebar.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6" id="sections-grid">
                @foreach($sections as $section)
                    @include('lecturer.classes.partials._section_card', ['section' => $section])
                @endforeach
            </div>

            <div class="mt-6">{{ $sections->links() }}</div>
        @endif
    </div>
</x-app-layout>
