<tr class="border-t border-[#EBF2FA] hover:bg-[#F8FAFD] transition-colors">
    <td class="px-5 py-3.5 font-medium text-[#1A3A6B]">{{ $schedule->exam->title }}</td>
    <td class="px-5 py-3.5 text-[#6B7C99]">{{ $schedule->courseSection->name ?? '—' }}</td>
    <td class="px-5 py-3.5 text-center text-[#374151]">{{ $schedule->exam_date->format('d/m/Y') }}</td>
    <td class="px-5 py-3.5 text-center text-[#374151]">{{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} – {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}</td>
    <td class="px-5 py-3.5 text-center text-[#374151]">{{ $schedule->assigned_count ?? 0 }}</td>
    <td class="px-5 py-3.5 text-center">
        @php
        $statusMap = [
            'scheduled' => ['Đã lên lịch', 'bg-[#EBF2FA] text-[#1A3A6B]'],
            'in_progress' => ['Đang thi', 'bg-[#FFFBEB] text-[#92400E]'],
            'completed' => ['Hoàn thành', 'bg-[#ECFDF5] text-[#065F46]'],
            'cancelled' => ['Đã hủy', 'bg-[#FEF2F2] text-[#991B1B]'],
        ];
        [$label, $cls] = $statusMap[$schedule->status] ?? ['—', 'bg-[#F3F4F6] text-[#6B7C99]'];
        @endphp
        <span class="inline-block text-xs font-medium px-2.5 py-0.5 rounded-full {{ $cls }}">{{ $label }}</span>
    </td>
    <td class="px-5 py-3.5 text-center">
        <div class="flex items-center justify-center gap-2" x-data="{ confirmingDelete: false }">
            <a href="{{ route('lecturer.schedules.edit', $schedule->id) }}" class="text-[#185FA5] hover:underline text-xs">Sửa</a>
            
            <template x-if="!confirmingDelete">
                <div class="flex items-center gap-2">
                    <form method="POST" action="{{ route('lecturer.schedules.assign-students', $schedule->id) }}" class="inline">
                        @csrf
                        <button type="submit" class="text-[#065F46] hover:underline text-xs">Phân SV</button>
                    </form>
                    <button type="button" @click="confirmingDelete = true" class="text-[#DC2626] hover:underline text-xs">Xoá</button>
                </div>
            </template>

            <template x-if="confirmingDelete">
                <div class="flex items-center gap-2 bg-red-50 px-2 py-1 rounded border border-red-100">
                    <span class="text-[10px] text-red-600 font-bold">Xác nhận?</span>
                    <form method="POST" action="{{ route('lecturer.schedules.destroy', $schedule->id) }}" class="inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-[10px] font-bold text-red-700 hover:underline">Có</button>
                    </form>
                    <button type="button" @click="confirmingDelete = false" class="text-[10px] text-navy-400 hover:underline">Không</button>
                </div>
            </template>
        </div>
    </td>
</tr>
