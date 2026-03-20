<x-app-layout>
    @section('title', 'Học phần của tôi — EMS')
    @section('page-title', 'Học phần của tôi')

    @php
        $enrolledSections = auth()->user()->enrolledSections()->withCount('students')->with('lecturer')->get();
    @endphp

    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-[24px] font-bold text-navy-900 leading-tight">Học phần của tôi</h2>
                <p class="text-[13px] font-medium text-text-muted mt-1">Danh sách các lớp học phần bạn đã tham gia.</p>
            </div>
            <x-button variant="primary" onclick="document.getElementById('join-class-modal').classList.remove('hidden')" class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                Tham gia lớp học phần
            </x-button>
        </div>



        {{-- Class Grid --}}
        @if($enrolledSections->isEmpty())
            <div class="text-center py-20 bg-surface-0 border-[0.5px] border-border-clean border-dashed rounded-[10px]">
                <svg class="mx-auto w-12 h-12 text-blue-200 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                <p class="font-semibold text-navy-900 text-[16px]">Chưa tham gia lớp học phần nào</p>
                <p class="text-[13px] text-text-muted mt-2 mb-6">Nhập mã lớp học để tham gia lớp học phần đầu tiên.</p>
                <x-button variant="primary" onclick="document.getElementById('join-class-modal').classList.remove('hidden')">
                    Tham gia lớp ngay
                </x-button>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                @foreach($enrolledSections as $section)
                    @php
                        $searchableText = strtolower(($section->name ?? '') . ' ' . $section->code);
                    @endphp
                    <x-card class="flex flex-col h-full overflow-hidden" x-show="searchQuery === '' || '{{ $searchableText }}'.includes(searchQuery.toLowerCase())">
                        {{-- Card Top --}}
                        <div class="px-5 py-4 border-b-[0.5px] border-border-clean bg-surface-1">
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
                            @if($section->lecturer)
                            <div class="flex items-center justify-between text-[13px]">
                                <span class="font-medium text-text-muted">Giảng viên</span>
                                <span class="font-semibold text-navy-900">{{ $section->lecturer->name }}</span>
                            </div>
                            @endif
                        </div>

                        {{-- Card Footer --}}
                        <div class="px-5 pb-5 pt-2 flex gap-3">
                            <x-button variant="primary" href="{{ route('student.dashboard') }}" class="flex-1 text-center justify-center">
                                Xem chi tiết
                            </x-button>
                            <form method="POST" action="{{ route('student.leave-class', $section) }}" onsubmit="return confirm('Bạn có chắc muốn rời lớp này?')">
                                @csrf
                                @method('DELETE')
                                <x-button type="submit" variant="outline" class="px-3 !text-red-600 !border-red-200 hover:!bg-red-50">
                                    Rời lớp
                                </x-button>
                            </form>
                        </div>
                    </x-card>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Modal tham gia lớp --}}
    <div id="join-class-modal" class="hidden fixed inset-0 bg-navy-950/60 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
        <x-card padding="true" class="w-full max-w-md">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-[17px] font-semibold text-navy-900">Tham gia lớp học phần</h3>
                <button onclick="document.getElementById('join-class-modal').classList.add('hidden')" class="text-text-muted hover:text-navy-900">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            @if(!auth()->user()->student_code)
            <div class="bg-amber-50 border-[0.5px] border-amber-600 rounded-[8px] p-4 text-center">
                <p class="text-[12px] text-amber-600 font-medium mb-3">Trước tiên hãy hoàn tất hồ sơ sinh viên (nhập MSSV).</p>
                <x-button variant="primary" class="w-full" href="{{ route('onboarding.show') }}">
                    Nhập thông tin sinh viên
                </x-button>
            </div>
            @else
            <form method="POST" action="{{ route('student.join-class') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-[12px] font-medium text-navy-900 mb-1">Mã lớp học</label>
                    <x-text-input name="invite_code" type="text" required placeholder="VD: ABC123" class="font-mono" />
                    @error('invite_code')
                    <p class="mt-1 text-[11px] font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <x-button variant="ghost" onclick="document.getElementById('join-class-modal').classList.add('hidden')">Hủy bỏ</x-button>
                    <x-button type="submit" variant="primary">Tham gia</x-button>
                </div>
            </form>
            @endif
        </x-card>
    </div>

    @if($errors->has('invite_code') || session('error'))
    <script>
        document.getElementById('join-class-modal').classList.remove('hidden');
    </script>
    @endif
</x-app-layout>
