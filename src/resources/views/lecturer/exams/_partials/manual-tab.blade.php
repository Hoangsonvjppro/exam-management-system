{{-- ═══ TAB 1: CHỌN THỦ CÔNG (AJAX) ═══ --}}
<div id="panel-manual" class="flex-1 flex flex-col">
    <div class="bg-white rounded-[10px] border-[0.5px] border-[#D6E2F0] flex flex-col h-full shadow-sm">
        {{-- Header --}}
        <div class="p-5 border-b border-[#EBF2FA] bg-[#F8FAFD] rounded-t-[10px]">
            <div class="flex justify-between items-center mb-3">
                <div>
                    <h3 class="text-[16px] font-bold text-[#1A3A6B]">Chọn câu hỏi cho đề thi</h3>
                    <p class="text-[12.5px] text-[#6B7C99] mt-1">Tìm kiếm, lọc và chọn câu hỏi từ ngân hàng môn học.</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="badge s-upcoming" id="manual-counter">Đã chọn: <strong id="selectedCount">0</strong> câu</div>
                    <button type="button" class="btn btn-ghost btn-sm" onclick="openQuickQuestionModal()">+ Thêm câu hỏi</button>
                    <button type="submit" class="btn btn-primary" id="btn-submit-manual">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        Lưu Đề Thi
                    </button>
                </div>
            </div>

            {{-- Tổng số câu --}}
            <div class="flex items-center gap-3 mb-3 p-2.5 bg-white rounded-lg border border-[#EBF2FA]">
                <span class="text-[13px] text-[#1A3A6B] font-semibold" id="manual-total-display">Tổng: 0 câu</span>
            </div>

            {{-- Filter bar --}}
            <div class="filter-bar">
                <input type="text" id="manual-search" class="ca-input search-input" placeholder="🔍 Tìm kiếm theo từ khóa..." oninput="debouncedSearchQuestions()">
                <select id="manual-chapter-filter" class="ca-select" onchange="searchQuestions(1)" style="min-width:150px;flex:1">
                    <option value="">Tất cả chương</option>
                </select>
                <select id="manual-difficulty-filter" class="ca-select" onchange="searchQuestions(1)" style="min-width:130px;flex:0.7">
                    <option value="">Tất cả độ khó</option>
                    @foreach($difficulties as $diff)
                    <option value="{{ $diff->code }}">{{ $diff->name }}</option>
                    @endforeach
                </select>
                <button type="button" class="btn btn-ghost btn-sm" id="collapse-all-btn" onclick="collapseAllPreviews()" style="display:none">
                    ↕ Thu gọn
                </button>
            </div>
        </div>

        @error('question_ids')
        <div class="px-5 py-3 bg-[#FEF2F2] border-b border-[#FCA5A5] text-[#991B1B] text-[13px] font-medium">{{ $message }}</div>
        @enderror

        {{-- Question list (AJAX rendered) --}}
        <div class="flex-1 overflow-y-auto px-5 py-4 max-h-[700px] min-h-[400px]" id="questions-container">
            <div id="questions-list"></div>

            {{-- Loading spinner --}}
            <div id="questions-loading" class="flex items-center justify-center py-8" style="display:none">
                <div class="spinner"></div>
                <span class="ml-3 text-[13px] text-[#6B7C99]">Đang tải câu hỏi...</span>
            </div>

            {{-- Empty state --}}
            <div id="questions-empty" class="h-full flex flex-col items-center justify-center text-center py-12" style="display:none">
                <div class="w-16 h-16 bg-[#F4F7FC] rounded-full flex items-center justify-center mb-4 border border-[#D6E2F0]">
                    <svg class="w-8 h-8 text-[#6B7C99]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                </div>
                <h4 class="text-[15px] font-bold text-[#1A3A6B] mb-2">Không tìm thấy câu hỏi</h4>
                <p class="text-[13px] text-[#6B7C99] max-w-sm" id="questions-empty-text">Vui lòng chọn môn học để xem danh sách câu hỏi.</p>
            </div>

            {{-- Load More button --}}
            <button type="button" id="load-more-btn" class="load-more-btn" onclick="loadMoreQuestions()" style="display:none">
                Tải thêm câu hỏi...
            </button>

            {{-- Page info --}}
            <p id="page-info" class="text-center text-[11px] text-[#6B7C99] mt-2" style="display:none"></p>
        </div>
    </div>
</div>
