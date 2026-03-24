<x-app-layout>
   @section('page-title', 'Câu hỏi')
   <div class="p-8 space-y-8 flex-1 bg-surface-container-low flex-1">
      <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-4">
         <div>
            <nav class="flex text-[10px] font-bold tracking-widest text-secondary uppercase mb-2 gap-2">

               <a href="{{ route('lecturer.dashboard') }}" class="text-primary">Dashboard</a>
               <span>/</span>
               <a href="{{ route('lecturer.questions.index') }}" class="bg-blue-500">Questions</a>

            </nav>
            <h2 class="text-3xl font-extrabold text-primary font-headline tracking-tight">Quản lý câu hỏi</h2>
            <p class="text-on-surface-variant mt-1">Danh sách các câu hỏi có trong hệ thống</p>
         </div>
         <div class="flex gap-3">
            <a href="{{ route('lecturer.questions.export', request()->query()) }}"
               class="bg-white text-primary border border-surface-container-high px-5 py-2.5 rounded-xl font-semibold flex items-center gap-2 hover:bg-surface-bright transition-all">
               <span class="material-symbols-outlined text-sm" data-icon="file_download">file_download</span>
               Xuất Excel
            </a>
            <a href="{{ route('lecturer.questions.create') }}"
               class="bg-white text-primary border border-surface-container-high px-5 py-2.5 rounded-xl font-semibold flex items-center gap-2 hover:bg-surface-bright transition-all"><span
                  class="material-symbols-outlined text-sm" data-icon="add">add</span>
               Thêm câu hỏi mới</a>
         </div>
      </div>

      @if (session('status'))
      <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
         {{ session('status') }}
      </div>
      @endif

      <form action="{{ route('lecturer.questions.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
         <div class="bg-white p-4 rounded-xl flex flex-col gap-1">
            <span class="text-[10px] font-bold text-secondary uppercase tracking-wider">Môn học</span>
            <select onchange="this.form.submit()" name="sub-sel-ques" id="sub-sel-ques" class="border-none bg-transparent p-0 font-semibold text-on-surface focus:ring-0">
               <option value="">Tất cả môn học</option>
               @foreach ($subjects as $sj)
               <option value="{{ $sj->code }}" {{ request()->input('sub-sel-ques') == $sj->code ? ' selected' : '' }}>{{ $sj->name }}</option>
               @endforeach
            </select>
         </div>
         <div class="bg-white p-4 rounded-xl flex flex-col gap-1">
            <span class="text-[10px] font-bold text-secondary uppercase tracking-wider">Mức độ</span>
            <select onchange="this.form.submit()" name="diff-sel-ques" id="diff-sel-ques" class="border-none bg-transparent p-0 font-semibold text-on-surface focus:ring-0">
               <option value="">Tất cả mức độ</option>
               @foreach ($difficulties as $diff)
               <option value="{{ $diff->code }}" {{ request()->input('diff-sel-ques') === $diff->code ? ' selected' : '' }}>{{ $diff->name }}</option>
               @endforeach
            </select>
         </div>
         <div class="bg-white p-4 rounded-xl flex flex-col gap-1">
            <span class="text-[10px] font-bold text-secondary uppercase tracking-wider">Chương</span>
            <select onchange="this.form.submit()" name="chap-sel-ques" id="chap-sel-ques" class="border-none bg-transparent p-0 font-semibold text-on-surface focus:ring-0">
               <option value="">Tất cả chương</option>
               @foreach ($chapters as $chap)
               <option value="{{           <select onchange="this.form.submit()" name="diff-sel-ques" id="diff-sel-ques" class="border-none bg-transparent p-0 font-semibold text-on-surface focus:ring-0">
               <option value="">Tất cả mức độ</option>
               @foreach ($difficulties as $diff)
               <option value="{{ $diff->code }}" {{ request()->input('diff-sel-ques') === $diff->code ? ' selected' : '' }}>{{ $diff->name }}</option>
               @endforeach
            </select> $chap->id }}" {{ (string) request()->input('chap-sel-ques') === (string) $chap->id ? ' selected' : '' }}>{{ $chap->name }}</option>
            @endforeach
            </select>
         </div>
         <div class="bg-white p-4 rounded-xl flex flex-col gap-1">
            <span class="text-[10px] font-bold text-secondary uppercase tracking-wider">Trạng thái</span>
            <select onchange="this.form.submit()" name="status-sel-ques" id="status-sel-ques" class="border-none bg-transparent p-0 font-semibold text-on-surface focus:ring-0">
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
                  <tr class="bg-surface-container-lowest border-b border-surface-container-low">
                     <th class="px-6 py-4 text-[10px] font-extrabold text-secondary uppercase tracking-widest">Nội dung
                        câu hỏi</th>
                     <th class="px-6 py-4 text-[10px] font-extrabold text-secondary uppercase tracking-widest">Môn học
                     </th>
                     <th class="px-6 py-4 text-[10px] font-extrabold text-secondary uppercase tracking-widest">Chương
                     </th>
                     <th class="px-6 py-4 text-[10px] font-extrabold text-secondary uppercase tracking-widest">Mức độ
                     </th>
                     <th
                        class="px-6 py-4 text-[10px] font-extrabold text-secondary uppercase tracking-widest text-right">
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

                  <tr class="hover:bg-surface-container-low/30 transition-colors">
                     <td class="px-6 py-4 max-w-md">
                        <p class="text-sm font-medium text-on-surface line-clamp-1">{{ \Illuminate\Support\Str::limit(trim(strip_tags($question->content)), 110) }}</p>
                        <p class="text-[10px] text-secondary mt-1">ID: Q-{{ $question->id }} • Cập nhật: {{ $question->updated_at?->diffForHumans() ?? 'N/A' }}</p>
                     </td>
                     <td class="px-6 py-4">
                        <span class="text-sm font-semibold text-primary">{{ $question->subject?->name ?? 'N/A' }}</span>
                     </td>
                     <td class="px-6 py-4">
                        <span class="text-sm text-secondary">{{ $question->chapter?->name ?? 'Chưa gán chương' }}</span>
                     </td>
                     <td class="px-6 py-4">
                        <span class="px-3 py-1 text-[10px] font-bold rounded-full uppercase {{ $difficultyClass }}">{{ $difficultyLabel }}</span>
                     </td>
                     <td class="px-6 py-4 text-right">
                        <div class="flex justify-end gap-2">
                           <a href="{{ route('lecturer.questions.edit', $question) }}" class="p-2 text-secondary hover:text-primary hover:bg-primary/10 rounded-lg transition-all" type="button">
                              <span class="material-symbols-outlined text-sm" data-icon="edit">edit</span>
                           </a>
                           <form method="POST" action="{{ route('lecturer.questions.destroy', $question) }}" onsubmit="return confirm('Bạn có chắc muốn xoá câu hỏi này không?')">
                              @csrf
                              @method('DELETE')
                              <button class="p-2 text-secondary hover:text-error hover:bg-error-container/20 rounded-lg transition-all" type="submit">
                                 <span class="material-symbols-outlined text-sm" data-icon="delete">delete</span>
                              </button>
                           </form>
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