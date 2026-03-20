<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-card>
                <h2 class="text-2xl font-bold mb-4">Chọn Câu Hỏi Cho: {{ $exam->title }}</h2>
                <p class="text-text-muted mb-6">Hãy tick vào các câu hỏi bạn muốn đưa vào đề thi này.</p>

                <form method="POST" action="{{ route('lecturer.exams.questions.store', $exam->id) }}">
                    @csrf
                    
                    <div class="overflow-x-auto bg-white rounded-[10px] shadow overflow-y-auto relative mb-4">
                        <table class="border-collapse table-auto w-full whitespace-no-wrap bg-white table-striped relative">
                            <thead>
                                <tr class="text-left">
                                    <th class="py-2 px-3 sticky top-0 border-b border-border-clean bg-surface-1">Chọn</th>
                                    <th class="py-2 px-3 sticky top-0 border-b border-border-clean bg-surface-1">Nội dung câu hỏi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($questions as $question)
                                    <tr>
                                        <td class="border-dashed border-t border-border-clean px-3 py-2">
                                            <input type="checkbox" name="question_ids[]" value="{{ $question->id }}" 
                                                   class="rounded border-border-clean text-navy-600 shadow-sm focus:ring-blue-200"
                                                   {{ in_array($question->id, $selectedQuestionIds) ? 'checked' : '' }}>
                                        </td>
                                        <td class="border-dashed border-t border-border-clean px-3 py-2">
                                            {!! Str::limit($question->content, 100) !!} </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <a href="{{ route('lecturer.classes.show', $exam->course_section_id) }}" class="text-text-muted hover:underline mr-4">Huỷ / Quay lại</a>
                        <x-primary-button>
                            Lưu Đề Thi
                        </x-primary-button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
</x-app-layout>