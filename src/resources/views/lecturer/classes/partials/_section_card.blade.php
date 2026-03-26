<x-card class="flex flex-col h-full overflow-hidden" x-show="searchQuery === '' || '{{ strtolower(($section->name ?? '') . ' ' . $section->code) }}'.includes(searchQuery.toLowerCase())">
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
