<x-app-layout>
    <div class="py-6 bg-gray-100 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 flex flex-col md:flex-row gap-6">

            <div class="w-full md:w-3/4 space-y-6">
                <form id="exam-form" action="{{ route('student.exams.submit', $exam->id) }}" method="POST">
                    @csrf

                    @foreach($questions as $index => $question)
                    <x-card class="mb-4">
                        <h3 class="font-bold text-lg mb-3">Câu {{ $index + 1 }}: {!! $question->content !!}</h3>

                        <div class="space-y-2 pl-4">
                            @foreach($question->options as $option)
                            <label class="flex items-center space-x-3 cursor-pointer p-2 hover:bg-gray-50 rounded">
                                <input type="radio"
                                    name="answers[{{ $question->id }}]"
                                    value="{{ $option->id }}"
                                    data-question-id="{{ $question->id }}"
                                    class="answer-radio h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500"
                                    {{ (isset($savedAnswers[$question->id]) && $savedAnswers[$question->id] == $option->id) ? 'checked' : '' }}>
                                <span class="text-gray-700">{!! $option->content !!}</span>
                            </label>
                            @endforeach
                        </div>
                    </x-card>
                    @endforeach
                </form>
            </div>

            <div class="w-full md:w-1/4">
                <div class="sticky top-6 bg-white p-6 rounded-lg shadow-md border-t-4 border-indigo-500 text-center">
                    <h4 class="text-gray-500 font-semibold mb-2">Thời gian còn lại</h4>
                    <div id="countdown-timer" class="text-4xl font-mono font-bold text-red-500 mb-6">
                        --:--
                    </div>

                    <p id="save-status" class="text-sm text-green-500 mb-4 h-5"></p>

                    <button type="button" onclick="document.getElementById('exam-form').submit();" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded transition">
                        NỘP BÀI
                    </button>
                </div>
            </div>

        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // 1. Đồng hồ đếm ngược
            let timeLeft = parseInt('{{ $timeLeftSeconds }}');
            const timerDisplay = document.getElementById('countdown-timer');
            const examForm = document.getElementById('exam-form');

            const countdown = setInterval(function() {
                if (timeLeft <= 0) {
                    clearInterval(countdown);
                    timerDisplay.innerHTML = "HẾT GIỜ";
                    examForm.submit(); // Hết giờ tự nộp
                } else {
                    let minutes = Math.floor(timeLeft / 60);
                    let seconds = timeLeft % 60;
                    timerDisplay.innerHTML =
                        (minutes < 10 ? "0" : "") + minutes + ":" +
                        (seconds < 10 ? "0" : "") + seconds;
                    timeLeft -= 1;
                }
            }, 1000);

            // 2. Auto-save đáp án bằng AJAX
            const radios = document.querySelectorAll('.answer-radio');
            const statusText = document.getElementById('save-status');

            radios.forEach(radio => {
                radio.addEventListener('change', function() {
                    statusText.innerText = "Đang lưu...";

                    const questionId = this.getAttribute('data-question-id');
                    const optionId = this.value;

                    fetch("{{ route('student.exams.save-answer', $exam->id) }}", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": "{{ csrf_token() }}"
                            },
                            body: JSON.stringify({
                                question_id: questionId,
                                question_option_Id: optionId
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                statusText.innerText = "Đã lưu thành công ✓";
                                setTimeout(() => {
                                    statusText.innerText = "";
                                }, 2000);
                            } else {
                                statusText.innerText = "Lỗi lưu đáp án!";
                                statusText.classList.replace('text-green-500', 'text-red-500');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            statusText.innerText = "Mất kết nối!";
                        });
                });
            });
        });
    </script>
</x-app-layout>