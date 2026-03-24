@php
$question = $question ?? null;

$difficultyLabelMap = [
'remember' => 'Dễ (Remember)',
'understand' => 'Trung bình (Understand)',
'apply' => 'Khó (Apply)',
'analyze' => 'Nâng cao (Analyze)',
];

$statusLabelMap = [
'draft' => 'Chờ duyệt',
'approved' => 'Đã duyệt',
'hidden' => 'Ẩn',
];
@endphp

@if ($errors->any())
<div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
    <ul class="list-disc pl-5">
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if ($method !== 'POST')
    @method($method)
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white p-4 rounded-xl">
            <label for="subject_id" class="block text-xs font-bold text-secondary uppercase tracking-wider mb-2">Môn học</label>
            <select id="subject_id" name="subject_id" class="w-full rounded-lg border-surface-container-high">
                <option value="">Chọn môn học</option>
                @foreach ($subjects as $subject)
                <option value="{{ $subject->id }}" {{ (string) old('subject_id', $question->subject_id ?? '') === (string) $subject->id ? 'selected' : '' }}>{{ $subject->name }} ({{ $subject->code }})</option>
                @endforeach
            </select>
        </div>

        <div class="bg-white p-4 rounded-xl">
            <label for="chapter_id" class="block text-xs font-bold text-secondary uppercase tracking-wider mb-2">Chương</label>
            <select id="chapter_id" name="chapter_id" class="w-full rounded-lg border-surface-container-high">
                <option value="">Không gán chương</option>
                @foreach ($chapters as $chapter)
                <option value="{{ $chapter->id }}" {{ (string) old('chapter_id', $question->chapter_id ?? '') === (string) $chapter->id ? 'selected' : '' }}>{{ $chapter->subject?->name }} - {{ $chapter->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="bg-white p-4 rounded-xl">
            <label for="question_type_id" class="block text-xs font-bold text-secondary uppercase tracking-wider mb-2">Loại câu hỏi</label>
            <select id="question_type_id" name="question_type_id" class="w-full rounded-lg border-surface-container-high">
                <option value="">Chọn loại câu hỏi</option>
                @foreach ($questionTypes as $questionType)
                <option value="{{ $questionType->id }}" {{ (string) old('question_type_id', $question->question_type_id ?? '') === (string) $questionType->id ? 'selected' : '' }}>{{ $questionType->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="bg-white p-4 rounded-xl">
            <label for="difficulty" class="block text-xs font-bold text-secondary uppercase tracking-wider mb-2">Mức độ</label>
            <select id="difficulty" name="difficulty" class="w-full rounded-lg border-surface-container-high">
                @foreach ($difficulties as $difficulty)
                <option value="{{ $difficulty->code }}" {{ old('difficulty', $question->difficulty ?? 'remember') === $difficulty->code ? 'selected' : '' }}>{{ $difficulty->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="bg-white p-4 rounded-xl md:col-span-2">
            <label for="status" class="block text-xs font-bold text-secondary uppercase tracking-wider mb-2">Trạng thái</label>
            <select id="status" name="status" class="w-full rounded-lg border-surface-container-high">
                @foreach ($statuses as $status)
                <option value="{{ $status }}" {{ old('status', $question->status ?? 'draft') === $status ? 'selected' : '' }}>{{ $statusLabelMap[$status] ?? ucfirst($status) }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="bg-white p-4 rounded-xl">
        <label for="content" class="block text-xs font-bold text-secondary uppercase tracking-wider mb-2">Nội dung câu hỏi</label>
        <textarea id="content" name="content" rows="6" class="w-full rounded-lg border-surface-container-high">{{ old('content', $question->content ?? '') }}</textarea>
    </div>

    <div class="bg-white p-4 rounded-xl">
        <label for="explanation" class="block text-xs font-bold text-secondary uppercase tracking-wider mb-2">Giải thích (tuỳ chọn)</label>
        <textarea id="explanation" name="explanation" rows="4" class="w-full rounded-lg border-surface-container-high">{{ old('explanation', $question->explanation ?? '') }}</textarea>
    </div>

    <div class="flex items-center gap-3">
        <button type="submit" class="bg-primary text-white px-5 py-2.5 rounded-xl font-semibold hover:opacity-90 transition-all">{{ $submitLabel }}</button>
        <a href="{{ route('lecturer.questions.index') }}" class="bg-white text-primary border border-surface-container-high px-5 py-2.5 rounded-xl font-semibold hover:bg-surface-bright transition-all">Quay lại</a>
    </div>
</form>