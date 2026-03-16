<x-app-layout>
    @section('title', 'Lớp học phần của tôi — EMS')
    @section('page-title', 'Lớp học phần')

    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-black uppercase">Lớp học phần của tôi</h2>
                <p class="text-sm font-semibold text-slate-500 mt-1">Quản lý và tạo các lớp học phần bạn phụ trách.</p>
            </div>
            <a href="{{ route('lecturer.classes.create') }}"
               class="h-10 px-5 flex items-center gap-2 bg-ems-primary text-white brutal-border brutal-shadow font-black uppercase tracking-wide brutal-btn text-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                Tạo lớp mới
            </a>
        </div>

        @if(session('success'))
            <div class="p-4 bg-green-100 brutal-border font-semibold text-green-800 text-sm">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="p-4 bg-red-100 brutal-border font-semibold text-red-800 text-sm">
                {{ session('error') }}
            </div>
        @endif

        {{-- Class Grid --}}
        @if($sections->isEmpty())
            <div class="text-center py-20 brutal-border bg-white">
                <svg class="mx-auto w-14 h-14 text-slate-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                <p class="font-black uppercase text-slate-400 text-lg">Chưa có lớp học phần nào</p>
                <p class="text-slate-400 text-sm mt-2 mb-6">Tạo lớp học phần đầu tiên và chia sẻ mã mời cho sinh viên.</p>
                <a href="{{ route('lecturer.classes.create') }}"
                   class="inline-flex items-center gap-2 h-10 px-6 bg-ems-primary text-white brutal-border brutal-shadow font-black uppercase tracking-wide brutal-btn text-sm">
                    Tạo lớp ngay
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                @foreach($sections as $section)
                    <div class="bg-white brutal-border brutal-shadow flex flex-col">
                        {{-- Card Top --}}
                        <div class="p-5 border-b-4 border-black
                            @if($section->status === 'active') bg-ems-primary/10
                            @elseif($section->status === 'archived') bg-slate-100
                            @else bg-red-50 @endif">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-black text-xs uppercase tracking-widest text-slate-500">{{ $section->code }}</p>
                                    <h3 class="font-black text-lg leading-tight mt-1">{{ $section->name ?? $section->code }}</h3>
                                </div>
                                <span class="uppercase text-xs font-black px-2 py-1 brutal-border shrink-0
                                    @if($section->status === 'active') bg-green-200 text-green-800
                                    @elseif($section->status === 'archived') bg-slate-200 text-slate-600
                                    @else bg-red-200 text-red-700 @endif">
                                    {{ match($section->status) {
                                        'active'   => 'Đang mở',
                                        'archived' => 'Đã lưu trữ',
                                        default    => 'Đã huỷ',
                                    } }}
                                </span>
                            </div>
                        </div>

                        {{-- Card Body --}}
                        <div class="p-5 flex-1 space-y-3">
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-semibold text-slate-500">Sinh viên</span>
                                <span class="font-black">{{ $section->students_count ?? 0 }} / {{ $section->max_students }}</span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-semibold text-slate-500">Mã tham gia</span>
                                <span class="font-black font-mono bg-slate-100 brutal-border px-2 py-0.5 text-xs tracking-widest uppercase">
                                    {{ $section->invite_code ?? '—' }}
                                </span>
                            </div>
                        </div>

                        {{-- Card Footer --}}
                        <div class="px-5 pb-5 flex gap-2">
                            <a href="{{ route('lecturer.classes.show', $section) }}"
                               class="flex-1 h-9 flex items-center justify-center bg-ems-primary text-white brutal-border font-black text-xs uppercase tracking-wide brutal-btn">
                                Xem chi tiết
                            </a>
                            <a href="{{ route('lecturer.classes.edit', $section) }}"
                               class="h-9 px-3 flex items-center justify-center bg-white brutal-border font-black text-xs uppercase brutal-btn">
                                Sửa
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <div>{{ $sections->links() }}</div>
        @endif
    </div>
</x-app-layout>
