<x-app-layout>
    @section('title', 'Chi tiết đề thi - ' . $exam->title)
    @section('page-title', 'Chi tiết đề thi')

    <div class="space-y-6">

        {{-- Header --}}
        <x-card padding="true" variant="featured">
            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <h2 class="text-2xl md:text-3xl font-bold text-navy-900 leading-tight">{{ $exam->title }}
                        </h2>
                        @php
                            $statusValue = $exam->status?->value;
                            $statusColors = [
                                'draft' => 'bg-surface-1 text-text-muted border-border-clean',
                                'published' => 'bg-teal-50 text-teal-700 border-teal-300',
                                'closed' => 'bg-red-50 text-red-700 border-red-300',
                            ];
                            $statusLabels = ['draft' => 'Nháp', 'published' => 'Đang mở', 'closed' => 'Đã đóng'];
                        @endphp
                        <span
                            class="px-2.5 py-0.5 rounded-full text-xs font-semibold border-[0.5px] {{ $statusColors[$statusValue] ?? '' }}">
                            {{ $statusLabels[$statusValue] ?? $statusValue }}
                        </span>
                    </div>
                    <p class="text-sm text-text-muted">{{ $exam->subject->name }}</p>
                    @if($exam->description)
                        <p class="text-sm text-text-muted mt-2">{{ $exam->description }}</p>
                    @endif
                </div>

                {{-- Actions --}}
                <div class="flex flex-wrap items-center gap-2">
                    @can('manageLecturer', $exam)
                        @php
                            $deleteConfirmMessage = $attemptCount > 0
                                ? 'Đề đã có sinh viên thi. Sẽ xoá mềm (lưu trữ). Tiếp tục?'
                                : 'Xoá vĩnh viễn đề thi này?';
                        @endphp
                        <a href="{{ route('lecturer.exams.edit', $exam->id) }}"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-border-clean rounded-[6px] text-xs font-medium text-text-muted hover:bg-surface-1 transition">
                            <x-ui-icon name="star" class="w-3.5 h-3.5" />
                            Sửa
                        </a>


                        @if($exam->status === \App\Enums\ExamStatus::Draft)
                            <form method="POST" action="{{ route('lecturer.exams.publish', $exam->id) }}" class="inline">
                                @csrf @method('PATCH')
                                <button type="submit"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-teal-600 rounded-[6px] text-xs font-medium text-white hover:bg-teal-700 transition">
                                    <x-ui-icon name="play" class="w-3.5 h-3.5" />
                                    Mở đề
                                </button>
                            </form>
                        @endif

                        @if($exam->status === \App\Enums\ExamStatus::Published)
                            <div x-data="{ confirming: false }" class="inline">
                                <button type="button" @click="confirming = true" x-show="!confirming"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-orange-500 rounded-[6px] text-xs font-medium text-white hover:bg-orange-600 transition">
                                    <x-ui-icon name="document-text" class="w-3.5 h-3.5" />
                                    Đóng đề
                                </button>
                                <div x-show="confirming" x-cloak class="inline-flex items-center gap-2 bg-orange-50 px-2 py-1 rounded border border-orange-200">
                                    <span class="text-[10px] text-orange-700 font-bold">Đóng đề?</span>
                                    <form method="POST" action="{{ route('lecturer.exams.close', $exam->id) }}" class="inline">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="text-[10px] font-bold text-orange-600 hover:underline">Có</button>
                                    </form>
                                    <button type="button" @click="confirming = false" class="text-[10px] text-navy-400 hover:underline">Không</button>
                                </div>
                            </div>
                        @endif

                        @if($exam->status === \App\Enums\ExamStatus::Closed)
                            <button type="button" onclick="document.getElementById('reopen-modal').classList.remove('hidden')"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-navy-900 rounded-[6px] text-xs font-medium text-white hover:bg-navy-950 transition">
                                <x-ui-icon name="play" class="w-3.5 h-3.5 rotate-180" />
                                Mở lại
                            </button>
                        @endif

                        <div x-data="{ confirming: false }" class="inline">
                            <button type="button" @click="confirming = true" x-show="!confirming"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-500 rounded-[6px] text-xs font-medium text-white hover:bg-red-600 transition">
                                <x-ui-icon name="x-mark" class="w-3.5 h-3.5" />
                                Xoá
                            </button>
                            <div x-show="confirming" x-cloak class="inline-flex flex-col items-center gap-1 bg-red-50 p-2 rounded border border-red-200 min-w-[150px]">
                                <span class="text-[10px] text-red-700 font-bold leading-tight text-center">{{ $deleteConfirmMessage }}</span>
                                <div class="flex gap-3 mt-1">
                                    <form method="POST" action="{{ route('lecturer.exams.destroy', $exam->id) }}" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-[10px] font-bold text-red-600 hover:underline">Xoá</button>
                                    </form>
                                    <button type="button" @click="confirming = false" class="text-[10px] text-navy-400 hover:underline">Hủy</button>
                                </div>
                            </div>
                        </div>
                    @endcan
                </div>
            </div>
        </x-card>

        {{-- Thông tin đề thi --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <x-card padding="true">
                <p class="text-xs font-medium text-text-muted mb-0.5">Thời gian làm bài</p>
                <p class="text-xl font-bold text-navy-900">{{ $exam->duration_minutes }} <span
                        class="text-xs text-text-muted">phút</span></p>
            </x-card>
            <x-card padding="true">
                <p class="text-xs font-medium text-text-muted mb-0.5">Số câu hỏi</p>
                <p class="text-xl font-bold text-navy-900">{{ $exam->questions->count() }} <span
                        class="text-xs text-text-muted">câu</span></p>
            </x-card>
            <x-card padding="true">
                <p class="text-xs font-medium text-text-muted mb-0.5">Tổng điểm</p>
                <p class="text-xl font-bold text-navy-900">{{ number_format($exam->total_points, 2) }}</p>
            </x-card>
            <x-card padding="true">
                <p class="text-xs font-medium text-text-muted mb-0.5">Điểm đạt</p>
                <p class="text-xl font-bold text-navy-900">{{ number_format($exam->pass_points, 2) }}</p>
            </x-card>
        </div>

        {{-- Thời gian & Cấu hình --}}
        <x-card padding="true">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h4 class="text-sm font-semibold text-navy-900 mb-3">Khung thời gian</h4>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-text-muted">Mở đề:</span>
                            <span
                                class="font-medium text-navy-900">{{ $exam->start_time?->format('H:i - d/m/Y') ?? 'Không giới hạn' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-text-muted">Đóng đề:</span>
                            <span
                                class="font-medium text-navy-900">{{ $exam->end_time?->format('H:i - d/m/Y') ?? 'Không giới hạn' }}</span>
                        </div>
                    </div>
                </div>
                <div>
                    <h4 class="text-sm font-semibold text-navy-900 mb-3">Cấu hình hiển thị kết quả</h4>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-text-muted">Cho xem điểm tổng:</span>
                            <span
                                class="font-medium {{ $exam->show_score_after_submit ? 'text-teal-600' : 'text-red-500' }}">
                                {{ $exam->show_score_after_submit ? 'Có' : 'Không' }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-text-muted">Cho xem chi tiết đáp án:</span>
                            <span
                                class="font-medium {{ $exam->show_answers_after_submit ? 'text-teal-600' : 'text-red-500' }}">
                                {{ $exam->show_answers_after_submit ? 'Có' : 'Không' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </x-card>

        {{-- Reopen reason nếu có --}}
        @if($exam->reopen_reason)
            <x-card padding="true">
                <div class="flex items-start gap-3">
                    <x-ui-icon name="document-text" class="w-5 h-5 text-navy-900" />
                    <div>
                        <p class="text-sm font-semibold text-navy-900 mb-1">Lý do mở lại đề thi</p>
                        <p class="text-sm text-text-muted">{{ $exam->reopen_reason }}</p>
                    </div>
                </div>
            </x-card>
        @endif

        {{-- Thống kê bài thi --}}
        <x-card padding="true">
            <x-slot name="header">
                <h3 class="text-lg font-semibold text-navy-900">Sinh viên đã thi
                    ({{ $completedCount }}/{{ $attemptCount }})</h3>
            </x-slot>

            @if($exam->attempts->isEmpty())
                <div class="text-center py-8">
                    <p class="text-text-muted text-sm">Chưa có sinh viên nào thi bài này.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-border-clean">
                                <th class="text-left py-2 px-3 font-semibold text-text-muted">Sinh viên</th>
                                <th class="text-center py-2 px-3 font-semibold text-text-muted">Trạng thái</th>
                                <th class="text-center py-2 px-3 font-semibold text-text-muted">Điểm</th>
                                <th class="text-center py-2 px-3 font-semibold text-text-muted">Thời gian bắt đầu</th>
                                <th class="text-center py-2 px-3 font-semibold text-text-muted">Thời gian nộp</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($exam->attempts as $attempt)
                                <tr class="border-b border-border-clean hover:bg-surface-0 transition-colors">
                                    <td class="py-2.5 px-3 font-medium text-navy-900">{{ $attempt->user->name ?? 'N/A' }}</td>
                                    <td class="py-2.5 px-3 text-center">
                                        @if($attempt->status === \App\Enums\ExamAttemptStatus::Completed)
                                            <span
                                                class="px-2 py-0.5 rounded-full text-xs font-semibold bg-teal-50 text-teal-700 border-[0.5px] border-teal-200">Đã
                                                nộp</span>
                                        @else
                                            <span
                                                class="px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border-[0.5px] border-amber-200">Đang
                                                thi</span>
                                        @endif
                                    </td>
                                    <td
                                        class="py-2.5 px-3 text-center font-semibold {{ ($attempt->total_score ?? 0) >= $exam->pass_points ? 'text-teal-600' : 'text-red-500' }}">
                                        {{ $attempt->total_score !== null ? number_format($attempt->total_score, 2) : '—' }}
                                    </td>
                                    <td class="py-2.5 px-3 text-center text-text-muted">
                                        {{ $attempt->started_at->format('H:i d/m') }}</td>
                                    <td class="py-2.5 px-3 text-center text-text-muted">
                                        {{ $attempt->completed_at?->format('H:i d/m') ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-card>

        {{-- Nút quay lại --}}
        <div class="flex justify-start">
            <a href="{{ route('lecturer.exams.index') }}" class="btn btn-secondary">
                Quay lại danh sách đề thi
            </a>
        </div>
    </div>
    @can('manageLecturer', $exam)
        {{-- Reopen Modal --}}
        <div id="reopen-modal"
            class="hidden fixed inset-0 bg-navy-950/60 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
            <x-card padding="true" class="w-full max-w-md">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-navy-900">Mở lại đề thi</h3>
                    <button onclick="document.getElementById('reopen-modal').classList.add('hidden')"
                        class="text-text-muted hover:text-navy-900">
                        <x-ui-icon name="x-mark" class="w-5 h-5" />
                    </button>
                </div>

                <form method="POST" action="{{ route('lecturer.exams.reopen', $exam->id) }}" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label class="block text-xs font-medium text-navy-900 mb-1">Lý do mở lại <span
                                class="text-red-500">*</span></label>
                        <textarea name="reopen_reason" rows="3" required
                            class="w-full border-border-clean focus:border-navy-600 focus:ring-blue-200 rounded-[6px] shadow-sm text-sm"
                            placeholder="Nhập lý do mở lại đề thi..."></textarea>
                        @error('reopen_reason')
                            <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="document.getElementById('reopen-modal').classList.add('hidden')"
                            class="px-3 py-1.5 text-xs font-medium text-text-muted hover:text-navy-900">Huỷ</button>
                        <button type="submit"
                            class="px-4 py-1.5 bg-navy-900 text-white text-xs font-medium rounded-[6px] hover:bg-navy-950 transition">
                            Xác nhận mở lại
                        </button>
                    </div>
                </form>
            </x-card>
        </div>
    @endcan
</x-app-layout>