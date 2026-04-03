<x-app-layout>
    <div class="py-8 bg-[#F8FAFD] min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h2 class="text-[22px] font-bold text-[#1A3A6B] mb-1">Sửa Lịch Thi</h2>
                    <p class="text-[13px] text-[#6B7C99]">Đề thi: {{ $schedule->exam->title }} | Lớp: {{ $schedule->courseSection->name }}</p>
                </div>
                <a href="{{ route('lecturer.schedules.index') }}" class="inline-flex items-center gap-2 text-[13px] text-[#185FA5] hover:underline">← Quay lại</a>
            </div>

            <div class="bg-white rounded-[10px] border border-[#D6E2F0] p-6 max-w-2xl"
                x-data="{
                    scheduleMode: '{{ old('schedule_mode', $schedule->schedule_mode ?: \App\Models\ExamSchedule::MODE_WITHIN_DAY) }}',
                    singleDayDate: '{{ old('exam_date', $schedule->exam_date->format('Y-m-d')) }}'
                }">
                <form method="POST" action="{{ route('lecturer.schedules.update', $schedule->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="space-y-4">
                        <div>
                            <label class="block text-[12px] font-semibold text-[#1A3A6B] mb-2">Chế độ mở đề <span class="text-[#DC2626]">*</span></label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <label class="border border-[#D6E2F0] rounded-lg px-3 py-2 flex items-start gap-2 cursor-pointer"
                                    :class="scheduleMode === 'in_range' ? 'bg-[#EAF4FD] border-[#185FA5]' : 'bg-white'">
                                    <input type="radio" name="schedule_mode" value="in_range" x-model="scheduleMode" class="mt-0.5">
                                    <span>
                                        <span class="block text-[12px] font-semibold text-[#1A3A6B]">Trong khoảng thời gian</span>
                                        <span class="block text-[11px] text-[#6B7C99]">Mở liên tục từ ngày bắt đầu đến ngày kết thúc.</span>
                                    </span>
                                </label>
                                <label class="border border-[#D6E2F0] rounded-lg px-3 py-2 flex items-start gap-2 cursor-pointer"
                                    :class="scheduleMode === 'within_day' ? 'bg-[#EAF4FD] border-[#185FA5]' : 'bg-white'">
                                    <input type="radio" name="schedule_mode" value="within_day" x-model="scheduleMode" class="mt-0.5">
                                    <span>
                                        <span class="block text-[12px] font-semibold text-[#1A3A6B]">Kiểm tra trong ngày</span>
                                        <span class="block text-[11px] text-[#6B7C99]">Chỉ cho phép tham gia trong khung giờ của 1 ngày.</span>
                                    </span>
                                </label>
                            </div>
                            @error('schedule_mode') <span class="text-[11px] text-[#DC2626]">{{ $message }}</span> @enderror
                        </div>

                        <template x-if="scheduleMode === 'within_day'">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-[12px] font-semibold text-[#1A3A6B] mb-1">Ngày kiểm tra <span class="text-[#DC2626]">*</span></label>
                                    <input type="date" name="exam_date" x-model="singleDayDate" value="{{ old('exam_date', $schedule->exam_date->format('Y-m-d')) }}" required class="w-full border border-[#D6E2F0] rounded-lg px-3 py-2 text-[13px]">
                                    @error('exam_date') <span class="text-[11px] text-[#DC2626]">{{ $message }}</span> @enderror
                                </div>

                                <input type="hidden" name="end_date" :value="singleDayDate">

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[12px] font-semibold text-[#1A3A6B] mb-1">Giờ bắt đầu <span class="text-[#DC2626]">*</span></label>
                                        <input type="text" name="start_time" value="{{ old('start_time', \Carbon\Carbon::parse($schedule->start_time)->format('H:i')) }}" required inputmode="numeric" maxlength="5" pattern="([01][0-9]|2[0-3]):[0-5][0-9]" placeholder="HH:mm" title="Nhập giờ theo định dạng 24h, ví dụ 08:30" class="w-full border border-[#D6E2F0] rounded-lg px-3 py-2 text-[13px]">
                                        @error('start_time') <span class="text-[11px] text-[#DC2626]">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[12px] font-semibold text-[#1A3A6B] mb-1">Giờ kết thúc <span class="text-[#DC2626]">*</span></label>
                                        <input type="text" name="end_time" value="{{ old('end_time', \Carbon\Carbon::parse($schedule->end_time)->format('H:i')) }}" required inputmode="numeric" maxlength="5" pattern="([01][0-9]|2[0-3]):[0-5][0-9]" placeholder="HH:mm" title="Nhập giờ theo định dạng 24h, ví dụ 17:45" class="w-full border border-[#D6E2F0] rounded-lg px-3 py-2 text-[13px]">
                                        @error('end_time') <span class="text-[11px] text-[#DC2626]">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                        </template>

                        <template x-if="scheduleMode === 'in_range'">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[12px] font-semibold text-[#1A3A6B] mb-1">Ngày bắt đầu <span class="text-[#DC2626]">*</span></label>
                                    <input type="date" name="exam_date" value="{{ old('exam_date', $schedule->exam_date->format('Y-m-d')) }}" required class="w-full border border-[#D6E2F0] rounded-lg px-3 py-2 text-[13px]">
                                    @error('exam_date') <span class="text-[11px] text-[#DC2626]">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-[12px] font-semibold text-[#1A3A6B] mb-1">Ngày kết thúc <span class="text-[#DC2626]">*</span></label>
                                    <input type="date" name="end_date" value="{{ old('end_date', ($schedule->end_date ?? $schedule->exam_date)->format('Y-m-d')) }}" required class="w-full border border-[#D6E2F0] rounded-lg px-3 py-2 text-[13px]">
                                    @error('end_date') <span class="text-[11px] text-[#DC2626]">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </template>

                        <div class="p-3 border border-[#D6E2F0] rounded-lg bg-[#F8FAFD]">
                            <label class="flex items-start gap-2 cursor-pointer">
                                <input type="checkbox" name="disable_attempt_timer" value="1" {{ old('disable_attempt_timer', $schedule->disable_attempt_timer) ? 'checked' : '' }} class="mt-0.5 rounded border-[#D6E2F0] text-[#185FA5] focus:ring-[#185FA5]">
                                <span>
                                    <span class="block text-[12px] font-semibold text-[#1A3A6B]">Không tính thời gian làm bài</span>
                                    <span class="block text-[11px] text-[#6B7C99]">Sinh viên được làm bài đến khi hết cửa sổ mở đề, không đếm ngược theo thời lượng đề.</span>
                                </span>
                            </label>
                            @error('disable_attempt_timer') <span class="text-[11px] text-[#DC2626]">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-[12px] font-semibold text-[#1A3A6B] mb-1">Trạng thái</label>
                            <select name="status" class="w-full border border-[#D6E2F0] rounded-lg px-3 py-2 text-[13px]">
                                @foreach(['scheduled' => 'Đã lên lịch', 'in_progress' => 'Đang diễn ra', 'completed' => 'Hoàn thành', 'cancelled' => 'Đã hủy'] as $val => $label)
                                <option value="{{ $val }}" {{ old('status', $schedule->status) === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-[12px] font-semibold text-[#1A3A6B] mb-1">Ghi chú</label>
                            <textarea name="notes" class="w-full border border-[#D6E2F0] rounded-lg px-3 py-2 text-[13px]" rows="3">{{ old('notes', $schedule->notes) }}</textarea>
                        </div>

                        @php
                        $currentLinkedColumnId = $schedule->courseSection->gradeColumns->where('exam_schedule_id', $schedule->id)->first()?->id;
                        @endphp
                        <div class="mt-4 p-4 border border-indigo-100 rounded-[8px] bg-indigo-50/30">
                            <label class="block text-[13px] font-bold text-[#1A3A6B] mb-1">Cột điểm đồng bộ</label>
                            <p class="text-[11px] text-[#6B7C99] mb-3 leading-relaxed">Kết quả bài thi sẽ được hệ thống tự động đổ vào cột điểm này khi sinh viên nộp bài.</p>

                            <select name="grade_column_id" class="w-full border border-[#D6E2F0] rounded-lg px-3 py-2 text-[13px] focus:border-[#185FA5]">
                                <option value="">-- Không đồng bộ điểm / Xóa đồng bộ --</option>
                                @foreach($schedule->courseSection->gradeColumns as $col)
                                <option value="{{ $col->id }}" {{ $currentLinkedColumnId === $col->id ? 'selected' : '' }}>
                                    {{ $col->name }} ({{ floatval($col->weight) }}%)
                                </option>
                                @endforeach
                            </select>

                            @if(!$currentLinkedColumnId)
                            <div class="mt-3">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="link_grade_column" value="1" class="rounded border-indigo-300 text-[#185FA5] focus:ring-[#185FA5]">
                                    <span class="text-[12px] text-[#1A3A6B]">Hoặc đánh dấu để <strong>Tự động tạo cột mới</strong> (Trọng số 0%)</span>
                                </label>
                            </div>
                            @endif
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t border-[#D6E2F0]">
                            <a href="{{ route('lecturer.schedules.index') }}" class="border border-[#D6E2F0] text-[#1A3A6B] px-5 py-2.5 rounded-lg text-[13px] font-semibold hover:bg-[#F4F7FC] transition-colors">Hủy</a>
                            <button type="submit" class="bg-[#1A3A6B] text-white px-6 py-2.5 rounded-lg text-[13px] font-semibold hover:bg-[#0F2A53] transition-colors">
                                Cập nhật
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>