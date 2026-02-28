<x-app-layout>
    @section('title', 'Dashboard — Quản trị viên')
    @section('page-title', 'Dashboard')

    <div class="space-y-6">

        {{-- Greeting --}}
        <div>
            <h2 class="text-2xl font-bold text-gray-900">
                Xin chào, {{ auth()->user()->name }} 👋
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Chào mừng đến EMS — Hệ thống Quản lý Thi trắc nghiệm
            </p>
        </div>

        {{-- Stat Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            @php
                $stats = [
                    ['label' => 'Người dùng',    'value' => '—', 'color' => 'indigo',  'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
                    ['label' => 'Môn học',       'value' => '—', 'color' => 'emerald', 'icon' => 'M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222'],
                    ['label' => 'Lớp học phần', 'value' => '—', 'color' => 'blue',    'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
                    ['label' => 'Đề thi',        'value' => '—', 'color' => 'violet',  'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                ];
            @endphp

            @foreach($stats as $stat)
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-{{ $stat['color'] }}-50 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 text-{{ $stat['color'] }}-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="{{ $stat['icon'] }}"/>
                    </svg>
                </div>
                <div>
                    <div class="text-2xl font-bold text-gray-900">{{ $stat['value'] }}</div>
                    <div class="text-sm text-gray-500">{{ $stat['label'] }}</div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Placeholder content --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-8 text-center">
            <svg class="mx-auto w-12 h-12 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <h3 class="text-gray-500 font-medium">Dashboard đang được phát triển</h3>
            <p class="text-gray-400 text-sm mt-1">Biểu đồ và thống kê sẽ được thêm vào Tuần 4</p>
        </div>

    </div>
</x-app-layout>
