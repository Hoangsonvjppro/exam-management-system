{{-- ═══ TAB 2: SINH TỪ MA TRẬN ═══ --}}
<div id="panel-matrix" class="flex-1 flex-col" style="display: none;">
    <div class="bg-white rounded-[10px] border-[0.5px] border-[#D6E2F0] flex flex-col shadow-sm">
        {{-- Header --}}
        <div class="p-5 border-b border-[#EBF2FA] flex justify-between items-center bg-[#F8FAFD] rounded-t-[10px]">
            <div>
                <h3 class="text-[16px] font-bold text-[#1A3A6B]">Cấu trúc ma trận đề thi</h3>
                <p class="text-[12.5px] text-[#6B7C99] mt-1">Định nghĩa số câu theo chương và độ khó, hệ thống sẽ tự động chọn ngẫu nhiên.</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="badge s-upcoming">Tổng: <span id="matrixTotalQuestions">0</span> câu • <span id="matrixTotalPoints">0.00</span> điểm</div>
                <button type="submit" class="btn btn-primary" id="btn-submit-matrix">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    Tạo Đề Từ Ma Trận
                </button>
            </div>
        </div>

        @error('matrix')
        <div class="px-5 py-3 bg-[#FEF2F2] border-b border-[#FCA5A5] text-[#991B1B] text-[13px] font-medium">{{ $message }}</div>
        @enderror

        <div class="p-5">
            {{-- Preset section --}}
            <div class="mb-5 p-4 bg-[#F8FAFD] rounded-lg border border-[#EBF2FA]">
                <div class="flex items-center justify-between mb-3">
                    <label class="text-[12px] font-bold text-[#1A3A6B] uppercase tracking-wider">Cấu hình mẫu</label>
                    <div class="flex items-center gap-2">
                        <label class="text-[12px] font-semibold text-[#6B7C99]">Tổng số câu:</label>
                        <input type="number" id="preset-total" value="20" min="4" max="100" class="ca-input" style="width:70px" oninput="updateActivePreset()">
                    </div>
                </div>
                <div class="flex flex-wrap gap-2" id="preset-buttons">
                    <button type="button" class="preset-btn" data-preset="easy" onclick="applyPreset('easy')">
                        📗 Mẫu Dễ <span class="text-[10px] text-[#6B7C99] ml-1">(60% NB)</span>
                    </button>
                    <button type="button" class="preset-btn" data-preset="standard" onclick="applyPreset('standard')">
                        📘 Mẫu Chuẩn <span class="text-[10px] text-[#6B7C99] ml-1">(4-3-2-1)</span>
                    </button>
                    <button type="button" class="preset-btn" data-preset="hard" onclick="applyPreset('hard')">
                        📕 Mẫu Khó <span class="text-[10px] text-[#6B7C99] ml-1">(Thi cuối kỳ)</span>
                    </button>
                    <button type="button" class="preset-btn" onclick="clearMatrix()">
                        🗑️ Xóa tất cả
                    </button>
                </div>
            </div>

            {{-- Matrix availability warning --}}
            <div id="matrix-availability-warning" class="mb-4 px-4 py-2.5 bg-[#FEF2F2] border border-[#FCA5A5] text-[#991B1B] text-[12px] rounded-lg" style="display:none">
                <strong>⚠️ Không đủ câu hỏi:</strong> <span id="matrix-warning-text"></span>
            </div>

            {{-- Matrix table --}}
            <table class="ca-table text-left" id="matrix-table">
                <thead>
                    <tr>
                        <th>Chương</th>
                        <th>Độ khó</th>
                        <th>Số câu</th>
                        <th>Điểm/câu</th>
                        <th style="width:80px">Sẵn có</th>
                        <th class="w-12"></th>
                    </tr>
                </thead>
                <tbody id="matrix-body">
                    {{-- JS sẽ thêm rows --}}
                </tbody>
            </table>

            <button type="button" onclick="addMatrixRow()" class="btn btn-ghost mt-4 w-full">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Thêm hàng ma trận
            </button>
        </div>
    </div>
</div>
