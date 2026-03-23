<x-app-layout>
    @section('page-title', 'Thêm câu hỏi')

    <div class="p-8 space-y-6 flex-1 bg-surface-container-low">
        <div>
            <nav class="flex text-[10px] font-bold tracking-widest text-secondary uppercase mb-2 gap-2">
                <a href="{{ route('lecturer.dashboard') }}" class="text-primary">Dashboard</a>
                <span>/</span>
                <a href="{{ route('lecturer.questions.index') }}" class="text-primary">Questions</a>
                <span>/</span>
                <span>New</span>
            </nav>
            <h2 class="text-3xl font-extrabold text-primary font-headline tracking-tight">Thêm câu hỏi mới</h2>
            <p class="text-on-surface-variant mt-1">Nhập thông tin cơ bản để tạo câu hỏi trong ngân hàng.</p>
        </div>

        @include('question._form', [
        'action' => route('lecturer.questions.store'),
        'method' => 'POST',
        'submitLabel' => 'Tạo câu hỏi',
        ])
    </div>
</x-app-layout>