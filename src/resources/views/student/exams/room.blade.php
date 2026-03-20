@php
    $totalQuestions = count($questions);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>{{ $exam->title }} - EduPortal</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet"/>
    <style data-purpose="custom-styles">
        body { background-color: #f1f5f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .question-btn { width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: 8px; font-weight: 500; font-size: 1.125rem; border: 1px solid #cbd5e1; background-color: white; position: relative; }
        .question-btn.answered { background-color: #10b981; color: white; border-color: #10b981; }
        .question-btn.current { background-color: #1e3a8a; color: white; border-color: #1e3a8a; }
        .question-btn.flagged { border-color: #d97706; color: #d97706; }
        .option-card { border: 1px solid #cbd5e1; border-radius: 8px; padding: 1rem 1.5rem; margin-bottom: 1rem; display: flex; align-items: center; cursor: pointer; background-color: white; }
        .option-card.selected { background-color: #eff6ff; border-color: #1e3a8a; }
        .custom-radio { appearance: none; width: 24px; height: 24px; border: 2px solid #cbd5e1; border-radius: 50%; margin-right: 1rem; outline: none; position: relative; }
        .custom-radio:checked { border-color: #1e3a8a; }
        .custom-radio:checked::after { content: ''; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 12px; height: 12px; background-color: #1e3a8a; border-radius: 50%; }
        /* Hide all questions by default, show current */
        .question-container { display: none; }
        .question-container.active { display: block; }
        
        /* Dark mode styles */
        body.dark-mode { background-color: #111827; }
        body.dark-mode header { background-color: #1f2937; border-color: #374151; }
        body.dark-mode .text-gray-800, body.dark-mode .text-gray-700 { color: #f3f4f6; }
        body.dark-mode .bg-white { background-color: #1f2937; border-color: #374151; }
        body.dark-mode .question-btn { background-color: #374151; border-color: #4b5563; color: #f3f4f6; }
        body.dark-mode .question-btn.answered { background-color: #10b981; border-color: #10b981; color: white; }
        body.dark-mode .question-btn.current { background-color: #3b82f6; border-color: #3b82f6; color: white; }
        body.dark-mode .option-card { background-color: #374151; border-color: #4b5563; }
        body.dark-mode .option-card.selected { background-color: #1e3a8a; border-color: #3b82f6; }
        body.dark-mode .option-card .text-gray-700, body.dark-mode .option-card .text-gray-800 { color: #f3f4f6; }
        body.dark-mode .bg-gray-50 { background-color: #374151; }
    </style>
</head>
<body class="min-h-screen flex flex-col">
<!-- BEGIN: MainHeader -->
<header class="bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center shadow-sm z-10 relative transition-colors duration-200">
    <div class="flex items-center space-x-2">
        <i class="fa-solid fa-graduation-cap text-3xl text-gray-700"></i>
        <span class="text-2xl font-bold tracking-tight text-gray-800">EDUPORTAL</span>
    </div>
    <div class="flex items-center space-x-6">
        <div class="flex items-center space-x-3">
            <span class="text-gray-700 font-medium whitespace-nowrap">Sinh viên: {{ Auth::user()->name ?? 'Sinh viên' }}</span>
            <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center text-gray-500 relative flex-shrink-0">
                <i class="fa-solid fa-user text-xl"></i>
            </div>
        </div>
    </div>
</header>
<!-- END: MainHeader -->

<!-- BEGIN: MainContent -->
<main class="flex-1 p-6 flex gap-6 w-full max-w-[1600px] mx-auto transition-colors duration-200" id="main-content">
    <!-- Left Column: Question Navigator -->
    <div class="w-[340px] flex-shrink-0 flex flex-col gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex flex-col transition-colors duration-200 h-full max-h-[calc(100vh-120px)] overflow-y-auto">
            <h2 class="text-xl font-bold mb-6 text-gray-800">Question Navigator</h2>
            <div class="grid grid-cols-5 gap-3 mb-8" id="question-navigator">
                @foreach($questions as $index => $question)
                    <button type="button" 
                            class="question-btn {{ $index === 0 ? 'current' : '' }} {{ isset($savedAnswers[$question->id]) ? 'answered' : '' }}" 
                            onclick="goToQuestion({{ $index }})" 
                            id="nav-btn-{{ $index }}">
                        {{ $index + 1 }}
                        <div class="flag-indicator absolute top-0 right-0 w-0 h-0 border-t-[20px] border-l-[20px] border-t-[#d97706] border-l-transparent rounded-tr-[6px] hidden"></div>
                        <i class="fa-solid fa-flag text-[8px] text-white absolute top-0.5 right-0.5 z-10 hidden"></i>
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Center Column: Question Content -->
    <div class="flex-1 flex flex-col gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 flex flex-col h-full transition-colors duration-200 relative">
            
            <form id="exam-form" action="{{ route('student.exams.submit', $exam->id) }}" method="POST">
                @csrf
                
                @foreach($questions as $index => $question)
                    <div class="question-container {{ $index === 0 ? 'active' : '' }}" id="question-{{ $index }}">
                        <!-- Question Header -->
                        <div class="flex justify-between items-center pb-6 border-b border-gray-200 mb-8 transition-colors duration-200">
                            <h2 class="text-2xl font-bold text-gray-800">
                                Câu hỏi {{ $index + 1 }} <span class="text-gray-500 font-normal">/ {{ $totalQuestions }}</span>
                            </h2>
                            <button type="button" onclick="toggleFlag({{ $index }})" id="flag-btn-{{ $index }}" class="flex items-center space-x-2 px-4 py-2 border border-amber-500 text-amber-600 rounded-lg font-medium hover:bg-amber-50 transition-colors">
                                <i class="fa-regular fa-flag"></i>
                                <span>Đặt cờ hiệu</span>
                            </button>
                        </div>

                        <!-- Question Text -->
                        <div class="mb-8 question-text-content">
                            <h3 class="text-xl font-bold text-gray-800 leading-relaxed">
                               Câu {{ $index + 1 }}: {!! $question->content !!}
                            </h3>
                        </div>

                        <!-- Options -->
                        <div class="flex-1 space-y-4">
                            @foreach($question->options as $option)
                                @php
                                    $isChecked = isset($savedAnswers[$question->id]) && $savedAnswers[$question->id] == $option->id;
                                @endphp
                                <label class="option-card {{ $isChecked ? 'selected' : '' }}" onclick="selectOption(this, {{ $index }})">
                                    <input type="radio" 
                                           name="answers[{{ $question->id }}]" 
                                           value="{{ $option->id }}" 
                                           data-question-id="{{ $question->id }}"
                                           class="answer-radio custom-radio"
                                           {{ $isChecked ? 'checked' : '' }}>
                                    <span class="text-lg text-gray-700">{!! $option->content !!}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </form>

            <div id="save-status" class="absolute top-4 right-4 text-sm font-medium text-green-500 hidden bg-green-50 px-3 py-1 rounded shadow-sm border border-green-200 transition-opacity">Đã lưu</div>

            <!-- Navigation Buttons -->
            <div class="flex gap-4 mt-8 pt-6 border-t border-gray-100 mt-auto transition-colors duration-200">
                <button type="button" onclick="prevQuestion()" id="btn-prev" class="flex-1 py-4 border-2 border-gray-300 rounded-xl text-gray-700 font-bold text-lg hover:bg-gray-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    Câu trước
                </button>
                <button type="button" onclick="nextQuestion()" id="btn-next" class="flex-1 py-4 bg-[#1e3a8a] text-white rounded-xl font-bold text-lg hover:bg-blue-800 transition-colors shadow-md disabled:opacity-50 disabled:cursor-not-allowed">
                    Câu tiếp theo
                </button>
            </div>
        </div>
    </div>

    <!-- Right Column: Timer and Tools -->
    <div class="w-[300px] flex-shrink-0 flex flex-col gap-6">
        <!-- Timer -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 text-center transition-colors duration-200">
            <div class="flex items-center justify-center space-x-2 text-gray-600 mb-2 font-medium transition-colors duration-200">
                <i class="fa-regular fa-clock"></i>
                <span>Thời gian còn lại</span>
            </div>
            <div id="countdown-timer" class="text-5xl font-bold text-[#b91c1c] tracking-widest">
                -- : --
            </div>
        </div>

        <!-- Submit Button -->
        <button type="button" onclick="document.getElementById('exam-form').submit();" class="w-full py-4 bg-[#1e3a8a] text-white rounded-xl font-bold text-lg hover:bg-blue-800 transition-colors shadow-md">
            NỘP BÀI
        </button>

        <!-- Scratchpad -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex-1 flex flex-col transition-colors duration-200 min-h-[200px]">
            <h3 class="text-lg font-bold text-gray-800 mb-4 transition-colors duration-200">Ô nháp</h3>
            <textarea class="w-full flex-1 border border-gray-300 rounded-lg p-3 text-gray-600 resize-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200" placeholder="Ghi chú tại đây..."></textarea>
        </div>

        <!-- Display Settings -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 transition-colors duration-200">
            <h3 class="text-lg font-bold text-gray-800 mb-4 transition-colors duration-200">Cài đặt hiển thị</h3>
            <div class="flex items-center justify-between mb-4">
                <span class="text-gray-700 font-medium transition-colors duration-200">Cỡ chữ:</span>
                <div class="flex space-x-2">
                    <button onclick="changeFontSize(-1)" class="w-10 h-8 border border-gray-300 rounded flex items-center justify-center font-medium hover:bg-gray-50 transition-colors dark:text-gray-400">A-</button>
                    <button onclick="changeFontSize(1)" class="w-10 h-8 border border-gray-300 rounded flex items-center justify-center font-medium hover:bg-gray-50 transition-colors dark:text-gray-400">A+</button>
                </div>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-gray-700 font-medium transition-colors duration-200">Chế độ tối:</span>
                <div class="flex items-center space-x-3">
                    <!-- Toggle Switch -->
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="dark-mode-toggle" class="sr-only peer" onchange="toggleDarkMode()">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#1e3a8a]"></div>
                    </label>
                </div>
            </div>
        </div>
    </div>
</main>
<!-- END: MainContent -->

<script>
    const totalQuestions = {{ $totalQuestions }};
    let currentQuestionIndex = 0;
    let baseFontSize = 16;
    
    const saveUrl = "{{ route('student.exams.save-answer', $exam->id) }}";
    const csrfToken = "{{ csrf_token() }}";
    const statusEl = document.getElementById('save-status');

    document.addEventListener("DOMContentLoaded", function() {
        updateNavigationButtons();
        
        let timeLeft = parseInt('{{ $timeLeftSeconds }}');
        const timerDisplay = document.getElementById('countdown-timer');
        const examForm = document.getElementById('exam-form');

        const countdown = setInterval(function() {
            if (timeLeft <= 0) {
                clearInterval(countdown);
                timerDisplay.innerHTML = "00 : 00";
                examForm.submit();
            } else {
                let minutes = Math.floor(timeLeft / 60);
                let seconds = timeLeft % 60;
                timerDisplay.innerHTML =
                    (minutes < 10 ? "0" : "") + minutes + " : " +
                    (seconds < 10 ? "0" : "") + seconds;
                timeLeft -= 1;
            }
        }, 1000);
        
        document.querySelectorAll('.answer-radio').forEach(radio => {
            radio.addEventListener('change', function() {
                const questionIdx = this.closest('.question-container').id.split('-')[1];
                const btn = document.getElementById(`nav-btn-${questionIdx}`);
                btn.classList.add('answered');
                
                autoSave(this.getAttribute('data-question-id'), this.value);
            });
        });
    });
    
    function autoSave(questionId, optionId) {
        statusEl.style.display = 'block';
        statusEl.innerText = "Đang lưu...";
        statusEl.className = "absolute top-4 right-4 text-sm font-medium text-yellow-600 bg-yellow-50 px-3 py-1 rounded shadow-sm border border-yellow-200 z-50 transition-opacity";
        
        fetch(saveUrl, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken
            },
            body: JSON.stringify({
                question_id: questionId,
                question_option_id: optionId
            })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                statusEl.innerText = "Đã lưu ✓";
                statusEl.className = "absolute top-4 right-4 text-sm font-medium text-green-600 bg-green-50 px-3 py-1 rounded shadow-sm border border-green-200 z-50 transition-opacity";
                setTimeout(() => {
                    statusEl.style.opacity = '0';
                    setTimeout(() => {
                        statusEl.style.display = 'none';
                        statusEl.style.opacity = '1';
                    }, 300);
                }, 2000);
            } else {
                 statusEl.innerText = "Lỗi lưu đáp án!";
                 statusEl.className = "absolute top-4 right-4 text-sm font-medium text-red-600 bg-red-50 px-3 py-1 rounded shadow-sm border border-red-200 z-50";
            }
        })
        .catch(() => {
            statusEl.innerText = "Lỗi kết nối!";
            statusEl.className = "absolute top-4 right-4 text-sm font-medium text-red-600 bg-red-50 px-3 py-1 rounded shadow-sm border border-red-200 z-50";
        });
    }

    function goToQuestion(index) {
        if (index < 0 || index >= totalQuestions) return;
        
        document.getElementById(`question-${currentQuestionIndex}`).classList.remove('active');
        document.getElementById(`nav-btn-${currentQuestionIndex}`).classList.remove('current');
        
        currentQuestionIndex = index;
        document.getElementById(`question-${currentQuestionIndex}`).classList.add('active');
        document.getElementById(`nav-btn-${currentQuestionIndex}`).classList.add('current');
        
        updateNavigationButtons();
    }

    function prevQuestion() {
        goToQuestion(currentQuestionIndex - 1);
    }

    function nextQuestion() {
        goToQuestion(currentQuestionIndex + 1);
    }

    function updateNavigationButtons() {
        document.getElementById('btn-prev').disabled = (currentQuestionIndex === 0);
        document.getElementById('btn-next').disabled = (currentQuestionIndex === totalQuestions - 1);
    }

    function selectOption(label, index) {
        const container = document.getElementById(`question-${index}`);
        container.querySelectorAll('.option-card').forEach(card => card.classList.remove('selected'));
        label.classList.add('selected');
    }
    
    function toggleFlag(index) {
        const navBtn = document.getElementById(`nav-btn-${index}`);
        const flagBtn = document.getElementById(`flag-btn-${index}`);
        
        const isFlagged = navBtn.classList.toggle('flagged');
        
        const flagElements = navBtn.querySelectorAll('.flag-indicator, .fa-flag');
        if(isFlagged) {
            flagElements.forEach(el => el.classList.remove('hidden'));
            flagBtn.classList.replace('border-amber-500', 'bg-amber-500');
            flagBtn.classList.replace('text-amber-600', 'text-white');
            flagBtn.innerHTML = '<i class="fa-solid fa-flag"></i><span>Bỏ cờ hiệu</span>';
        } else {
            flagElements.forEach(el => el.classList.add('hidden'));
            flagBtn.classList.replace('bg-amber-500', 'border-amber-500');
            flagBtn.classList.replace('text-white', 'text-amber-600');
            flagBtn.innerHTML = '<i class="fa-regular fa-flag"></i><span>Đặt cờ hiệu</span>';
        }
    }

    function changeFontSize(direction) {
        baseFontSize += (direction * 2);
        if (baseFontSize < 14) baseFontSize = 14;
        if (baseFontSize > 28) baseFontSize = 28;
        
        document.querySelectorAll('.question-text-content').forEach(el => {
            el.style.fontSize = `${baseFontSize}px`;
        });
    }

    function toggleDarkMode() {
        document.body.classList.toggle('dark-mode');
    }
</script>
</body>
</html>