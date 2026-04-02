<x-app-layout>
    <div class="p-8 space-y-8 flex-1 bg-surface-container-low flex-1">
        <div class="mb-10 flex items-end justify-between">
            <div>
                <nav class="flex items-center gap-2 text-on-surface-variant text-xs mb-2">
                    <a href="{{ route('lecturer.dashboard') }}" class="hover:text-primary">Dashboard</a>
                    <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                    <a href="{{ route('lecturer.questions.index') }}" class="hover:text-primary underline">Ngân hàng câu hỏi</a>
                    <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                    <span class="text-primary font-semibold">Cập nhật câu hỏi</span>
                </nav>
                <h1 class="text-3xl font-extrabold text-primary tracking-tight font-headline">Cập nhật câu hỏi #{{ $question->id }}</h1>
                <p class="text-on-surface-variant font-body mt-1">Chỉnh sửa nội dung và phân loại câu hỏi hiện tại</p>
            </div>
        </div>

        <div class="add-content">
            @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-6 py-4 rounded-2xl mb-6 shadow-sm">
                <div class="flex items-center gap-2 mb-2 font-bold uppercase text-xs tracking-wider">
                    <span class="material-symbols-outlined text-[20px]">error</span>
                    Có lỗi xảy ra:
                </div>
                <ul class="list-disc list-inside space-y-1 text-sm opacity-90">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form id="add-question-form" action="{{ route('lecturer.questions.update', $question) }}" method="POST" class="space-y-8">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="space-y-2">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-on-surface-variant ml-1">Chọn môn học</label>
                        <div class="relative">
                            <select name="subject_id" id="add_subject"
                                class="w-full bg-white border border-outline-variant/30 rounded-xl px-4 py-3.5 text-on-surface font-medium focus:ring-2 focus:ring-primary/20 cursor-pointer shadow-sm">
                                <option value="">Chọn môn học từ danh sách...</option>
                                @foreach ($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ old('subject_id', $question->subject_id) == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-on-surface-variant ml-1">Chọn chương</label>
                        <div class="relative">
                            <select name="chapter_id" id="add_chapter" data-initial="{{ old('chapter_id', $question->chapter_id) }}"
                                class="w-full bg-white border border-outline-variant/30 rounded-xl px-4 py-3.5 text-on-surface font-medium focus:ring-2 focus:ring-primary/20 cursor-pointer shadow-sm">
                                <option value="">Chọn chương tương ứng...</option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-on-surface-variant ml-1">Loại câu hỏi</label>
                        <div class="relative">
                            <select name="question_type_id" id="question_type_id"
                                class="w-full bg-white border border-outline-variant/30 rounded-xl px-4 py-3.5 text-on-surface font-medium focus:ring-2 focus:ring-primary/20 cursor-pointer shadow-sm">
                                @foreach ($questionTypes as $type)
                                <option value="{{ $type->id }}" data-code="{{ $type->code }}" {{ old('question_type_id', $question->question_type_id) == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Question Content Area with Rich Text Toolbar -->
                <div class="space-y-2">
                    <div class="flex justify-between items-center ml-1">
                        <label class="text-[11px] font-bold uppercase tracking-wider text-on-surface-variant">Nội dung câu hỏi</label>
                        <div class="flex items-center gap-3">
                            <span class="text-[10px] text-on-surface-variant/60 font-medium italic">Hỗ trợ Markdown & LaTeX</span>
                        </div>
                    </div>
                    <div class="rounded-2xl border border-outline-variant/30 bg-white overflow-hidden focus-within:ring-4 focus-within:ring-primary/10 focus-within:border-primary/40 transition-all shadow-sm">
                        <!-- Tiptap Toolbar -->
                        <div class="flex items-center gap-1 p-2 bg-surface-container-lowest border-b border-outline-variant/15" id="main-editor-toolbar">
                            <button class="toolbar-btn w-10 h-10 rounded-lg hover:bg-primary/10 hover:text-primary transition-all flex items-center justify-center" data-tiptap="bold" title="Bold" type="button"><span class="material-symbols-outlined text-[20px]">format_bold</span></button>
                            <button class="toolbar-btn w-10 h-10 rounded-lg hover:bg-primary/10 hover:text-primary transition-all flex items-center justify-center" data-tiptap="italic" title="Italic" type="button"><span class="material-symbols-outlined text-[20px]">format_italic</span></button>
                            <button class="toolbar-btn w-10 h-10 rounded-lg hover:bg-primary/10 hover:text-primary transition-all flex items-center justify-center" data-tiptap="underline" title="Underline" type="button"><span class="material-symbols-outlined text-[20px]">format_underlined</span></button>
                            <div class="w-px h-6 bg-outline-variant/30 mx-2"></div>
                            <button class="toolbar-btn w-10 h-10 rounded-lg hover:bg-primary/10 hover:text-primary transition-all flex items-center justify-center" data-tiptap="bulletList" title="Bullet List" type="button"><span class="material-symbols-outlined text-[20px]">format_list_bulleted</span></button>
                            <button class="toolbar-btn w-10 h-10 rounded-lg hover:bg-primary/10 hover:text-primary transition-all flex items-center justify-center" data-tiptap="orderedList" title="Numbered List" type="button"><span class="material-symbols-outlined text-[20px]">format_list_numbered</span></button>
                            <div class="w-px h-6 bg-outline-variant/30 mx-2"></div>
                            <button class="toolbar-btn w-10 h-10 rounded-lg hover:bg-primary/10 hover:text-primary transition-all flex items-center justify-center" data-tiptap="image" title="Insert Image" type="button"><span class="material-symbols-outlined text-[20px]">image</span></button>
                            <button class="toolbar-btn w-10 h-10 rounded-lg hover:bg-primary/10 hover:text-primary transition-all flex items-center justify-center" data-tiptap="latex" title="Math Formula (LaTeX)" type="button"><span class="material-symbols-outlined text-[20px]">functions</span></button>
                            <button class="toolbar-btn w-10 h-10 rounded-lg hover:bg-primary/10 hover:text-primary transition-all flex items-center justify-center" data-tiptap="codeBlock" title="Code Block" type="button"><span class="material-symbols-outlined text-[20px]">code</span></button>
                            <div class="flex-1"></div>
                        </div>
                        <!-- Tiptap Editor Container -->
                        <div id="tiptap-editor" class="w-full bg-white px-6 py-5 text-on-surface font-body leading-relaxed min-h-[250px] outline-none prose prose-slate max-w-none focus:outline-none"></div>

                        <!-- Hidden input to store editor HTML content for form submission -->
                        <input type="hidden" name="content" id="question_content_input">
                        <input type="file" id="editor-image-upload" class="hidden" accept="image/*">
                    </div>
                </div>

                <!-- Answers Section -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between ml-1">
                        <label class="text-[11px] font-bold uppercase tracking-wider text-on-surface-variant">Câu trả lời (Lựa chọn)</label>
                        <span class="text-[10px] text-on-surface-variant/60 font-medium italic">Click vào chữ cái để chọn đáp án đúng.</span>
                    </div>
                    <div class="space-y-4 p-4 rounded-2xl border-2 border-dashed border-outline-variant/20 bg-surface-container-low/30" id="answer-options-container">
                        <!-- Options will be added by JS -->
                    </div>
                    <button class="add-option-btn mt-2 flex items-center gap-2 text-primary font-bold text-sm hover:bg-primary/5 px-4 py-3 rounded-xl transition-all border border-dashed border-primary/30 w-full justify-center" type="button">
                        <span class="material-symbols-outlined text-[20px]">add</span>
                        <span>Thêm phương án</span>
                    </button>
                </div>

                <template id="option-template">
                    <div class="flex items-start gap-4 group option-row p-4 rounded-2xl border border-outline-variant/30 bg-white shadow-sm hover:border-primary/40 hover:shadow-md transition-all">
                        <label class="cursor-pointer relative mt-2">
                            <input class="peer sr-only correct-input" name="correct_options[]" type="radio" value="0" />
                            <div class="w-12 h-12 rounded-2xl bg-surface-container-high border border-outline-variant/30 flex items-center justify-center text-on-surface-variant font-bold peer-checked:bg-green-500 peer-checked:text-white peer-checked:border-green-600 transition-all shadow-sm relative overflow-visible">
                                <span class="option-label text-lg">A</span>
                            </div>
                        </label>

                        <div class="flex-1 bg-surface-container-lowest rounded-2xl overflow-hidden border border-outline-variant/20 focus-within:ring-4 focus-within:ring-primary/5 focus-within:border-primary/30 transition-all">
                            <div class="flex items-center gap-1 px-3 py-2 bg-surface-container-high/30 border-b border-outline-variant/15 option-toolbar">
                                <button class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-primary/10 hover:text-primary transition-colors" type="button" data-tiptap="bold" title="In đậm"><span class="material-symbols-outlined text-[18px]">format_bold</span></button>
                                <button class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-primary/10 hover:text-primary transition-colors" type="button" data-tiptap="italic" title="In nghiêng"><span class="material-symbols-outlined text-[18px]">format_italic</span></button>
                                <button class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-primary/10 hover:text-primary transition-colors" type="button" data-tiptap="underline" title="Gạch chân"><span class="material-symbols-outlined text-[18px]">format_underlined</span></button>
                                <div class="w-px h-4 bg-outline-variant/30 mx-1"></div>
                                <button class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-primary/10 hover:text-primary transition-colors" type="button" data-tiptap="latex" title="Công thức Toán"><span class="material-symbols-outlined text-[18px]">functions</span></button>
                            </div>

                            <div class="option-editor-target px-4 py-4 text-on-surface min-h-[80px] outline-none prose prose-sm max-w-none focus:outline-none"></div>
                            <input type="hidden" class="option-hidden-input" name="options[][content]">
                        </div>

                        <button class="mt-4 p-2.5 text-on-surface-variant/40 hover:text-error hover:bg-error/10 rounded-xl transition-all remove-option-btn" type="button" title="Xóa phương án">
                            <span class="material-symbols-outlined">delete</span>
                        </button>
                    </div>
                </template>

                <!-- Difficulty Selection -->
                <div class="space-y-3">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-on-surface-variant ml-1">Mức độ khó</label>
                    <div class="flex flex-wrap gap-4">
                        @foreach($difficulties as $difficulty)
                        <label class="relative flex items-center cursor-pointer group">
                            <input class="peer sr-only" name="difficulty" type="radio" value="{{ $difficulty->code }}" {{ old('difficulty', $question->difficulty) === $difficulty->code ? 'checked' : '' }} />
                            <div class="px-6 py-2.5 rounded-full border border-outline-variant flex items-center gap-2 text-on-surface-variant font-semibold 
                                peer-checked:bg-blue-50 peer-checked:border-blue-500 peer-checked:text-blue-700 transition-all hover:bg-surface-container-high
                                @if($difficulty->code == 'remember') peer-checked:bg-green-50 peer-checked:border-green-500 peer-checked:text-green-700 @endif
                                @if($difficulty->code == 'apply' || $difficulty->code == 'analyze') peer-checked:bg-red-50 peer-checked:border-red-500 peer-checked:text-red-700 @endif">
                                <span class="w-2 h-2 rounded-full 
                                    @if($difficulty->code == 'remember') bg-green-500 @elseif($difficulty->code == 'understand') bg-blue-500 @else bg-red-500 @endif"></span>
                                {{ $difficulty->name }}
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>

                <!-- Actions -->
                <div class="pt-6 border-t border-outline-variant/15 flex flex-col md:flex-row md:items-center justify-end gap-3">
                    <a href="{{ route('lecturer.questions.index') }}"
                        class="px-8 py-3.5 rounded-xl font-bold text-on-surface-variant hover:bg-surface-container-high transition-all text-center">
                        Hủy
                    </a>
                    <button
                        class="px-10 py-3.5 rounded-xl font-bold bg-navy-900 text-white shadow-lg hover:bg-navy-950 transition-all flex items-center justify-center gap-2"
                        type="submit">
                        <span class="material-symbols-outlined text-[20px]">save</span>
                        Lưu câu hỏi
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    @php
    $questionEditorConfig = [
    'initialQuestionContent' => old('content', $question->content),
    'initialOptions' => $question->options,
    'initialSubjectId' => old('subject_id', $question->subject_id),
    ];
    @endphp
    <script id="question-editor-config" type="application/json">
        @json($questionEditorConfig)
    </script>
    @vite(['resources/js/question.js'])
    @endpush
</x-app-layout>