<x-app-layout>
    @section('title', 'Học phần của tôi — EMS')
    @section('page-title', 'Học phần của tôi')

    <div class="space-y-6" x-data="{ searchQuery: '' }">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-3xl font-bold text-navy-900 leading-tight">Học phần của tôi</h2>
                <p class="text-sm font-medium text-text-muted mt-1">Danh sách các lớp học phần bạn đã tham gia.</p>
            </div>
            <x-button variant="primary" onclick="document.getElementById('join-class-modal').classList.remove('hidden')" class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                </svg>
                Tham gia lớp học phần
            </x-button>
        </div>

        {{-- Filters & Search --}}
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white p-4 rounded-[10px] border-[0.5px] border-border-clean shadow-sm">
            <x-search-input x-model="searchQuery" placeholder="Tìm kiếm theo tên hoặc mã lớp..." class="!max-w-md" />
            <div class="flex items-center gap-2 text-sm text-text-muted">
                <span>Sắp xếp:</span>
                <select class="bg-transparent border-none text-navy-900 font-semibold focus:ring-0 cursor-pointer">
                    <option>Mới nhất</option>
                    <option>Cũ nhất</option>
                    <option>Tên A-Z</option>
                </select>
            </div>
        </div>



        {{-- Class Grid --}}
        @if($enrolledSections->isEmpty())
        <div class="text-center py-20 bg-surface-0 border-[0.5px] border-border-clean border-dashed rounded-[10px]">
            <x-ui-icon name="academic-cap" class="mx-auto w-12 h-12 text-blue-200 mb-4" />
            <p class="font-semibold text-navy-900 text-lg">Chưa tham gia lớp học phần nào</p>
            <p class="text-sm text-text-muted mt-2 mb-6">Nhập mã lớp học để tham gia lớp học phần đầu tiên.</p>
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
                            <p class="font-semibold text-[10px] uppercase tracking-wider text-text-muted">{{ $section->code }}</p>
                            <h3 class="font-bold text-lg text-navy-900 leading-tight mt-1">{{ $section->name ?? $section->code }}</h3>
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
                    <div class="flex items-center justify-between text-sm">
                        <span class="font-medium text-text-muted">Sinh viên</span>
                        <span class="font-bold text-navy-900">{{ $section->students_count ?? 0 }} <span class="text-text-muted font-medium">/ {{ $section->max_students }}</span></span>
                    </div>
                    @if($section->lecturer)
                    <div class="flex items-center justify-between text-sm">
                        <span class="font-medium text-text-muted">Giảng viên</span>
                        <span class="font-semibold text-navy-900">{{ $section->lecturer->name }}</span>
                    </div>
                    @endif
                </div>

                {{-- Card Footer --}}
                <div class="px-5 pb-5 pt-2 flex flex-col gap-3" x-data="{ confirmingLeave: false }">
                    <div class="flex gap-3">
                        <x-button variant="primary" href="{{ route('student.dashboard') }}" class="flex-1 text-center justify-center">
                            Xem chi tiết
                        </x-button>
                        <x-button type="button" @click="confirmingLeave = true" x-show="!confirmingLeave" variant="outline" class="px-3 !text-red-600 !border-red-200 hover:!bg-red-50">
                            Rời lớp
                        </x-button>
                    </div>

                    <div x-show="confirmingLeave" x-cloak class="flex items-center justify-between bg-red-50 p-3 rounded-lg border border-red-100">
                        <span class="text-xs font-bold text-red-700">Chắc chắn muốn rời?</span>
                        <div class="flex gap-3">
                            <form method="POST" action="{{ route('student.leave-class', $section) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-bold text-red-600 hover:underline">Xác nhận</button>
                            </form>
                            <button type="button" @click="confirmingLeave = false" class="text-xs font-medium text-navy-600 hover:underline">Hủy</button>
                        </div>
                    </div>
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
                <h3 class="text-lg font-semibold text-navy-900">Tham gia lớp học phần</h3>
                <button onclick="document.getElementById('join-class-modal').classList.add('hidden')" class="text-text-muted hover:text-navy-900">
                    <x-ui-icon name="x-mark" class="w-5 h-5" />
                </button>
            </div>

            @if(!auth()->user()->student_code)
            <div class="bg-amber-50 border-[0.5px] border-amber-600 rounded-[8px] p-4 text-center">
                <p class="text-xs text-amber-600 font-medium mb-3">Trước tiên hãy hoàn tất hồ sơ sinh viên (nhập MSSV).</p>
                <x-button variant="primary" class="w-full" href="{{ route('onboarding.show') }}">
                    Nhập thông tin sinh viên
                </x-button>
            </div>
            @else
            <form method="POST" action="{{ route('student.join-class') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-navy-900 mb-1">Mã lớp học</label>
                    <x-text-input name="invite_code" type="text" :value="old('invite_code')" required placeholder="VD: ABC123" class="font-mono" />
                    @error('invite_code')
                    <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
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