@php
$runtimeStatus = $schedule->runtime_status;
$canEdit = $schedule->can_edit;

$statusMap = [
'scheduled' => ['Đã lên lịch', 'bg-[#EBF2FA] text-[#1A3A6B] border border-[#D6E2F0]', 'bg-[#1A3A6B]'],
'in_progress' => ['Đang diễn ra', 'bg-[#FFFBEB] text-[#92400E] border border-[#FDE68A]', 'bg-[#D97706]'],
'completed' => ['Hoàn thành', 'bg-[#ECFDF5] text-[#065F46] border border-[#A7F3D0]', 'bg-[#059669]'],
'cancelled' => ['Đã hủy', 'bg-[#FEF2F2] text-[#991B1B] border border-[#FECACA]', 'bg-[#DC2626]'],
];

[$label, $badgeClass, $dotClass] = $statusMap[$runtimeStatus] ?? ['—', 'bg-[#F3F4F6] text-[#6B7C99] border border-[#E5E7EB]', 'bg-[#6B7C99]'];
@endphp

<tr class="border-t border-[#EBF2FA] hover:bg-[#F8FAFD] transition-colors {{ $runtimeStatus === 'in_progress' ? 'bg-[#FFFBEB]/35' : '' }}">
    <td class="px-5 py-5 font-semibold text-[#1A3A6B] leading-relaxed">{{ $schedule->exam->title }}</td>
    <td class="px-5 py-5 text-[#6B7C99] leading-relaxed">{{ $schedule->courseSection->name ?? '—' }}</td>
    <td class="px-5 py-5 text-center text-[#374151] font-medium">{{ $schedule->exam_date->format('d/m/Y') }}</td>
    <td class="px-5 py-5 text-center text-[#374151] font-medium">{{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} – {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}</td>
    <td class="px-5 py-5 text-center text-[#374151] font-semibold">{{ $schedule->assigned_count ?? 0 }}</td>
    <td class="px-5 py-5 text-center">
        <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full {{ $badgeClass }}">
            <span class="h-1.5 w-1.5 rounded-full {{ $dotClass }} {{ $runtimeStatus === 'in_progress' ? 'animate-pulse' : '' }}"></span>
            {{ $label }}
        </span>
    </td>
    <td class="px-5 py-5 text-center align-top">
        <div
            class="relative inline-block text-left"
            x-data="{
                open: false,
                confirmingDelete: false,
                confirmingCancel: false,
                menuTop: 0,
                menuLeft: 0,
                menuWidth: 256,
                placeMenu() {
                    const rect = this.$refs.menuBtn.getBoundingClientRect();
                    this.menuTop = rect.bottom + 8;
                    this.menuLeft = Math.max(12, rect.right - this.menuWidth);
                },
                toggleMenu() {
                    this.open = !this.open;
                    this.confirmingDelete = false;
                    this.confirmingCancel = false;
                    if (this.open) {
                        this.$nextTick(() => this.placeMenu());
                    }
                },
                closeMenu() {
                    this.open = false;
                    this.confirmingDelete = false;
                    this.confirmingCancel = false;
                }
            }"
            @keydown.escape.window="closeMenu()"
            @resize.window="if(open){ placeMenu(); }"
            @scroll.window="if(open){ placeMenu(); }">
            <button
                x-ref="menuBtn"
                type="button"
                @click.stop="toggleMenu()"
                class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-[#D6E2F0] bg-white text-[#6B7C99] hover:text-[#1A3A6B] hover:border-[#BFD4EA] transition-colors"
                aria-label="Mở menu thao tác">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="12" cy="5" r="1.8" />
                    <circle cx="12" cy="12" r="1.8" />
                    <circle cx="12" cy="19" r="1.8" />
                </svg>
            </button>

            <template x-teleport="body">
                <div
                    x-show="open"
                    x-cloak
                    @click.away="closeMenu()"
                    @click.stop
                    x-transition.origin.top.right
                    class="fixed rounded-xl border border-[#D6E2F0] bg-white shadow-lg ring-1 ring-black/5 z-[200] overflow-hidden"
                    :style="`top: ${menuTop}px; left: ${menuLeft}px; width: ${menuWidth}px;`">

                    <div class="px-3 py-2.5 bg-[#F8FAFD] border-b border-[#EEF2F7] text-left">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-[#1A3A6B]">Thao tác ca thi</p>
                        <p class="text-[11px] text-[#6B7C99] mt-0.5">{{ $schedule->exam->title }}</p>
                    </div>

                    <a href="{{ route('lecturer.schedules.monitor', $schedule->id) }}"
                        class="block px-3 py-3 text-[12px] font-semibold text-[#0F766E] hover:bg-[#F0FDFA] transition-colors">
                        Giám sát ca thi
                    </a>

                    <button
                        type="button"
                        @click='openAssignModal({{ $schedule->id }}, {!! json_encode($schedule->exam->title) !!}, {!! json_encode($schedule->courseSection->name ?? "—") !!}); open = false'
                        class="block w-full text-left px-3 py-3 text-[12px] font-semibold text-[#1D4ED8] hover:bg-[#EFF6FF] transition-colors">
                        Phân sinh viên
                    </button>

                    @if($canEdit)
                    <a href="{{ route('lecturer.schedules.edit', $schedule->id) }}"
                        class="block px-3 py-3 text-[12px] font-semibold text-[#374151] hover:bg-[#F3F4F6] transition-colors">
                        Sửa lịch thi
                    </a>
                    @else
                    <div class="px-3 py-3 text-[12px] font-semibold text-[#94A3B8] bg-[#F8FAFD] cursor-not-allowed text-left">
                        Sửa lịch thi
                        <p class="mt-0.5 text-[10px] font-medium">Ca thi đã bắt đầu, kết thúc hoặc bị hủy.</p>
                    </div>
                    @endif

                    <div class="border-t border-[#EEF2F7]"></div>

                    @if($canEdit)
                    <template x-if="!confirmingDelete">
                        <button
                            type="button"
                            @click="confirmingDelete = true"
                            class="block w-full text-left px-3 py-3 text-[12px] font-semibold text-[#DC2626] hover:bg-[#FEF2F2] transition-colors">
                            Xoá lịch thi
                        </button>
                    </template>

                    <div x-show="confirmingDelete" x-cloak class="px-3 py-2.5 bg-red-50 border-t border-red-100">
                        <p class="text-[10px] font-semibold text-red-700">Xác nhận xóa ca thi này?</p>
                        <div class="mt-1.5 flex items-center gap-3">
                            <form method="POST" action="{{ route('lecturer.schedules.destroy', $schedule->id) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-[10px] font-bold text-red-700 hover:underline">Có, xóa</button>
                            </form>
                            <button type="button" @click="confirmingDelete = false" class="text-[10px] font-medium text-[#6B7C99] hover:underline">Hủy</button>
                        </div>
                    </div>
                    @elseif($runtimeStatus !== 'cancelled')
                    <template x-if="!confirmingCancel">
                        <button
                            type="button"
                            @click="confirmingCancel = true"
                            class="block w-full text-left px-3 py-3 text-[12px] font-semibold text-[#B45309] hover:bg-[#FFFBEB] transition-colors">
                            Hủy ca thi
                        </button>
                    </template>

                    <div x-show="confirmingCancel" x-cloak class="px-3 py-2.5 bg-amber-50 border-t border-amber-100">
                        <p class="text-[10px] font-semibold text-amber-700">Ca thi đã bắt đầu hoặc kết thúc chỉ có thể hủy. Xác nhận hủy?</p>
                        <div class="mt-1.5 flex items-center gap-3">
                            <form method="POST" action="{{ route('lecturer.schedules.cancel', $schedule->id) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="text-[10px] font-bold text-amber-700 hover:underline">Có, hủy ca</button>
                            </form>
                            <button type="button" @click="confirmingCancel = false" class="text-[10px] font-medium text-[#6B7C99] hover:underline">Đóng</button>
                        </div>
                    </div>
                    @endif
                </div>
            </template>
        </div>
    </td>
</tr>