<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-card>
                <h2 class="text-2xl font-bold mb-4">Tạo Bài Kiểm Tra Mới</h2>
                <p class="text-gray-600 mb-6">Lớp học: {{ $courseSection->name ?? 'Lớp đang chọn' }}</p>

                <form method="POST" action="{{ route('lecturer.course-sections.exams.store', $courseSection->id) }}">
                    @csrf

                    <div class="mb-4">
                        <x-input-label for="title" value="Tên bài kiểm tra (VD: Thi giữa kỳ)" />
                        <x-text-input id="title" class="block mt-1 w-full" type="text" name="title" required autofocus />
                        <x-input-error :messages="$errors->get('title')" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <x-input-label for="description" value="Mô tả / Hướng dẫn làm bài" />
                        <textarea id="description" name="description" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full" rows="3"></textarea>
                    </div>

                    <div class="mb-4">
                        <x-input-label for="duration_minutes" value="Thời gian làm bài (Phút)" />
                        <x-text-input id="duration_minutes" class="block mt-1 w-full" type="number" name="duration_minutes" value="45" required />
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <x-input-label for="start_time" value="Thời gian Mở đề (Tuỳ chọn)" />
                            <x-text-input id="start_time" class="block mt-1 w-full" type="datetime-local" name="start_time" />
                        </div>
                        <div>
                            <x-input-label for="end_time" value="Thời gian Đóng đề (Tuỳ chọn)" />
                            <x-text-input id="end_time" class="block mt-1 w-full" type="datetime-local" name="end_time" />
                        </div>
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <x-primary-button class="ml-4">
                            Lưu và Tiếp tục (Chọn câu hỏi)
                        </x-primary-button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
</x-app-layout>