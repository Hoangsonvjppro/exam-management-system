<x-app-layout>
    @section('title', 'Tổng quan — Sinh viên')
    @section('page-title', 'Tổng quan học tập')

    <div class="space-y-6">

        {{-- ═══ Hero: Greeting + Quick Stats ═══ --}}
        <div class="bg-[#F4F7FC] border-[0.5px] border-[#B5D4F4] rounded-[10px] p-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <h2 class="text-2xl md:text-3xl font-bold text-[#1A3A6B] leading-tight">
                        Xin chào, {{ auth()->user()->name }}!
                    </h2>
                    <p class="mt-2 text-sm text-[#6B7C99]">
                        @if (auth()->user()->student_code)
                            Mã sinh viên: <span class="font-semibold text-[#1A3A6B]">{{ auth()->user()->student_code }}</span>
                            @if(auth()->user()->studentClass)
                                — Lớp: <span class="font-semibold text-[#1A3A6B]">{{ auth()->user()->studentClass->name }}</span>
                            @endif
                        @else
                            Bạn chưa hoàn tất hồ sơ sinh viên (MSSV + Họ tên).
                            <a href="{{ route('onboarding.show') }}" class="text-[#185FA5] font-medium hover:underline ml-1">Hoàn tất hồ sơ ngay →</a>
                        @endif
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2 px-3 py-2 bg-white border-[0.5px] border-[#D6E2F0] rounded-[6px]">
                        <svg class="w-4 h-4 text-[#378ADD]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                        </svg>
                        <span class="font-bold text-[#1A3A6B] text-sm">{{ $classes->count() }}</span>
                        <span class="text-[#6B7C99] text-sm">lớp</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══ Join Class Form (always visible) ═══ --}}
        <div class="bg-white border-[0.5px] border-[#D6E2F0] rounded-[10px] p-5">
            <div class="flex items-center gap-2 mb-4">
                <svg class="w-5 h-5 text-[#1D9E75]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
                <h3 class="text-lg font-semibold text-[#1A3A6B]">Tham gia lớp học phần</h3>
            </div>

            @if(session('success'))
                <div class="mb-4 flex items-start gap-2 p-3 bg-[#E1F5EE] border-[0.5px] border-[#1D9E75] rounded-[6px]">
                    <div class="w-[6px] h-[6px] rounded-full bg-[#1D9E75] mt-[6px] shrink-0"></div>
                    <p class="text-sm text-[#065F46]">{{ session('success') }}</p>
                </div>
            @endif

            @if(session('warning'))
                <div class="mb-4 flex items-start gap-2 p-3 bg-[#FEF3C7] border-[0.5px] border-[#D97706] rounded-[6px]">
                    <div class="w-[6px] h-[6px] rounded-full bg-[#D97706] mt-[6px] shrink-0"></div>
                    <p class="text-sm text-[#78350F]">{{ session('warning') }}</p>
                </div>
            @endif

            <form action="{{ route('student.join-class') }}" method="POST" class="flex flex-col sm:flex-row items-start sm:items-end gap-3">
                @csrf
                <div class="flex-1 w-full sm:w-auto">
                    <label for="invite_code" class="block text-xs font-medium text-[#1A3A6B] mb-1">Mã mời lớp</label>
                    <input type="text"
                           id="invite_code"
                           name="invite_code"
                           value="{{ old('invite_code') }}"
                           placeholder="Nhập mã mời do giảng viên cung cấp"
                           class="w-full border-[1.5px] rounded-[6px] px-3 py-2 text-sm text-[#1A3A6B] bg-white
                                  placeholder:text-[#6B7C99]/60
                                  focus:outline-none focus:ring-[3px] focus:ring-[#E6F1FB] focus:border-[#185FA5]
                                  @error('invite_code') border-[#DC2626] bg-[#FEF2F2] @else border-[#D6E2F0] @enderror
                                  transition-all" />
                    @error('invite_code')
                        <p class="mt-1 text-xs text-[#DC2626]">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2 bg-[#1A3A6B] text-white text-sm font-medium rounded-[6px]
                               hover:bg-[#0B2347] active:scale-[0.98] transition-all shadow-sm whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tham gia
                </button>
            </form>

            <p class="mt-2 text-[11px] text-[#6B7C99]">Liên hệ giảng viên để nhận mã mời nếu bạn chưa có.</p>
        </div>

        {{-- ═══ Class List ═══ --}}
        <div class="bg-white border-[0.5px] border-[#D6E2F0] rounded-[10px]">
            <div class="px-5 py-4 border-b-[0.5px] border-[#D6E2F0]">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-[#378ADD]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <h3 class="text-lg font-semibold text-[#1A3A6B]">Lớp học phần của tôi</h3>
                </div>
            </div>

            <div class="p-5">
                @if($classes->isEmpty())
                    {{-- Empty State --}}
                    <div class="text-center py-16 bg-[#F8FAFD] border-[0.5px] border-[#D6E2F0] border-dashed rounded-[8px]">
                        <svg class="mx-auto w-12 h-12 text-[#B5D4F4] mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                        </svg>
                        <p class="font-semibold text-[#1A3A6B] text-base">Bạn chưa tham gia lớp nào</p>
                        <p class="text-sm text-[#6B7C99] mt-2 max-w-sm mx-auto">Hãy nhập mã mời ở phần trên để tham gia lớp học phần đầu tiên của bạn.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                        @foreach($classes as $section)
                            <a href="{{ route('student.classes.show', $section) }}"
                               class="group block border-[0.5px] border-[#D6E2F0] rounded-[10px] overflow-hidden
                                      hover:border-[#378ADD] hover:shadow-md transition-all">

                                {{-- Card Header --}}
                                <div class="px-4 py-3 bg-[#F4F7FC] border-b-[0.5px] border-[#D6E2F0]">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0">
                                            <p class="font-semibold text-[10px] uppercase tracking-wider text-[#6B7C99]">{{ $section->code }}</p>
                                            <h4 class="font-bold text-[15px] text-[#1A3A6B] leading-snug mt-0.5 truncate group-hover:text-[#185FA5] transition-colors">
                                                {{ $section->name ?? $section->code }}
                                            </h4>
                                        </div>
                                        <span class="uppercase text-[10px] font-bold px-2 py-0.5 rounded-[4px] shrink-0
                                            @if($section->status === 'active') bg-[#E1F5EE] text-[#065F46] border-[0.5px] border-[#9FE1CB]
                                            @elseif($section->status === 'archived') bg-[#F4F7FC] text-[#6B7C99] border-[0.5px] border-[#D6E2F0]
                                            @else bg-[#FEE2E2] text-[#991B1B] border-[0.5px] border-[#FCA5A5] @endif">
                                            {{ match($section->status) {
                                                'active'   => 'Đang mở',
                                                'archived' => 'Lưu trữ',
                                                default    => 'Đã huỷ',
                                            } }}
                                        </span>
                                    </div>
                                </div>

                                {{-- Card Body --}}
                                <div class="px-4 py-3 space-y-2">
                                    @if($section->lecturer)
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="text-[#6B7C99] text-xs">Giảng viên</span>
                                            <span class="font-medium text-[#1A3A6B] text-xs">{{ $section->lecturer->name }}</span>
                                        </div>
                                    @endif
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-[#6B7C99] text-xs">Sĩ số</span>
                                        <span class="font-bold text-[#1A3A6B] text-xs">{{ $section->students_count ?? 0 }} <span class="font-normal text-[#6B7C99]">/ {{ $section->max_students }}</span></span>
                                    </div>
                                </div>

                                {{-- Card Footer --}}
                                <div class="px-4 pb-3">
                                    <div class="flex items-center justify-between text-[11px]">
                                        <span class="text-[#6B7C99]">Xem chi tiết</span>
                                        <svg class="w-3.5 h-3.5 text-[#6B7C99] group-hover:text-[#185FA5] group-hover:translate-x-0.5 transition-all" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

    </div>
</x-app-layout>