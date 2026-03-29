<x-app-layout>
    @section('page-title', 'Cập nhật câu hỏi')

    <div class="p-8 space-y-6 flex-1 bg-surface-container-low">
        <div>
            <nav class="flex text-[10px] font-bold tracking-widest text-secondary uppercase mb-2 gap-2">
                <a href="{{ route('lecturer.dashboard') }}" class="text-primary">Dashboard</a>
                <span>/</span>
                <a href="{{ route('lecturer.questions.index') }}" class="text-primary">Questions</a>
                <span>/</span>
                <span>Edit</span>
            </nav>
            <h2 class="text-3xl font-extrabold text-primary font-headline tracking-tight">Cập nhật câu hỏi #{{ $question->id }}</h2>
            <p class="text-on-surface-variant mt-1">Chỉnh sửa nội dung và thông tin phân loại câu hỏi.</p>
        </div>

        @include('question._form', [
        'action' => route('lecturer.questions.update', $question),
        'method' => 'PUT',
        'submitLabel' => 'Lưu thay đổi',
        'question' => $question,
        ])
    </div>
</x-app-layout>