<x-app-layout>
    @section('title', 'Quản lý khiếu nại — EMS')
    @section('page-title', 'Khiếu nại')

    <div class="space-y-6" x-data="lecturerComplaints()">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-[28px] font-bold text-navy-900 leading-tight">Quản lý khiếu nại</h2>
                <p class="text-sm font-medium text-text-muted mt-1">Xem xét và giải quyết các phản hồi về điểm số của sinh viên.</p>
            </div>
        </div>

        <x-card padding="true">
            @if($complaints->isEmpty())
            <div class="text-center py-16 bg-surface-0 border-[0.5px] border-border-clean border-dashed rounded-[8px]">
                <svg class="w-14 h-14 text-blue-100 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                </svg>
                <p class="text-navy-900 font-semibold text-base mb-2">Không có khiếu nại nào cần xử lý</p>
            </div>
            @else
            <div class="overflow-x-auto border border-border-clean rounded-[8px]">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface-1 border-b border-border-clean">
                            <th class="py-3 px-4 text-[11px] font-semibold uppercase tracking-wider text-text-muted">Sinh viên</th>
                            <th class="py-3 px-4 text-[11px] font-semibold uppercase tracking-wider text-text-muted">Bài thi / Lớp</th>
                            <th class="py-3 px-4 text-[11px] font-semibold uppercase tracking-wider text-text-muted text-center">Số câu đúng</th>
                            <th class="py-3 px-4 text-[11px] font-semibold uppercase tracking-wider text-text-muted text-center">Trạng thái</th>
                            <th class="py-3 px-4 text-[11px] font-semibold uppercase tracking-wider text-text-muted">Lý do</th>
                            <th class="py-3 px-4 text-[11px] font-semibold uppercase tracking-wider text-text-muted text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-clean/70">
                        @foreach($complaints as $complaint)
                        <tr class="hover:bg-surface-0 transition-colors">
                            <td class="py-3 px-4 align-top">
                                <p class="text-[13px] font-bold text-navy-900">{{ $complaint->student->name }}</p>
                                <p class="text-[11px] text-text-muted mt-0.5">{{ $complaint->student->student_id ?? $complaint->student->email }}</p>
                            </td>
                            <td class="py-3 px-4 align-top">
                                <p class="text-[13px] font-semibold text-navy-900 leading-snug">{{ $complaint->schedule->exam->title ?? 'N/A' }}</p>
                                <p class="text-[11px] text-text-muted mt-1">{{ $complaint->section->code ?? 'N/A' }}</p>
                                <p class="text-[10px] text-text-muted mt-0.5">{{ $complaint->created_at->format('H:i d/m/Y') }}</p>
                            </td>
                            <td class="py-3 px-4 align-top text-center">
                                @php
                                $attemptCorrectCount = $complaint->attempt->correct_count ?? 0;
                                $attemptTotalQuestions = $complaint->schedule->exam->questions()->count();
                                @endphp
                                <span class="text-[14px] font-bold text-navy-900">{{ $attemptCorrectCount }}/{{ $attemptTotalQuestions }}</span>
                                <span class="block text-[11px] text-text-muted mt-0.5">Điểm: {{ number_format($complaint->current_score, 1) }}/10</span>
                                @if($complaint->updated_score)
                                <span class="block text-[12px] font-bold text-teal-600 mt-1">-> {{ number_format($complaint->updated_score, 1) }}/10</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 align-top text-center">
                                @php
                                $statusConfig = match($complaint->status) {
                                'pending' => ['bg-yellow-50 text-yellow-700 border-yellow-200', 'Đang chờ'],
                                'reviewing' => ['bg-blue-50 text-blue-700 border-blue-200', 'Đang xem'],
                                'resolved' => ['bg-teal-50 text-teal-700 border-teal-200', 'Đã duyệt'],
                                'rejected' => ['bg-red-50 text-red-700 border-red-200', 'Từ chối'],
                                default => ['bg-gray-50 text-gray-500 border-gray-200', 'N/A']
                                };
                                @endphp
                                <span class="inline-flex items-center text-[10px] font-bold uppercase rounded-[4px] px-2 py-1 border {{ $statusConfig[0] }}">
                                    {{ $statusConfig[1] }}
                                </span>
                            </td>
                            <td class="py-3 px-4 align-top max-w-[250px]">
                                <div class="text-[12px] text-text-muted mb-2">
                                    <span class="break-words line-clamp-3" title="{{ $complaint->reason }}">{{ $complaint->reason }}</span>
                                </div>
                                @if($complaint->reviewer_note)
                                <div class="text-[11px] bg-blue-50/50 p-2 rounded border border-blue-100/50">
                                    <span class="text-blue-900 break-words line-clamp-2" title="{{ $complaint->reviewer_note }}">{{ $complaint->reviewer_note }}</span>
                                </div>
                                @endif
                            </td>
                            <td class="py-3 px-4 align-top text-center">
                                @if(in_array($complaint->status, ['pending', 'reviewing']))
                                <x-button type="button" variant="primary" size="sm"
                                    @click="openReviewModal({{ $complaint->id }}, '{{ addslashes($complaint->student->name) }}', '{{ addslashes($complaint->reason) }}', {{ $attemptCorrectCount }}, {{ $attemptTotalQuestions }})">
                                    Xử lý
                                </x-button>
                                @else
                                <span class="text-[11px] text-text-muted font-medium">Hoàn tất lúc<br>{{ $complaint->resolved_at?->format('H:i d/m') }}</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $complaints->links() }}
            </div>
            @endif
        </x-card>

        {{-- Modal Xử lý khiếu nại --}}
        <x-modal name="review-modal" maxWidth="lg">
            <div class="p-6 md:p-8">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-[20px] font-bold text-navy-900">Xử lý khiếu nại</h3>
                    <button @click="$dispatch('close-modal', 'review-modal')" class="text-text-muted hover:text-navy-900 transition-colors">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="space-y-4">
                    <div class="p-4 bg-surface-1 border border-border-clean rounded-lg space-y-3">
                        <div>
                            <span class="block text-[11px] text-text-muted font-medium uppercase tracking-wider mb-1">Sinh viên</span>
                            <span class="text-[14px] font-bold text-navy-900" x-text="reviewStudentName"></span>
                        </div>
                        <div>
                            <span class="block text-[11px] text-text-muted font-medium uppercase tracking-wider mb-1">Lý do khiếu nại</span>
                            <span class="text-[13px] text-navy-900 leading-relaxed break-words" x-text="reviewReason"></span>
                        </div>
                        <div>
                            <span class="block text-[11px] text-text-muted font-medium uppercase tracking-wider mb-1">Số câu đúng hiện tại</span>
                            <span class="text-[14px] font-bold text-navy-900" x-text="reviewCurrentCorrectCount + '/' + reviewTotalQuestions"></span>
                        </div>
                        <div>
                            <span class="block text-[11px] text-text-muted font-medium uppercase tracking-wider mb-1">Điểm hiện tại</span>
                            <span class="text-[14px] font-bold text-red-500" x-text="currentScoreDisplay"></span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mb-2">
                        <label class="relative flex items-center justify-center gap-2 p-3 border-2 rounded-[8px] cursor-pointer transition-all"
                            :class="resolutionStatus === 'resolved' ? 'border-teal-500 bg-teal-50/30' : 'border-border-clean hover:bg-surface-0'">
                            <input type="radio" name="status" value="resolved" x-model="resolutionStatus" class="sr-only">
                            <div class="w-4 h-4 rounded-full border border-teal-500 flex items-center justify-center" :class="resolutionStatus === 'resolved' ? 'bg-teal-500' : 'bg-white'">
                                <svg x-show="resolutionStatus === 'resolved'" class="w-2.5 h-2.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <span class="text-[13px] font-bold text-teal-700">Chấp nhận</span>
                        </label>
                        <label class="relative flex items-center justify-center gap-2 p-3 border-2 rounded-[8px] cursor-pointer transition-all"
                            :class="resolutionStatus === 'rejected' ? 'border-red-500 bg-red-50/30' : 'border-border-clean hover:bg-surface-0'">
                            <input type="radio" name="status" value="rejected" x-model="resolutionStatus" class="sr-only">
                            <div class="w-4 h-4 rounded-full border border-red-500 flex items-center justify-center" :class="resolutionStatus === 'rejected' ? 'bg-red-500' : 'bg-white'">
                                <svg x-show="resolutionStatus === 'rejected'" class="w-2.5 h-2.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </div>
                            <span class="text-[13px] font-bold text-red-700">Từ chối</span>
                        </label>
                    </div>

                    <div x-show="resolutionStatus === 'resolved'" x-transition.opacity.duration.200ms class="space-y-3">
                        <div>
                            <label for="updatedCorrectCount" class="block text-[12px] font-semibold text-navy-900 mb-1.5">Số câu đúng mới <span class="text-red-500">*</span></label>
                            <input id="updatedCorrectCount" type="number" step="1" min="0" :max="reviewTotalQuestions" x-model="updatedCorrectCount" :placeholder="'Nhập số từ 0 đến ' + reviewTotalQuestions" class="w-full h-11 px-3 bg-white border-[1.5px] border-border-clean rounded-[6px] text-[14px] text-navy-900 placeholder:text-text-muted focus:border-blue-400 focus:ring-4 focus:ring-blue-100/50 transition-all outline-none" />
                            <p class="text-[11px] text-text-muted mt-1">Điểm sẽ tự tính: <strong class="text-navy-900" x-text="previewScore"></strong>/10</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[12px] font-semibold text-navy-900 mb-1.5">Ghi chú phản hồi <span class="text-red-500">*</span></label>
                        <textarea x-model="reviewerNote" rows="3" placeholder="Nhập giải thích cho quyết định của bạn..."
                            class="w-full p-3 bg-white border-[1.5px] border-border-clean rounded-[6px] text-[13px] text-navy-900 placeholder:text-text-muted focus:border-blue-400 focus:ring-4 focus:ring-blue-100/50 transition-all outline-none resize-y"></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <x-button type="button" variant="ghost" @click="$dispatch('close-modal', 'review-modal')">Hủy</x-button>
                        <x-button type="button" variant="primary" @click="submitReview()" x-bind:disabled="isSubmitting">
                            <span x-show="!isSubmitting">Lưu kết quả</span>
                            <span x-show="isSubmitting">Đang lưu...</span>
                        </x-button>
                    </div>
                </div>
            </div>
        </x-modal>
    </div>

    @push('scripts')
    @vite(['resources/js/pages/lecturer/complaints-index.js'])
    @endpush
</x-app-layout>