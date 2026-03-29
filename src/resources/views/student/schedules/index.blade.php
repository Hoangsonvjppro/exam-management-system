<x-app-layout>
    @section('title', 'Lịch Thi - Sinh viên')
    @section('page-title', 'Lịch Thi')

    <div class="py-8 bg-[#F8FAFD] min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-[#1A3A6B] mb-1">Lịch Thi Của Tôi</h2>
                <p class="text-sm text-[#6B7C99]">Tất cả ca thi từ các lớp học phần bạn đang theo học.</p>
            </div>

            <div class="bg-white rounded-[10px] border border-[#D6E2F0] overflow-hidden shadow-sm">
                @if($schedules->isEmpty())
                <div class="text-center py-16">
                    <x-ui-icon name="calendar" class="w-12 h-12 text-blue-100 mx-auto mb-4" />
                    <h4 class="text-lg font-bold text-[#1A3A6B] mb-2">Chưa có lịch thi nào</h4>
                    <p class="text-sm text-[#6B7C99]">Bạn chưa có ca thi nào được lên lịch. Hãy kiểm tra lại lớp học phần.</p>
                </div>
                @else
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-[#F4F7FC] text-[#1A3A6B]">
                            <th class="px-5 py-4 text-left font-bold uppercase text-[10px] tracking-wider">Đề thi</th>
                            <th class="px-5 py-4 text-left font-bold uppercase text-[10px] tracking-wider">Lớp</th>
                            <th class="px-5 py-4 text-center font-bold uppercase text-[10px] tracking-wider">Ngày</th>
                            <th class="px-5 py-4 text-center font-bold uppercase text-[10px] tracking-wider">Giờ</th>
                            <th class="px-5 py-4 text-center font-bold uppercase text-[10px] tracking-wider">Trạng thái</th>
                            <th class="px-5 py-4 text-center font-bold uppercase text-[10px] tracking-wider">Thao tác</th>
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
                            <td class="px-5 py-4 font-bold text-[#1A3A6B]">{{ $schedule->exam->title }}</td>
                            <td class="px-5 py-4 text-gray-500 font-medium">{{ $schedule->courseSection->name ?? '—' }}</td>
                            <td class="px-5 py-4 text-center font-semibold text-gray-700">{{ $schedule->exam_date->format('d/m/Y') }}</td>
                            <td class="px-5 py-4 text-center text-gray-700 flex flex-col justify-center">
                                <span class="font-bold text-[#1A3A6B]">{{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }}</span>
                                <span class="text-[10px] text-gray-400 uppercase font-bold">{{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}</span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="inline-block text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full border {{ $statusCls }}">{{ $statusLabel }}</span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                @if($isOpen)
                                <a href="{{ route('student.exams.show', $schedule->id) }}" class="inline-flex items-center gap-2 bg-[#1A3A6B] text-white px-4 py-2 rounded-lg text-xs font-bold hover:bg-[#0F2A53] transition-all shadow-sm">
                                    <x-ui-icon name="arrow-right-on-rectangle" class="w-4 h-4" />
                                    Vào thi
                                </a>
                                @elseif($statusLabel === 'Sắp tới')
                                <span class="text-xs text-[#6B7C99] font-medium italic">Chưa đến giờ</span>
                                @elseif($statusLabel === 'Hoàn thành' || $statusLabel === 'Đã kết thúc')
                                <a href="{{ route('student.exams.result', $schedule->id) }}" class="text-xs text-[#185FA5] hover:underline font-bold flex items-center justify-center gap-1">
                                    <x-ui-icon name="chart-bar" class="w-3.5 h-3.5" />
                                    Kết quả
                                </a>
                                @else
                                <span class="text-xs text-[#6B7C99]">—</span>
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
