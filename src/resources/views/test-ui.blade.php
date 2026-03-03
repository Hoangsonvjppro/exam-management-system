<x-app-layout>
    <x-slot name="header">
        {{ __('Tổng quan') }}
    </x-slot>
    <x-slot name="subtitle">
        {{ __('Chào mừng trở lại, ') }} {{ Auth::user()->name ?? 'Nguyễn Văn A' }}!
    </x-slot>

    <div class="space-y-8">

        {{-- ============================================================ --}}
        {{-- SECTION 1: Stat Cards (giống 3 card trên cùng trong ảnh) --}}
        {{-- ============================================================ --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            {{-- Card 1: Kỳ thi sắp tới --}}
            <x-card hoverable>
                <div class="flex items-start justify-between">
                    <div class="flex items-start gap-3">
                        <div class="w-11 h-11 bg-primary-50 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Kỳ thi sắp tới</p>
                            <h3 class="font-bold text-gray-900 text-sm leading-snug">Toán Rời Rạc & Lý...</h3>
                            <div class="flex items-center gap-3 mt-2 text-xs text-gray-500">
                                <span class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    08:00 AM
                                </span>
                                <span class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    25/10/2023
                                </span>
                            </div>
                        </div>
                    </div>
                    <x-badge type="warning">Sắp diễn ra</x-badge>
                </div>
            </x-card>

            {{-- Card 2: Tiến độ học tập --}}
            <x-card hoverable>
                <div class="flex items-start gap-3">
                    <div class="w-11 h-11 bg-success-50 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-success-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Tiến độ học tập</p>
                        <div class="flex items-baseline gap-2">
                            <span class="text-3xl font-extrabold text-gray-900">75%</span>
                            <span class="text-xs text-gray-500">Học kỳ 1</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Hoàn thành 3/4 môn học đăng ký</p>
                        {{-- Progress bar --}}
                        <div class="mt-2 w-full bg-gray-100 rounded-full h-2">
                            <div class="bg-success-500 h-2 rounded-full" style="width: 75%"></div>
                        </div>
                    </div>
                </div>
            </x-card>

            {{-- Card 3: Điểm trung bình (GPA) --}}
            <x-card hoverable>
                <div class="flex items-start gap-3">
                    <div class="w-11 h-11 bg-warning-50 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-warning-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Điểm trung bình (GPA)</p>
                        <div class="flex items-baseline gap-1">
                            <span class="text-3xl font-extrabold text-gray-900">3.6</span>
                            <span class="text-sm text-gray-400">/ 4.0</span>
                        </div>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="text-xs text-success-500 font-semibold flex items-center gap-0.5">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18" /></svg>
                                +0.4
                            </span>
                            <span class="text-xs text-gray-400">Xếp hạng: Xuất sắc</span>
                        </div>
                        {{-- GPA bar --}}
                        <div class="mt-2 w-full bg-gray-100 rounded-full h-2">
                            <div class="bg-warning-500 h-2 rounded-full" style="width: 90%"></div>
                        </div>
                    </div>
                </div>
            </x-card>
        </div>

        {{-- ============================================================ --}}
        {{-- SECTION 2: Buttons & Badges Showcase --}}
        {{-- ============================================================ --}}
        <x-card>
            <x-slot name="header">
                <h2 class="text-lg font-bold text-gray-900">Buttons & Badges</h2>
            </x-slot>

            {{-- Button variants --}}
            <div class="space-y-4">
                <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide">Button Variants</h3>
                <div class="flex flex-wrap items-center gap-3">
                    <x-button variant="primary">Primary</x-button>
                    <x-button variant="secondary">Secondary</x-button>
                    <x-button variant="danger">Danger</x-button>
                    <x-button variant="success">Success</x-button>
                    <x-button variant="warning">Warning</x-button>
                    <x-button variant="ghost">Ghost</x-button>
                    <x-button variant="outline">Outline</x-button>
                </div>

                <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mt-6">Button Sizes</h3>
                <div class="flex flex-wrap items-center gap-3">
                    <x-button variant="primary" size="xs">Extra Small</x-button>
                    <x-button variant="primary" size="sm">Small</x-button>
                    <x-button variant="primary" size="md">Medium</x-button>
                    <x-button variant="primary" size="lg">Large</x-button>
                </div>

                <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mt-6">With Icons (like Nộp bài, Làm bài...)</h3>
                <div class="flex flex-wrap items-center gap-3">
                    <x-button variant="primary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                        Nộp bài
                    </x-button>
                    <x-button variant="secondary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                        Xem ngay
                    </x-button>
                    <x-button variant="outline">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                        Làm bài
                    </x-button>
                    <x-button variant="danger" size="sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        Sắp hết hạn
                    </x-button>
                </div>

                <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mt-6">Icon-Only Buttons</h3>
                <div class="flex flex-wrap items-center gap-3">
                    <x-button variant="ghost" :iconOnly="true">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                    </x-button>
                    <x-button variant="ghost" :iconOnly="true">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                    </x-button>
                    <x-button variant="ghost" :iconOnly="true">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                    </x-button>
                </div>

                <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mt-6">Badges</h3>
                <div class="flex flex-wrap items-center gap-3">
                    <x-badge type="info">Đang học</x-badge>
                    <x-badge type="success">Hoàn thành</x-badge>
                    <x-badge type="warning">Sắp diễn ra</x-badge>
                    <x-badge type="danger">Quá hạn</x-badge>
                    <x-badge type="neutral">Nháp</x-badge>
                </div>
            </div>
        </x-card>

        {{-- ============================================================ --}}
        {{-- SECTION 3: Form Inputs --}}
        {{-- ============================================================ --}}
        <x-card>
            <x-slot name="header">
                <h2 class="text-lg font-bold text-gray-900">Form Inputs</h2>
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-input-label value="Họ và tên" :required="true" />
                    <x-text-input type="text" placeholder="Nguyễn Văn A" class="mt-1" />
                </div>
                <div>
                    <x-input-label value="Email" :required="true" />
                    <x-text-input type="email" placeholder="nguyenvana@example.com" class="mt-1" />
                </div>
                <div>
                    <x-input-label value="Mã số sinh viên" />
                    <x-text-input type="text" placeholder="20210001" class="mt-1" />
                </div>
                <div>
                    <x-input-label value="Lớp" />
                    <x-text-input type="text" placeholder="CNTT - K15" class="mt-1" />
                </div>
                <div class="md:col-span-2">
                    <x-input-label value="Ghi chú" />
                    <textarea rows="3" class="mt-1 w-full border-border bg-white text-gray-800 placeholder-gray-400 focus:border-primary-500 focus:ring-primary-500 rounded-lg shadow-sm text-sm py-2.5 px-4 transition duration-150" placeholder="Nhập ghi chú..."></textarea>
                </div>
                <div class="md:col-span-2">
                    <x-input-label value="Trạng thái (disabled)" />
                    <x-text-input type="text" value="Đã khóa" :disabled="true" class="mt-1" />
                </div>
            </div>
        </x-card>

        {{-- ============================================================ --}}
        {{-- SECTION 4: Table (Nhiệm vụ cần hoàn thành) --}}
        {{-- ============================================================ --}}
        <x-table>
            <x-slot name="header">
                <h2 class="text-lg font-bold text-gray-900">Nhiệm vụ cần hoàn thành</h2>
                <a href="#" class="text-sm text-primary-500 hover:text-primary-600 font-medium">Xem tất cả →</a>
            </x-slot>

            <x-slot name="head">
                <x-table-heading>Nhiệm vụ</x-table-heading>
                <x-table-heading>Môn học</x-table-heading>
                <x-table-heading>Hạn nộp</x-table-heading>
                <x-table-heading align="center">Trạng thái</x-table-heading>
                <x-table-heading align="right">Hành động</x-table-heading>
            </x-slot>

            {{-- Row 1 --}}
            <tr class="hover:bg-surface-hover transition-colors">
                <x-table-cell>
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 bg-danger-50 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-danger-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">Bài tập lớn nhóm - Phân tích thiết kế</p>
                            <p class="text-xs text-gray-500">Môn: Công nghệ phần mềm</p>
                        </div>
                    </div>
                </x-table-cell>
                <x-table-cell>Công nghệ phần mềm</x-table-cell>
                <x-table-cell>Hôm nay</x-table-cell>
                <x-table-cell align="center">
                    <x-badge type="danger">Sắp hết hạn</x-badge>
                </x-table-cell>
                <x-table-cell align="right">
                    <x-button variant="primary" size="sm">Nộp bài</x-button>
                </x-table-cell>
            </tr>

            {{-- Row 2 --}}
            <tr class="hover:bg-surface-hover transition-colors">
                <x-table-cell>
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 bg-primary-50 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">Quiz Tuần 5: CSS Flexbox</p>
                            <p class="text-xs text-gray-500">Môn: Thiết kế Web</p>
                        </div>
                    </div>
                </x-table-cell>
                <x-table-cell>Thiết kế Web</x-table-cell>
                <x-table-cell>26/10/2023</x-table-cell>
                <x-table-cell align="center">
                    <x-badge type="info">Chưa làm</x-badge>
                </x-table-cell>
                <x-table-cell align="right">
                    <x-button variant="secondary" size="sm">Làm bài</x-button>
                </x-table-cell>
            </tr>

            {{-- Row 3 --}}
            <tr class="hover:bg-surface-hover transition-colors">
                <x-table-cell>
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 bg-secondary-50 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-secondary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">Xem lại bài giảng: Con trỏ trong C++</p>
                            <p class="text-xs text-gray-500">Môn: Lập trình nâng cao</p>
                        </div>
                    </div>
                </x-table-cell>
                <x-table-cell>Lập trình nâng cao</x-table-cell>
                <x-table-cell>Tự học</x-table-cell>
                <x-table-cell align="center">
                    <x-badge type="neutral">Tùy chọn</x-badge>
                </x-table-cell>
                <x-table-cell align="right">
                    <x-button variant="secondary" size="sm">Xem ngay</x-button>
                </x-table-cell>
            </tr>

            {{-- Row 4 --}}
            <tr class="hover:bg-surface-hover transition-colors">
                <x-table-cell>
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 bg-success-50 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-success-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">Báo cáo thực hành - Tuần 4</p>
                            <p class="text-xs text-gray-500">Môn: Cơ sở dữ liệu</p>
                        </div>
                    </div>
                </x-table-cell>
                <x-table-cell>Cơ sở dữ liệu</x-table-cell>
                <x-table-cell>22/10/2023</x-table-cell>
                <x-table-cell align="center">
                    <x-badge type="success">Đã nộp</x-badge>
                </x-table-cell>
                <x-table-cell align="right">
                    <x-button variant="ghost" size="sm">Chi tiết</x-button>
                </x-table-cell>
            </tr>
        </x-table>

        {{-- ============================================================ --}}
        {{-- SECTION 5: Bottom Row - Notification Banner + Card Grid       --}}
        {{-- ============================================================ --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Left: Cards in 2 columns --}}
            <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-6">
                {{-- Card example 1 --}}
                <x-card hoverable>
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 bg-primary-50 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900 text-sm">Lập trình C</h4>
                            <p class="text-xs text-gray-500">CS101 • 3 tín chỉ</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between text-xs text-gray-500 mb-2">
                        <span>Tiến độ</span>
                        <span class="font-semibold text-gray-700">60%</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-1.5">
                        <div class="bg-primary-500 h-1.5 rounded-full" style="width: 60%"></div>
                    </div>
                </x-card>

                {{-- Card example 2 --}}
                <x-card hoverable>
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 bg-success-50 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-success-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4" /></svg>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900 text-sm">Cơ sở dữ liệu</h4>
                            <p class="text-xs text-gray-500">CS201 • 4 tín chỉ</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between text-xs text-gray-500 mb-2">
                        <span>Tiến độ</span>
                        <span class="font-semibold text-gray-700">85%</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-1.5">
                        <div class="bg-success-500 h-1.5 rounded-full" style="width: 85%"></div>
                    </div>
                </x-card>
            </div>

            {{-- Right: Notification Banner (giống ảnh - xanh dương) --}}
            <div class="bg-primary-500 rounded-card p-6 text-white flex flex-col justify-between">
                <div>
                    <h3 class="font-bold text-lg mb-2">Thông báo mới</h3>
                    <p class="text-primary-100 text-sm leading-relaxed">
                        Đăng ký học phần kỳ tới sẽ bắt đầu vào ngày 01/11. Vui lòng kiểm tra lộ trình.
                    </p>
                </div>
                <button class="mt-4 w-full bg-white text-primary-600 font-semibold text-sm py-2.5 px-4 rounded-lg hover:bg-primary-50 transition-colors">
                    Xem chi tiết
                </button>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- SECTION 6: Legacy Breeze buttons (backward compat check)     --}}
        {{-- ============================================================ --}}
        <x-card>
            <x-slot name="header">
                <h2 class="text-lg font-bold text-gray-900">Legacy Breeze Components</h2>
            </x-slot>
            <p class="text-sm text-gray-500 mb-4">Kiểm tra các component cũ của Breeze vẫn hoạt động với design mới.</p>
            <div class="flex flex-wrap items-center gap-3">
                <x-primary-button>Primary Button</x-primary-button>
                <x-secondary-button>Secondary Button</x-secondary-button>
                <x-danger-button>Danger Button</x-danger-button>
            </div>
        </x-card>

        {{-- Footer --}}
        <div class="text-center text-xs text-gray-400 py-4">
            © 2023 EduPro LMS. Bản quyền thuộc về nhà trường.
        </div>

    </div>
</x-app-layout>
