<x-app-layout>
    <div class="py-8 bg-[#F8FAFD] min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-[#1A3A6B] mb-1">Quản lý Đề Thi</h2>
                    <p class="text-sm text-[#6B7C99]">Tất cả đề thi của bạn trong hệ thống.</p>
                </div>
                <a href="{{ route('lecturer.exams.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-[#1A3A6B] border border-transparent rounded-lg font-bold text-white text-xs uppercase tracking-wider hover:bg-[#142d54] transition shadow-sm">
                    <x-ui-icon name="plus" class="w-4 h-4" />
                    Thêm đề thi mới
                </a>
            </div>


            <div class="bg-white rounded-[10px] border border-[#D6E2F0] overflow-hidden shadow-sm">
                @if($exams->isEmpty())
                <div class="text-center py-20 bg-surface-0 border-[0.5px] border-border-clean border-dashed rounded-xl">
                    <x-ui-icon name="document-text" class="mx-auto w-12 h-12 text-blue-100 mb-4" />
                    <h4 class="text-lg font-bold text-[#1A3A6B] mb-2">Chưa có đề thi nào</h4>
                    <p class="text-sm text-[#6B7C99]">Nhấn "Thêm đề thi mới" để bắt đầu xây dựng ngân hàng đề của bạn.</p>
                </div>
                @else
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-[#F4F7FC] text-[#1A3A6B]">
                            <th class="px-5 py-4 text-left font-bold uppercase text-[10px] tracking-wider">Tên đề thi</th>
                            <th class="px-5 py-4 text-left font-bold uppercase text-[10px] tracking-wider">Môn học</th>
                            <th class="px-5 py-4 text-center font-bold uppercase text-[10px] tracking-wider">Loại</th>
                            <th class="px-5 py-4 text-center font-bold uppercase text-[10px] tracking-wider">Thời gian</th>
                            <th class="px-5 py-4 text-center font-bold uppercase text-[10px] tracking-wider">Trạng thái</th>
                            <th class="px-5 py-4 text-center font-bold uppercase text-[10px] tracking-wider">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($exams as $exam)
                        @php
                        $examStatusValue = $exam->status?->value;
                        @endphp
                        <tr class="border-t border-[#EBF2FA] hover:bg-[#F8FAFD] transition-colors">
                            <td class="px-5 py-4 font-bold text-[#1A3A6B]">{{ $exam->title }}</td>
                            <td class="px-5 py-4 text-gray-500 font-medium">
                                <span class="font-bold text-[#1A3A6B]">{{ $exam->subject->code ?? '—' }}</span>
                                <span class="text-xs ml-1 opacity-70">{{ $exam->subject->name ?? '' }}</span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="inline-block text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full border {{ $exam->exam_type === \App\Enums\ExamType::Official ? 'bg-blue-50 text-blue-800 border-blue-200' : 'bg-amber-50 text-amber-800 border-amber-200' }}">
                                    {{ $exam->exam_type === \App\Enums\ExamType::Official ? 'Chính thức' : 'Luyện tập' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-center font-bold text-gray-700">{{ $exam->duration_minutes }} <span class="text-[10px] text-gray-400">PHÚT</span></td>
                            <td class="px-5 py-4 text-center">
                                <span class="inline-block text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full border {{ $exam->status === \App\Enums\ExamStatus::Published ? 'bg-teal-50 text-teal-800 border-teal-200' : 'bg-gray-50 text-gray-600 border-gray-200' }}">
                                    {{ $examStatusValue === 'published' ? 'Đã phát hành' : ucfirst((string) $examStatusValue) }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <a href="{{ route('lecturer.exams.show', $exam->id) }}" class="inline-flex items-center gap-1.5 text-[#185FA5] hover:text-[#1A3A6B] text-xs font-bold transition-colors">
                                    <x-ui-icon name="eye" class="w-4 h-4" />
                                    Chi tiết
                                </a>
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