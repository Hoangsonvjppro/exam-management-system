{{-- Modal thêm câu hỏi nhanh --}}
<x-modal name="quick-question-modal" maxWidth="xl">
    <div class="p-6 md:p-7">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="text-[18px] font-bold text-[#1A3A6B]">Thêm câu hỏi mới</h3>
                <p class="text-[12px] text-[#6B7C99] mt-1">Câu hỏi sẽ tự động được thêm vào danh sách chọn.</p>
            </div>
            <button type="button" class="text-[#6B7C99] hover:text-[#1A3A6B]" @click="$dispatch('close-modal', 'quick-question-modal')">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <form id="quick-question-form" onsubmit="submitQuickQuestionForm(event)" class="space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[12px] font-semibold text-[#1A3A6B] mb-1">Môn học <span class="text-[#DC2626]">*</span></label>
                    <select id="quick-question-subject" name="subject_id" required class="ca-select">
                        <option value="">-- Chọn môn học --</option>
                        @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[12px] font-semibold text-[#1A3A6B] mb-1">Loại câu hỏi <span class="text-[#DC2626]">*</span></label>
                    <select name="question_type_id" required class="ca-select">
                        <option value="">-- Chọn loại --</option>
                        @foreach($quickQuestionTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[12px] font-semibold text-[#1A3A6B] mb-1">Mức độ <span class="text-[#DC2626]">*</span></label>
                    <select name="difficulty" required class="ca-select">
                        @foreach($difficulties as $diff)
                        <option value="{{ $diff->code }}">{{ $diff->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[12px] font-semibold text-[#1A3A6B] mb-1">Trạng thái</label>
                    <input type="text" value="Đã duyệt (auto)" readonly class="ca-input bg-[#F4F7FC] cursor-not-allowed">
                    <input type="hidden" name="status" value="approved">
                </div>
            </div>
            <div>
                <label class="block text-[12px] font-semibold text-[#1A3A6B] mb-1">Nội dung câu hỏi <span class="text-[#DC2626]">*</span></label>
                <textarea name="content" rows="4" required minlength="5" class="ca-input" placeholder="Nhập nội dung câu hỏi..."></textarea>
            </div>
            <div>
                <label class="block text-[12px] font-semibold text-[#1A3A6B] mb-1">Giải thích (tuỳ chọn)</label>
                <textarea name="explanation" rows="3" class="ca-input" placeholder="Giải thích đáp án..."></textarea>
            </div>
            <div id="quick-question-errors" class="text-[12px] text-[#DC2626]" style="display:none"></div>
            <div class="flex items-center justify-end gap-3 pt-3 border-t border-[#EBF2FA]">
                <button type="button" class="btn btn-ghost" @click="$dispatch('close-modal', 'quick-question-modal')">Huỷ</button>
                <button type="submit" id="quick-question-submit" class="btn btn-primary">Lưu và thêm vào đề</button>
            </div>
        </form>
    </div>
</x-modal>
