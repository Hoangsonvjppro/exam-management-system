<x-app-layout>
    @section('title', 'Dashboard — Sinh viên')
    @section('page-title', 'Dashboard')

    <div class="space-y-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Xin chào, {{ auth()->user()->name }} 👋</h2>
            <p class="mt-1 text-sm text-gray-500">
                @if(auth()->user()->student_code)
                    MSSV: <span class="font-medium">{{ auth()->user()->student_code }}</span>
                    &nbsp;·&nbsp; Lớp: <span class="font-medium">{{ auth()->user()->class_name ?? '—' }}</span>
                @endif
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            @foreach([
                ['label' => 'Bài thi đã làm',  'color' => 'violet', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                ['label' => 'Lớp học phần',    'color' => 'blue',   'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
                ['label' => 'Tỉ lệ chuyên cần','color' => 'emerald','icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
            ] as $stat)
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-{{ $stat['color'] }}-50 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 text-{{ $stat['color'] }}-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="{{ $stat['icon'] }}"/>
                    </svg>
                </div>
                <div>
                    <div class="text-2xl font-bold text-gray-900">—</div>
                    <div class="text-sm text-gray-500">{{ $stat['label'] }}</div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-8 text-center">
            <p class="text-gray-400 text-sm">Dashboard sinh viên đang được phát triển</p>
        </div>
    </div>
</x-app-layout>
