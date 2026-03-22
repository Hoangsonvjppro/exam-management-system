<x-app-layout>
    <div class="py-8 bg-[#F8FAFD] min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h2 class="text-[22px] font-bold text-[#1A3A6B] mb-1">Tạo Lịch Thi</h2>
                    <p class="text-[13px] text-[#6B7C99]">Đề thi: {{ $exam->title }}</p>
                </div>
                <a href="{{ route('lecturer.schedules.index') }}" class="inline-flex items-center gap-2 text-[13px] text-[#185FA5] hover:underline">← Quay lại</a>
            </div>

            <div class="bg-white rounded-[10px] border border-[#D6E2F0] p-6 max-w-2xl">
                <form method="POST" action="{{ route('lecturer.exams.schedules.store', $exam->id) }}">
                    @csrf
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="block text-[12px] font-semibold text-[#1A3A6B] mb-1">Ngày thi <span class="text-[#DC2626]">*</span></label>
                                <input type="date" name="exam_date" value="{{ old('exam_date') }}" required class="w-full border border-[#D6E2F0] rounded-lg px-3 py-2 text-[13px] focus:border-[#185FA5] focus:ring-1 focus:ring-[#E6F1FB]">
                                @error('exam_date') <span class="text-[11px] text-[#DC2626]">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[12px] font-semibold text-[#1A3A6B] mb-1">Giờ bắt đầu <span class="text-[#DC2626]">*</span></label>
                                <input type="time" name="start_time" value="{{ old('start_time') }}" required class="w-full border border-[#D6E2F0] rounded-lg px-3 py-2 text-[13px]">
                                @error('start_time') <span class="text-[11px] text-[#DC2626]">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-[12px] font-semibold text-[#1A3A6B] mb-1">Giờ kết thúc <span class="text-[#DC2626]">*</span></label>
                                <input type="time" name="end_time" value="{{ old('end_time') }}" required class="w-full border border-[#D6E2F0] rounded-lg px-3 py-2 text-[13px]">
                                @error('end_time') <span class="text-[11px] text-[#DC2626]">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-[12px] font-semibold text-[#1A3A6B] mb-1">Số SV tối đa</label>
                            <input type="number" name="max_students" value="{{ old('max_students') }}" min="1" class="w-full border border-[#D6E2F0] rounded-lg px-3 py-2 text-[13px]" placeholder="Để trống = không giới hạn">
                        </div>

                        <div>
                            <label class="block text-[12px] font-semibold text-[#1A3A6B] mb-1">Ghi chú</label>
                            <textarea name="notes" class="w-full border border-[#D6E2F0] rounded-lg px-3 py-2 text-[13px]" rows="3" placeholder="Lưu ý cho ca thi...">{{ old('notes') }}</textarea>
                        </div>

                        <div class="flex justify-end pt-2">
                            <button type="submit" class="bg-[#1A3A6B] text-white px-6 py-2.5 rounded-lg text-[13px] font-semibold hover:bg-[#0F2A53] transition-colors">
                                Tạo lịch thi
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
