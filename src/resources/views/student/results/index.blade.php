<x-app-layout>
    @section('title', 'Kết quả học tập')
    @section('page-title', 'Kết quả học tập')

    <div class="space-y-6">

        {{-- Header & Semester Filter --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-3xl font-bold text-navy-900 leading-tight">Kết quả học tập</h2>
                <p class="text-sm font-medium text-text-muted mt-1">Xem điểm tổng kết và tiến độ các môn học.</p>
            </div>

            @if($semesters->isNotEmpty())
            <form method="GET" action="{{ route('student.results.index') }}" x-data="{ submitOnSelect: true }"
                  class="flex items-center gap-2 bg-white px-3 py-2 rounded-[8px] border-[0.5px] border-border-clean shadow-sm">
                <span class="text-xs font-semibold uppercase tracking-wider text-text-muted">Học kỳ:</span>
                <select name="semester_id" onchange="this.form.submit()" class="text-sm font-bold text-navy-900 border-none bg-transparent focus:ring-0 p-0 cursor-pointer pr-6">
                    @foreach($semesters as $semester)
                        <option value="{{ $semester->id }}" @selected($currentSemester->id === $semester->id)>
                            {{ $semester->name }}
                        </option>
                    @endforeach
                </select>
            </form>
            @endif
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <x-card class="flex flex-col p-5 bg-gradient-to-br from-white to-blue-50/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-[8px] bg-blue-100 flex items-center justify-center shrink-0">
                        <x-ui-icon name="academic-cap" class="w-5 h-5 text-blue-600" />
                    </div>
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-text-muted mb-0.5">Môn học</p>
                        <h3 class="text-2xl font-black text-navy-900 leading-none">{{ $summary['total_sections'] }}</h3>
                    </div>
                </div>
            </x-card>

            <x-card class="flex flex-col p-5 bg-gradient-to-br from-white to-indigo-50/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-[8px] bg-indigo-100 flex items-center justify-center shrink-0">
                        <x-ui-icon name="document-text" class="w-5 h-5 text-indigo-600" />
                    </div>
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-text-muted mb-0.5">Tín chỉ</p>
                        <h3 class="text-2xl font-black text-navy-900 leading-none">{{ $summary['total_credits'] }}</h3>
                    </div>
                </div>
            </x-card>

            <x-card class="flex flex-col p-5 bg-gradient-to-br from-white to-emerald-50/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-[8px] bg-emerald-100 flex items-center justify-center shrink-0">
                        <x-ui-icon name="chart-bar" class="w-5 h-5 text-emerald-600" />
                    </div>
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-text-muted mb-0.5">Điểm TB (10)</p>
                        <h3 class="text-2xl font-black text-navy-900 leading-none">{{ number_format($summary['gpa_10'], 2) }}</h3>
                    </div>
                </div>
            </x-card>

            <x-card class="flex flex-col p-5 bg-gradient-to-br from-white to-purple-50/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-[8px] bg-purple-100 flex items-center justify-center shrink-0">
                        <x-ui-icon name="star" class="w-5 h-5 text-purple-600" />
                    </div>
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-text-muted mb-0.5">Điểm TB (4)</p>
                        <h3 class="text-2xl font-black text-navy-900 leading-none">{{ number_format($summary['gpa_4'], 2) }}</h3>
                    </div>
                </div>
            </x-card>
        </div>

        {{-- Grade Table List --}}
        @if($sections->isEmpty())
            <x-card padding="true">
                <div class="text-center py-10 bg-surface-0 border-[0.5px] border-border-clean border-dashed rounded-[8px]">
                    <x-ui-icon name="inbox" class="w-12 h-12 text-blue-200 mx-auto mb-3" />
                    <p class="text-navy-900 font-semibold">Chưa có dữ liệu học tập</p>
                    <p class="text-text-muted text-[13px] font-medium mt-1">Bạn chưa đăng ký môn học nào trong học kỳ này.</p>
                </div>
            </x-card>
        @else
            <x-card>
                <div class="w-full overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-surface-0 border-b border-border-clean text-[10px] uppercase tracking-wider font-bold text-text-muted">
                                <th class="px-5 py-4 w-[40px]"></th>
                                <th class="px-5 py-4">Môn học</th>
                                <th class="px-5 py-4 text-center">Tín chỉ</th>
                                <th class="px-5 py-4 text-right">Điểm hệ 10</th>
                                <th class="px-5 py-4 text-center">Điểm hệ 4</th>
                                <th class="px-5 py-4 text-center">Điểm chữ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y border-border-clean" x-data="{ expandedRows: [] }">
                            @foreach($sections as $index => $section)
                                @php
                                    $isPass = $section->final_score_10 >= 4.0;
                                @endphp
                                <!-- Main Row -->
                                <tr class="hover:bg-surface-0/50 transition-colors cursor-pointer group"
                                    @click="if(expandedRows.includes({{ $index }})) { expandedRows = expandedRows.filter(i => i !== {{ $index }}) } else { expandedRows.push({{ $index }}) }">
                                    <td class="px-5 py-4 align-middle text-center">
                                        <div class="w-6 h-6 rounded-full bg-surface-0 border border-border-clean flex items-center justify-center text-text-muted transition-transform duration-200 group-hover:border-blue-300 group-hover:bg-blue-50"
                                            :class="{ 'rotate-180': expandedRows.includes({{ $index }}) }">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 align-middle">
                                        <div class="flex flex-col">
                                            <span class="font-bold text-navy-900">{{ $section->subject->name ?? 'N/A' }}</span>
                                            <span class="text-[11px] font-medium uppercase tracking-wider text-text-muted mt-0.5">{{ $section->code }}</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 align-middle text-center font-bold text-text-muted">{{ $section->subject->credits ?? 0 }}</td>
                                    
                                    <td class="px-5 py-4 align-middle text-right">
                                        <span class="font-black text-[15px] {{ $isPass ? 'text-navy-900' : 'text-red-600' }}">
                                            {{ number_format($section->final_score_10, 2) }}
                                        </span>
                                        @if(!$section->has_all_grades)
                                            <span title="Chưa có đủ cột điểm kết thúc" class="ml-1 text-orange-500 font-bold">*</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 align-middle text-center">
                                        <span class="font-bold text-text-muted">{{ number_format($section->final_score_4, 2) }}</span>
                                    </td>
                                    <td class="px-5 py-4 align-middle text-center">
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-[8px] font-black text-sm
                                            @if($section->letter_grade == 'A') bg-emerald-100 text-emerald-700
                                            @elseif(in_array($section->letter_grade, ['B+', 'B'])) bg-blue-100 text-blue-700
                                            @elseif(in_array($section->letter_grade, ['C+', 'C'])) bg-amber-100 text-amber-700
                                            @elseif(in_array($section->letter_grade, ['D+', 'D'])) bg-orange-100 text-orange-700
                                            @else bg-red-100 text-red-700 @endif">
                                            {{ $section->letter_grade }}
                                        </span>
                                    </td>
                                </tr>

                                <!-- Expanded Details Row -->
                                <tr x-show="expandedRows.includes({{ $index }})" x-transition x-cloak class="bg-surface-0/30">
                                    <td colspan="6" class="px-5 py-4 !border-t-0">
                                        <div class="pl-14 pr-4">
                                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 bg-white p-4 rounded-[8px] border-[0.5px] border-border-clean shadow-sm">
                                                @forelse($section->gradeColumns as $column)
                                                    @php
                                                        $grade = $column->studentGrades->first();
                                                        $score = $grade && $grade->score !== null ? number_format($grade->score, 2) : '-';
                                                    @endphp
                                                    <div class="flex flex-col">
                                                        <span class="text-[10px] font-bold uppercase tracking-wider text-text-muted">{{ $column->name }} ({{ $column->weight }}%)</span>
                                                        <span class="text-sm font-black text-navy-900 mt-1">{{ $score }}</span>
                                                    </div>
                                                @empty
                                                    <div class="col-span-4 text-center py-2 text-sm text-text-muted font-medium italic">
                                                        Chưa thiết lập cột điểm cho môn học này
                                                    </div>
                                                @endforelse
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-card>
        @endif

    </div>
</x-app-layout>
