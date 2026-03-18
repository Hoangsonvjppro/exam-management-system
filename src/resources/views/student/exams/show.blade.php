<x-app-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <x-card class="text-center">
                <h2 class="text-3xl font-bold text-gray-800 mb-4">{{ $exam->title }}</h2>
                <p class="text-gray-600 mb-6">{{ $exam->description }}</p>

                <div class="flex justify-center gap-8 mb-8 border-y py-4 bg-gray-50">
                    <div>
                        <span class="block text-sm text-gray-500">Thời gian làm bài</span>
                        <span class="text-xl font-bold text-blue-600">{{ $exam->duration_minutes }} Phút</span>
                    </div>
                    <div>
                        <span class="block text-sm text-gray-500">Số câu hỏi</span>
                        <span class="text-xl font-bold text-blue-600">{{ $exam->questions->count() }} Câu</span>
                    </div>
                </div>

                @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
                @endif

                @if(session('info'))
                <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-4">
                    {{ session('info') }}
                </div>
                @endif

                @if(!$attempt)
                <form action="{{ route('student.exams.start', $exam->id) }}" method="POST">
                    @csrf
                    <x-primary-button class="px-8 py-3 text-lg">Bắt đầu làm bài</x-primary-button>
                </form>
                @elseif($attempt->status === 'in_progress')
                <p class="text-yellow-600 mb-4">Bạn đang có bài thi chưa hoàn thành!</p>
                <a href="{{ route('student.exams.room', $exam->id) }}" class="inline-flex items-center px-4 py-2 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-600">
                    Tiếp tục làm bài
                </a>
                @else
                <p class="text-green-600 font-bold text-lg mb-4">Bạn đã hoàn thành bài thi này!</p>
                <a href="{{ route('student.dashboard') }}" class="text-blue-500 hover:underline">Về trang chủ</a>
                @endif

            </x-card>
        </div>
    </div>
</x-app-layout>