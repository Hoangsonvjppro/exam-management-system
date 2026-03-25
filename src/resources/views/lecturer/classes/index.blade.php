<x-app-layout>
    @section('title', 'Lớp học phần của tôi — EMS')
    @section('page-title', 'Lớp học phần')

    <div class="space-y-6" x-data="{ searchQuery: '' }">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-[24px] font-bold text-navy-900 leading-tight">Lớp học phần của tôi</h2>
                <p class="text-[13px] font-medium text-text-muted mt-1">Quản lý và tạo các lớp học phần bạn phụ trách.</p>
            </div>
            <x-button variant="primary" href="{{ route('lecturer.classes.create') }}" class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                Tạo lớp mới
            </x-button>
        </div>

        {{-- Toolbar: Search & Action --}}
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white p-4 rounded-[10px] border-[0.5px] border-border-clean shadow-sm">
            <x-search-input x-model="searchQuery" placeholder="Tìm kiếm lớp học phần..." class="!max-w-md" />
            <div class="flex items-center gap-2">
                <span class="text-[13px] text-text-muted">Trạng thái:</span>
                <select class="text-[13px] border-none bg-transparent font-semibold text-navy-900 focus:ring-0 cursor-pointer">
                    <option value="all">Tất cả</option>
                    <option value="active">Đang mở</option>
                    <option value="archived">Lưu trữ</option>
                </select>
            </div>
        </div>


        {{-- Class Grid --}}
        @if($sections->isEmpty())
            <div class="text-center py-20 bg-surface-0 border-[0.5px] border-border-clean border-dashed rounded-[10px]">
                <svg class="mx-auto w-12 h-12 text-blue-200 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                <p class="font-semibold text-navy-900 text-[16px]">Chưa có lớp học phần nào</p>
                <p class="text-[13px] text-text-muted mt-2 mb-6">Tạo lớp học phần đầu tiên và chia sẻ mã mời cho sinh viên.</p>
                <x-button variant="primary" href="{{ route('lecturer.classes.create') }}">
                    Tạo lớp ngay
                </x-button>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                @foreach($sections as $section)
                    @php
                        $searchableText = strtolower(($section->name ?? '') . ' ' . $section->code);
                    @endphp
                    <x-card class="flex flex-col h-full overflow-hidden" x-show="searchQuery === '' || '{{ $searchableText }}'.includes(searchQuery.toLowerCase())">
                        {{-- Card Top --}}
                        <div class="px-5 py-4 border-b-[0.5px] border-border-clean
                            @if($section->status === 'active') bg-surface-1
                            @elseif($section->status === 'archived') bg-surface-1
                            @else bg-red-50 @endif">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-[11px] uppercase tracking-wider text-text-muted">{{ $section->code }}</p>
                                    <h3 class="font-bold text-[16px] text-navy-900 leading-tight mt-1">{{ $section->name ?? $section->code }}</h3>
                                </div>
                                <span class="uppercase text-[10px] font-bold px-2 py-1 rounded-[4px] shrink-0
                                    @if($section->status === 'active') bg-teal-50 text-teal-800 border-[0.5px] border-teal-200
                                    @elseif($section->status === 'archived') bg-surface-1 text-text-muted border-[0.5px] border-border-clean
                                    @else bg-red-50 text-red-700 border-[0.5px] border-red-200 @endif">
                                    {{ match($section->status) {
                                        'active'   => 'Đang mở',
                                        'archived' => 'Đã lưu trữ',
                                        default    => 'Đã huỷ',
                                    } }}
                                </span>
                            </div>
                        </div>

                        {{-- Card Body --}}
                        <div class="p-5 flex-1 space-y-4">
                            <div class="flex items-center justify-between text-[13px]">
                                <span class="font-medium text-text-muted">Sinh viên</span>
                                <span class="font-bold text-navy-900">{{ $section->students_count ?? 0 }} <span class="text-text-muted font-medium">/ {{ $section->max_students }}</span></span>
                            </div>
                            <div class="flex items-center justify-between text-[13px]">
                                <span class="font-medium text-text-muted">Mã tham gia</span>
                                <span class="font-mono bg-surface-0 border-[0.5px] border-border-clean px-2 py-0.5 text-[12px] rounded-[4px] font-bold text-navy-600 uppercase">
                                    {{ $section->invite_code ?? '—' }}
                                </span>
                            </div>
                        </div>

                        {{-- Card Footer --}}
                        <div class="px-5 pb-5 pt-2 flex gap-3">
                            <x-button variant="primary" href="{{ route('lecturer.classes.show', $section) }}" class="flex-1 text-center justify-center">
                                Xem chi tiết
                            </x-button>
                            <x-button variant="outline" href="{{ route('lecturer.classes.edit', $section) }}" class="px-3">
                                Sửa
                            </x-button>
                        </div>
                    </x-card>
                @endforeach
            </div>

            <div class="mt-6">{{ $sections->links() }}</div>
        @endif
    </div>
</x-app-layout>
