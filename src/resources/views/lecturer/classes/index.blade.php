<x-app-layout>
    @section('title', 'Lớp học phần của tôi — EMS')
    @section('page-title', 'Lớp học phần')

    <div class="space-y-6" x-data="{ searchQuery: '' }">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-navy-900 leading-tight">Lớp học phần của tôi</h2>
                <p class="text-sm font-medium text-text-muted mt-1">Quản lý và tạo các lớp học phần bạn phụ trách.</p>
            </div>
            <x-button variant="primary" href="{{ route('lecturer.classes.create') }}" class="flex items-center gap-2 text-sm">
                <x-ui-icon name="plus" class="w-4 h-4" />
                Tạo lớp mới
            </x-button>
        </div>

        {{-- Toolbar: Search & Action --}}
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white p-4 rounded-xl border border-border-clean/50 shadow-sm">
            <x-search-input x-model="searchQuery" placeholder="Tìm kiếm lớp học phần..." class="!max-w-md" />
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-text-muted uppercase tracking-wider">Trạng thái:</span>
                    <select class="text-sm border-none bg-transparent font-bold text-navy-900 focus:ring-0 cursor-pointer">
                        <option value="all">Tất cả</option>
                        <option value="active">Đang mở</option>
                        <option value="archived">Lưu trữ</option>
                    </select>
                </div>
            </div>
        </div>


        {{-- Class Grid --}}
        @if($sections->isEmpty())
            <div class="text-center py-20 bg-surface-0 border-[0.5px] border-border-clean border-dashed rounded-xl">
                <x-ui-icon name="rectangle-group" class="mx-auto w-12 h-12 text-blue-100 mb-4" />
                <p class="font-bold text-navy-900 text-lg">Chưa có lớp học phần nào</p>
                <p class="text-sm text-text-muted mt-2 mb-6">Tạo lớp học phần đầu tiên và chia sẻ mã mời cho sinh viên.</p>
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
                                    <p class="font-bold text-[10px] uppercase tracking-widest text-text-muted opacity-70">{{ $section->code }}</p>
                                    <h3 class="font-bold text-lg text-navy-900 leading-tight mt-1 group-hover:text-blue-600 transition-colors">{{ $section->name ?? $section->code }}</h3>
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
                        <div class="p-6 flex-1 space-y-4">
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-bold text-xs uppercase tracking-wider text-text-muted opacity-80">Sinh viên</span>
                                <span class="font-bold text-navy-900">{{ $section->students_count ?? 0 }} <span class="text-text-muted font-medium ml-1">/ {{ $section->max_students }}</span></span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-bold text-xs uppercase tracking-wider text-text-muted opacity-80">Mã mời</span>
                                <span class="font-mono bg-blue-50 border border-blue-100 px-3 py-1 text-xs rounded-full font-bold text-blue-700 uppercase tracking-widest">
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
