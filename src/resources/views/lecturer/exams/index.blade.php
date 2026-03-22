<x-app-layout>
    <div class="py-8 bg-[#F8FAFD] min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h2 class="text-[22px] font-bold text-[#1A3A6B] mb-1">Quản lý Đề Thi</h2>
                    <p class="text-[13px] text-[#6B7C99]">Tất cả đề thi của bạn.</p>
                </div>
            </div>

            @if(session('success'))
            <div class="mb-4 px-4 py-3 bg-[#ECFDF5] border border-[#A7F3D0] text-[#065F46] text-[13px] rounded-lg">{{ session('success') }}</div>
            @endif

            <div class="bg-white rounded-[10px] border border-[#D6E2F0] overflow-hidden shadow-sm">
                @if($exams->isEmpty())
                <div class="text-center py-16">
                    <div class="w-16 h-16 bg-[#F4F7FC] rounded-full flex items-center justify-center mx-auto mb-4 border border-[#D6E2F0]">
                        <svg class="w-8 h-8 text-[#6B7C99]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    </div>
                    <h4 class="text-[15px] font-bold text-[#1A3A6B] mb-2">Chưa có đề thi nào</h4>
                    <p class="text-[13px] text-[#6B7C99]">Tạo đề thi từ trang lớp học phần.</p>
                </div>
                @else
                <table class="w-full text-[13px]">
                    <thead>
                        <tr class="bg-[#F4F7FC] text-[#1A3A6B]">
                            <th class="px-5 py-3 text-left font-semibold">Tên đề thi</th>
                            <th class="px-5 py-3 text-left font-semibold">Lớp học phần</th>
                            <th class="px-5 py-3 text-center font-semibold">Loại</th>
                            <th class="px-5 py-3 text-center font-semibold">Thời gian</th>
                            <th class="px-5 py-3 text-center font-semibold">Trạng thái</th>
                            <th class="px-5 py-3 text-center font-semibold">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($exams as $exam)
                        <tr class="border-t border-[#EBF2FA] hover:bg-[#F8FAFD] transition-colors">
                            <td class="px-5 py-3.5 font-medium text-[#1A3A6B]">{{ $exam->title }}</td>
                            <td class="px-5 py-3.5 text-[#6B7C99]">{{ $exam->courseSection->name ?? '—' }}</td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="inline-block text-[11px] font-medium px-2.5 py-0.5 rounded-full {{ $exam->exam_type === 'official' ? 'bg-[#EBF2FA] text-[#1A3A6B]' : 'bg-[#FEF3C7] text-[#92400E]' }}">
                                    {{ $exam->exam_type === 'official' ? 'Chính thức' : 'Luyện tập' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-center text-[#6B7C99]">{{ $exam->duration_minutes }} phút</td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="inline-block text-[11px] font-medium px-2.5 py-0.5 rounded-full {{ $exam->status === 'published' ? 'bg-[#ECFDF5] text-[#065F46]' : 'bg-[#F3F4F6] text-[#6B7C99]' }}">
                                    {{ $exam->status === 'published' ? 'Đã phát hành' : ucfirst($exam->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <a href="{{ route('lecturer.exams.show', $exam->id) }}" class="text-[#185FA5] hover:underline text-[12px] font-medium">Xem chi tiết</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="px-5 py-3 border-t border-[#EBF2FA]">
                    {{ $exams->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>