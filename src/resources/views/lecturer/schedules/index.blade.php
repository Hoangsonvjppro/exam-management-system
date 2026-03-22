<x-app-layout>
    <div class="py-8 bg-[#F8FAFD] min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h2 class="text-[22px] font-bold text-[#1A3A6B] mb-1">Quản lý Lịch Thi</h2>
                    <p class="text-[13px] text-[#6B7C99]">Tất cả ca thi bạn đã lên lịch.</p>
                </div>
            </div>

            @if(session('success'))
            <div class="mb-4 px-4 py-3 bg-[#ECFDF5] border border-[#A7F3D0] text-[#065F46] text-[13px] rounded-lg">{{ session('success') }}</div>
            @endif

            <div class="bg-white rounded-[10px] border border-[#D6E2F0] overflow-hidden shadow-sm">
                @if($schedules->isEmpty())
                <div class="text-center py-16">
                    <div class="w-16 h-16 bg-[#F4F7FC] rounded-full flex items-center justify-center mx-auto mb-4 border border-[#D6E2F0]">
                        <svg class="w-8 h-8 text-[#6B7C99]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <h4 class="text-[15px] font-bold text-[#1A3A6B] mb-2">Chưa có lịch thi nào</h4>
                    <p class="text-[13px] text-[#6B7C99]">Tạo lịch thi từ trang chi tiết đề thi.</p>
                </div>
                @else
                <table class="w-full text-[13px]">
                    <thead>
                        <tr class="bg-[#F4F7FC] text-[#1A3A6B]">
                            <th class="px-5 py-3 text-left font-semibold">Đề thi</th>
                            <th class="px-5 py-3 text-left font-semibold">Lớp</th>
                            <th class="px-5 py-3 text-center font-semibold">Ngày</th>
                            <th class="px-5 py-3 text-center font-semibold">Giờ</th>
                            <th class="px-5 py-3 text-center font-semibold">SV</th>
                            <th class="px-5 py-3 text-center font-semibold">Trạng thái</th>
                            <th class="px-5 py-3 text-center font-semibold">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($schedules as $schedule)
                        <tr class="border-t border-[#EBF2FA] hover:bg-[#F8FAFD] transition-colors">
                            <td class="px-5 py-3.5 font-medium text-[#1A3A6B]">{{ $schedule->exam->title }}</td>
                            <td class="px-5 py-3.5 text-[#6B7C99]">{{ $schedule->exam->courseSection->name ?? '—' }}</td>
                            <td class="px-5 py-3.5 text-center text-[#374151]">{{ $schedule->exam_date->format('d/m/Y') }}</td>
                            <td class="px-5 py-3.5 text-center text-[#374151]">{{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} – {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}</td>
                            <td class="px-5 py-3.5 text-center text-[#374151]">{{ $schedule->assigned_count }}</td>
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
                                <span class="inline-block text-[11px] font-medium px-2.5 py-0.5 rounded-full {{ $cls }}">{{ $label }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('lecturer.schedules.edit', $schedule->id) }}" class="text-[#185FA5] hover:underline text-[12px]">Sửa</a>
                                    <form method="POST" action="{{ route('lecturer.schedules.assign-students', $schedule->id) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-[#065F46] hover:underline text-[12px]">Phân SV</button>
                                    </form>
                                    <form method="POST" action="{{ route('lecturer.schedules.destroy', $schedule->id) }}" class="inline" onsubmit="return confirm('Xoá lịch thi này?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-[#DC2626] hover:underline text-[12px]">Xoá</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>