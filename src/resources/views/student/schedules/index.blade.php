<x-app-layout>
    @section('title', 'Lịch Thi - Sinh viên')
    @section('page-title', 'Lịch Thi')

    <div class="py-8 bg-[#F8FAFD] min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6">
                <h2 class="text-[22px] font-bold text-[#1A3A6B] mb-1">Lịch Thi Của Tôi</h2>
                <p class="text-[13px] text-[#6B7C99]">Tất cả ca thi từ các lớp học phần bạn đang theo học.</p>
            </div>

            <div class="bg-white rounded-[10px] border border-[#D6E2F0] overflow-hidden shadow-sm">
                @if($schedules->isEmpty())
                <div class="text-center py-16">
                    <div class="w-16 h-16 bg-[#F4F7FC] rounded-full flex items-center justify-center mx-auto mb-4 border border-[#D6E2F0]">
                        <svg class="w-8 h-8 text-[#6B7C99]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h4 class="text-[15px] font-bold text-[#1A3A6B] mb-2">Chưa có lịch thi nào</h4>
                    <p class="text-[13px] text-[#6B7C99]">Bạn chưa có ca thi nào được lên lịch. Hãy kiểm tra lại lớp học phần.</p>
                </div>
                @else
                <table class="w-full text-[13px]">
                    <thead>
                        <tr class="bg-[#F4F7FC] text-[#1A3A6B]">
                            <th class="px-5 py-3 text-left font-semibold">Đề thi</th>
                            <th class="px-5 py-3 text-left font-semibold">Lớp</th>
                            <th class="px-5 py-3 text-center font-semibold">Ngày</th>
                            <th class="px-5 py-3 text-center font-semibold">Giờ</th>
                            <th class="px-5 py-3 text-center font-semibold">Thời lượng</th>
                            <th class="px-5 py-3 text-center font-semibold">Trạng thái</th>
                            <th class="px-5 py-3 text-center font-semibold">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($schedules as $schedule)
                        @php
                            $now = now();
                            $examDate = $schedule->exam_date->format('Y-m-d');
                            $startDt = \Carbon\Carbon::parse($examDate . ' ' . $schedule->start_time);
                            $endDt   = \Carbon\Carbon::parse($examDate . ' ' . $schedule->end_time);

                            if ($now->lt($startDt)) {
                                $statusLabel = 'Sắp tới';
                                $statusCls   = 'bg-[#EBF2FA] text-[#1A3A6B]';
                                $isOpen      = false;
                            } elseif ($now->between($startDt, $endDt)) {
                                $statusLabel = 'Đang mở';
                                $statusCls   = 'bg-[#ECFDF5] text-[#065F46]';
                                $isOpen      = true;
                            } else {
                                $statusLabel = 'Đã kết thúc';
                                $statusCls   = 'bg-[#F3F4F6] text-[#6B7C99]';
                                $isOpen      = false;
                            }

                            // Override by schedule status
                            if ($schedule->status === 'cancelled') {
                                $statusLabel = 'Đã hủy';
                                $statusCls   = 'bg-[#FEF2F2] text-[#991B1B]';
                                $isOpen      = false;
                            } elseif ($schedule->status === 'completed') {
                                $statusLabel = 'Hoàn thành';
                                $statusCls   = 'bg-[#ECFDF5] text-[#065F46]';
                                $isOpen      = false;
                            }
                        @endphp
                        <tr class="border-t border-[#EBF2FA] hover:bg-[#F8FAFD] transition-colors">
                            <td class="px-5 py-3.5 font-medium text-[#1A3A6B]">{{ $schedule->exam->title }}</td>
                            <td class="px-5 py-3.5 text-[#6B7C99]">{{ $schedule->courseSection->name ?? '—' }}</td>
                            <td class="px-5 py-3.5 text-center text-[#374151]">{{ $schedule->exam_date->format('d/m/Y') }}</td>
                            <td class="px-5 py-3.5 text-center text-[#374151]">{{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} – {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}</td>
                            <td class="px-5 py-3.5 text-center text-[#374151]">
                                @if($schedule->exam->duration_minutes >= 60)
                                    {{ floor($schedule->exam->duration_minutes / 60) }}h{{ $schedule->exam->duration_minutes % 60 > 0 ? $schedule->exam->duration_minutes % 60 . 'p' : '' }}
                                @else
                                    {{ $schedule->exam->duration_minutes }} phút
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="inline-block text-[11px] font-medium px-2.5 py-0.5 rounded-full {{ $statusCls }}">{{ $statusLabel }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                @if($isOpen)
                                <a href="{{ route('student.exams.show', $schedule->id) }}" class="inline-flex items-center gap-1.5 bg-[#1A3A6B] text-white px-3 py-1.5 rounded-lg text-[12px] font-semibold hover:bg-[#0F2A53] transition-all shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                    </svg>
                                    Vào phòng thi
                                </a>
                                @elseif($statusLabel === 'Sắp tới')
                                <span class="text-[12px] text-[#6B7C99]">Chưa đến giờ</span>
                                @elseif($statusLabel === 'Hoàn thành' || $statusLabel === 'Đã kết thúc')
                                <a href="{{ route('student.exams.result', $schedule->id) }}" class="text-[12px] text-[#185FA5] hover:underline font-medium">Xem kết quả</a>
                                @else
                                <span class="text-[12px] text-[#6B7C99]">—</span>
                                @endif
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
