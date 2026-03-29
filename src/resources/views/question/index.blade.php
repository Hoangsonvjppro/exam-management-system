<x-app-layout>
   @section('page-title', 'Câu hỏi')
   <div class="p-8 space-y-8 flex-1 bg-surface-container-low flex-1">
      <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-4">
         <div>
            <nav class="flex text-xs font-bold tracking-widest text-secondary uppercase mb-2 gap-2">
               <a href="{{ route('lecturer.dashboard') }}" class="text-primary hover:underline">Dashboard</a>
               <span>/</span>
               <a href="{{ route('lecturer.questions.index') }}" class="text-secondary">Questions</a>
            </nav>
            <h2 class="text-2xl md:text-3xl font-bold text-navy-900 leading-tight">Quản lý câu hỏi</h2>
            <p class="text-sm text-text-muted mt-1">Danh sách các câu hỏi có trong hệ thống</p>
         </div>
         <div class="flex gap-3">
            <a href="{{ route('lecturer.questions.export', request()->query()) }}"
               class="bg-white text-navy-900 border border-border-clean px-4 py-2 rounded-lg text-sm font-semibold flex items-center gap-2 hover:bg-surface-1 transition-all shadow-sm">
               <x-ui-icon name="arrow-down-tray" class="w-4 h-4" />
               Xuất Excel
            </a>
            <a href="{{ route('lecturer.questions.create') }}"
               class="bg-navy-900 text-white px-4 py-2 rounded-lg text-sm font-semibold flex items-center gap-2 hover:bg-navy-950 transition-all shadow-sm">
               <x-ui-icon name="plus" class="w-4 h-4" />
               Thêm câu hỏi mới</a>
         </div>
      </div>


      <form action="{{ route('lecturer.questions.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
         <div class="bg-white p-4 rounded-xl flex flex-col gap-1 shadow-sm border border-border-clean/50">
            <span class="text-xs font-bold text-text-muted uppercase tracking-wider">Môn học</span>
            <select onchange="this.form.submit()" name="sub-sel-ques" id="sub-sel-ques" class="border-none bg-transparent p-0 font-bold text-navy-900 focus:ring-0 text-sm cursor-pointer">
               <option value="">Tất cả môn học</option>
               @foreach ($subjects as $sj)
               <option value="{{ $sj->code }}" {{ request()->input('sub-sel-ques') == $sj->code ? ' selected' : '' }}>{{ $sj->name }}</option>
               @endforeach
            </select>
         </div>
         <div class="bg-white p-4 rounded-xl flex flex-col gap-1 shadow-sm border border-border-clean/50">
            <span class="text-xs font-bold text-text-muted uppercase tracking-wider">Mức độ</span>
            <select onchange="this.form.submit()" name="diff-sel-ques" id="diff-sel-ques" class="border-none bg-transparent p-0 font-bold text-navy-900 focus:ring-0 text-sm cursor-pointer">
               <option value="">Tất cả mức độ</option>
               @foreach ($difficulties as $diff)
               <option value="{{ $diff->code }}" {{ request()->input('diff-sel-ques') === $diff->code ? ' selected' : '' }}>{{ $diff->name }}</option>
               @endforeach
            </select>
         </div>
         <div class="bg-white p-4 rounded-xl flex flex-col gap-1 shadow-sm border border-border-clean/50">
            <span class="text-xs font-bold text-text-muted uppercase tracking-wider">Chương</span>
            <select onchange="this.form.submit()" name="chap-sel-ques" id="chap-sel-ques" class="border-none bg-transparent p-0 font-bold text-navy-900 focus:ring-0 text-sm cursor-pointer">
               <option value="">Tất cả chương</option>
               @foreach ($chapters as $chap)
               <option value="{{ $chap->id }}" {{ (string) request()->input('chap-sel-ques') === (string) $chap->id ? ' selected' : '' }}>{{ $chap->name }}</option>
               @endforeach
            </select>
         </div>
         <div class="bg-white p-4 rounded-xl flex flex-col gap-1 shadow-sm border border-border-clean/50">
            <span class="text-xs font-bold text-text-muted uppercase tracking-wider">Trạng thái</span>
            <select onchange="this.form.submit()" name="status-sel-ques" id="status-sel-ques" class="border-none bg-transparent p-0 font-bold text-navy-900 focus:ring-0 text-sm cursor-pointer">
               <option value="">Tất cả trạng thái</option>
               <option value="approved" {{ request()->input('status-sel-ques') === 'approved' ? ' selected' : '' }}>Đã duyệt</option>
               <option value="draft" {{ request()->input('status-sel-ques') === 'draft' ? ' selected' : '' }}>Chờ duyệt</option>
               <option value="hidden" {{ request()->input('status-sel-ques') === 'hidden' ? ' selected' : '' }}>Bản nháp</option>
            </select>
         </div>
      </form>

      <!-- Questions Table Card -->
      <div class="bg-white rounded-2xl overflow-hidden">
         <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
               <thead>
                  <tr class="bg-surface-1 border-b border-border-clean">
                     <th class="px-6 py-4 text-xs font-bold text-text-muted uppercase tracking-wider">Nội dung
                        câu hỏi</th>
                     <th class="px-6 py-4 text-xs font-bold text-text-muted uppercase tracking-wider">Môn học
                     </th>
                     <th class="px-6 py-4 text-xs font-bold text-text-muted uppercase tracking-wider">Chương
                     </th>
                     <th class="px-6 py-4 text-xs font-bold text-text-muted uppercase tracking-wider">Mức độ
                     </th>
                     <th
                        class="px-6 py-4 text-xs font-bold text-text-muted uppercase tracking-wider text-right">
                        Thao tác</th>
                  </tr>
               </thead>
               <tbody class="divide-y divide-surface-container-low">
                  @forelse ($questions as $question)
                  @php
                  $difficultyClassMap = [
                  'remember' => 'bg-green-100 text-green-700',
                  'understand' => 'bg-blue-100 text-blue-700',
                  'apply' => 'bg-orange-100 text-orange-700',
                  'analyze' => 'bg-red-100 text-red-700',
                  ];

                  $difficultyLabel = $question->difficultyLevel?->name ?? ucfirst((string) $question->difficulty);
                  $difficultyClass = $difficultyClassMap[$question->difficulty] ?? 'bg-slate-100 text-slate-700';
                  @endphp

                  <tr class="hover:bg-surface-1/50 transition-colors border-b border-border-clean/50">
                     <td class="px-6 py-4 max-w-md">
                        <p class="text-sm font-medium text-navy-900 line-clamp-2 leading-relaxed">{{ \Illuminate\Support\Str::limit(trim(strip_tags($question->content)), 150) }}</p>
                        <p class="text-xs text-text-muted mt-1 tracking-tight">ID: Q-{{ $question->id }} • Cập nhật: {{ $question->updated_at?->diffForHumans() ?? 'N/A' }}</p>
                     </td>
                     <td class="px-6 py-4">
                        <span class="text-sm font-bold text-navy-900">{{ $question->subject?->name ?? 'N/A' }}</span>
                     </td>
                     <td class="px-6 py-4">
                        <span class="text-sm text-text-muted">{{ $question->chapter?->name ?? 'Chưa gán chương' }}</span>
                     </td>
                     <td class="px-6 py-4">
                        <span class="px-3 py-1 text-xs font-bold rounded-full uppercase {{ $difficultyClass }}">{{ $difficultyLabel }}</span>
                     </td>
                     <td class="px-6 py-4 text-right">
                        <div class="flex justify-end items-center gap-2" x-data="{ confirming: false }">
                           <template x-if="!confirming">
                              <div class="flex items-center gap-2">
                                 <a href="{{ route('lecturer.questions.edit', $question) }}" class="p-2 text-text-muted hover:text-navy-900 hover:bg-surface-1 rounded-lg transition-all" title="Sửa">
                                    <x-ui-icon name="pencil-square" class="w-4 h-4" />
                                 </a>
                                 <button type="button" @click="confirming = true" class="p-2 text-text-muted hover:text-red-600 hover:bg-red-50 rounded-lg transition-all" title="Xoá">
                                    <x-ui-icon name="trash" class="w-4 h-4" />
                                 </button>
                              </div>
                           </template>

                           <div x-show="confirming" x-cloak class="inline-flex items-center gap-2 bg-red-50 px-2 py-1 rounded border border-red-100">
                              <span class="text-[10px] text-red-600 font-bold">Xoá ?</span>
                              <form method="POST" action="{{ route('lecturer.questions.destroy', $question) }}" class="inline">
                                 @csrf @method('DELETE')
                                 <button type="submit" class="text-[11px] font-bold text-red-700 hover:underline">Xoá</button>
                              </form>
                              <button type="button" @click="confirming = false" class="text-[11px] text-navy-400 hover:underline">Hủy</button>
                           </div>
                        </div>
                     </td>
                  </tr>
                  @empty
                  <tr>
                     <td colspan="5" class="px-6 py-10 text-center text-secondary">Chưa có câu hỏi nào khớp bộ lọc hiện tại.</td>
                  </tr>
                  @endforelse
               </tbody>
            </table>
         </div>
         <!-- Pagination Component -->
         <div
            class="px-6 py-5 bg-surface-container-lowest flex items-center justify-between border-t border-surface-container-low">
            <p class="text-sm text-secondary">
               Hiển thị <span class="font-bold text-on-surface">{{ $questions->firstItem() ?? 0 }} - {{ $questions->lastItem() ?? 0 }}</span> trong số <span
                  class="font-bold text-on-surface">{{ number_format($questions->total()) }}</span> câu hỏi
            </p>
            <div class="flex items-center gap-2">
               @if ($questions->onFirstPage())
               <span class="w-10 h-10 flex items-center justify-center rounded-xl border border-surface-container-high text-secondary/50 cursor-not-allowed">
                  <span class="material-symbols-outlined" data-icon="chevron_left">chevron_left</span>
               </span>
               @else
               <a href="{{ $questions->previousPageUrl() }}" class="w-10 h-10 flex items-center justify-center rounded-xl border border-surface-container-high text-secondary hover:bg-surface-container-low transition-all">
                  <span class="material-symbols-outlined" data-icon="chevron_left">chevron_left</span>
               </a>
               @endif

               @php
               $startPage = max(1, $questions->currentPage() - 1);
               $endPage = min($questions->lastPage(), $questions->currentPage() + 1);
               @endphp

               @for ($page = $startPage; $page <= $endPage; $page++)
                  @if ($page===$questions->currentPage())
                  <span class="w-10 h-10 flex items-center justify-center rounded-xl bg-primary text-white font-bold shadow-md shadow-primary/20">{{ $page }}</span>
                  @else
                  <a href="{{ $questions->url($page) }}" class="w-10 h-10 flex items-center justify-center rounded-xl text-secondary hover:bg-surface-container-low transition-all">{{ $page }}</a>
                  @endif
                  @endfor

                  @if ($questions->hasMorePages())
                  <a href="{{ $questions->nextPageUrl() }}" class="w-10 h-10 flex items-center justify-center rounded-xl border border-surface-container-high text-secondary hover:bg-surface-container-low transition-all">
                     <span class="material-symbols-outlined" data-icon="chevron_right">chevron_right</span>
                  </a>
                  @else
                  <span class="w-10 h-10 flex items-center justify-center rounded-xl border border-surface-container-high text-secondary/50 cursor-not-allowed">
                     <span class="material-symbols-outlined" data-icon="chevron_right">chevron_right</span>
                  </span>
                  @endif
            </div>
         </div>
      </div>
   </div>
</x-app-layout>