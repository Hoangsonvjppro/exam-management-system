<x-app-layout>
   @section('page-title', 'Câu hỏi')
   <div class="p-8 space-y-8 flex-1 bg-surface-container-low flex-1">
      <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
         <div>
            <nav class="flex text-[10px] font-bold tracking-widest text-secondary uppercase mb-2 gap-2">

               <a href="{{ route('lecturer.dashboard') }}" class="text-primary">Dashboard</a>
               <span>/</span>
               <a href="{{ route('lecturer.questions.index') }}" class="">Questions</a>

            </nav>
            <h2 class="text-3xl font-extrabold text-primary font-headline tracking-tight"> Quản lý câu hỏi</h2>
            <p class="text-on-surface-variant mt-1"> Danh sách các câu hỏi có trong hệ thống </p>
         </div>
         <div class="w-full lg:ml-auto lg:max-w-fit flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-3">
            <form action="{{ route('lecturer.questions.index') }}" method="GET" class="w-full sm:w-[22rem] lg:w-[20rem] flex items-center gap-2">
               <input type="hidden" name="sub-sel-ques" value="{{ request()->input('sub-sel-ques') }}">
               <input type="hidden" name="diff-sel-ques" value="{{ request()->input('diff-sel-ques') }}">
               <input type="hidden" name="chap-sel-ques" value="{{ request()->input('chap-sel-ques') }}">
               <input type="hidden" name="status-sel-ques" value="{{ request()->input('status-sel-ques') }}">

               <div class="relative flex-1">
                  <span class="material-symbols-outlined text-secondary absolute left-3 top-1/2 -translate-y-1/2 text-[18px]" data-icon="search">search</span>
                  @php
                  $clearSearchQuery = request()->except(['q', 'page']);
                  $hasSearchKeyword = filled(request()->input('q'));
                  @endphp
                  <input type="text"
                     name="q"
                     value="{{ request()->input('q') }}"
                     placeholder="Tìm nội dung câu hỏi..."
                     class="w-full bg-white text-on-surface border border-surface-container-high pl-10 pr-10 py-2.5 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                  <a href="{{ route('lecturer.questions.index', $clearSearchQuery) }}"
                     class="absolute right-2 top-1/2 -translate-y-1/2 w-7 h-7 rounded-md items-center justify-center text-secondary hover:text-primary hover:bg-surface-container-low transition-colors {{ $hasSearchKeyword ? 'flex' : 'hidden' }}"
                     title="Xóa tìm kiếm"
                     aria-label="Xóa tìm kiếm">
                     <span class="material-symbols-outlined text-[16px]" data-icon="close">close</span>
                  </a>
               </div>
               <button type="submit" class="shrink-0 px-3 py-2.5 rounded-lg bg-primary text-white text-xs font-semibold hover:opacity-90 transition-opacity">Tìm</button>
            </form>

            <a href="{{ route('lecturer.questions.export', request()->query()) }}"
               class="bg-white text-primary border border-surface-container-high px-5 py-2.5 rounded-xl font-semibold inline-flex items-center justify-center gap-2 hover:bg-surface-bright transition-all whitespace-nowrap">
               <span class="material-symbols-outlined text-sm" data-icon="file_download">file_download</span>
               Xuất Excel
            </a>
            <a href="{{ route('lecturer.questions.create') }}"
               class="bg-white text-primary border border-surface-container-high px-5 py-2.5 rounded-xl font-semibold inline-flex items-center justify-center gap-2 hover:bg-surface-bright transition-all whitespace-nowrap"><span
                  class="material-symbols-outlined text-sm" data-icon="add">add</span>
               Thêm câu hỏi mới</a>
         </div>
      </div>

      <form action="{{ route('lecturer.questions.index') }}" method="GET">
         <input type="hidden" name="q" value="{{ request()->input('q') }}">
         <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-xl flex flex-col gap-1 shadow-sm">
               <span class="text-[10px] font-bold text-secondary uppercase tracking-wider">Môn học</span>
               <select data-auto-submit="form" name="sub-sel-ques" id="sub-sel-ques"
                  class="border-none bg-transparent p-0  font-semibold text-on-surface focus:ring-0">
                  <option value="">Tất cả môn học</option>
                  @foreach ($subjects as $sj)
                  <option value="{{ $sj->code }}" {{ request()->input('sub-sel-ques') == $sj->code ? ' selected' : '' }}>
                     {{ $sj->name }}
                  </option>
                  @endforeach
               </select>
            </div>
            <div class="bg-white p-4 rounded-xl flex flex-col gap-1 shadow-sm">
               <span class="text-[10px] font-bold text-secondary uppercase tracking-wider">Mức độ</span>
               <select data-auto-submit="form" name="diff-sel-ques" id="diff-sel-ques"
                  class="border-none bg-transparent p-0 font-semibold text-on-surface focus:ring-0">
                  <option value="">Tất cả mức độ</option>
                  @foreach ($difficulties as $diff)
                  <option value="{{ $diff->code }}" {{ (string) request()->input('diff-sel-ques') === (string) $diff->code ? ' selected' : '' }}>{{ $diff->name }}</option>
                  @endforeach
               </select>
            </div>
            <div class="bg-white p-4 rounded-xl flex flex-col gap-1 shadow-sm">
               <span class="text-[10px] font-bold text-secondary uppercase tracking-wider">Chương</span>
               <select data-auto-submit="form" name="chap-sel-ques" id="chap-sel-ques"
                  class="border-none bg-transparent p-0 font-semibold text-on-surface focus:ring-0">
                  <option value="">Tất cả chương</option>
                  @foreach ($chapters as $chap)
                  <option value="{{ $chap->id }}" {{ (string) request()->input('chap-sel-ques') === (string) $chap->id ? ' selected' : '' }}>{{ $chap->name }}</option>
                  @endforeach
               </select>
            </div>
         </div>
      </form>
      <!-- Questions Table Card -->

      <div class="bg-white rounded-2xl overflow-hidden shadow-sm">
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
                  @foreach ($questions as $question)
                  <tr class="hover:bg-surface-container-low/30 transition-colors">
                     <td class="px-6 py-4 max-w-md">
                        <div class="text-sm font-medium text-on-surface line-clamp-2 prose-sm">{!! $question->content !!}</div>
                        <p class="text-[10px] text-secondary mt-1">ID: {{ $question->id }} • Cập nhật: {{ $question->updated_at?->diffForHumans() ?? 'N/A' }}</p>
                     </td>
                     <td class="px-6 py-4">
                        <span class="text-sm font-semibold text-primary">{{ $question->subject->name ?? 'Chưa xác định' }}</span>
                     </td>
                     <td class="px-6 py-4">
                        <span class="text-sm text-secondary">{{ $question->chapter->name ?? 'Chưa xác định' }}</span>
                     </td>
                     <td class="px-6 py-4 text-center">
                        @php
                        $difficultyClass = match($question->difficulty) {
                        'remember' => 'bg-green-100 text-green-700',
                        'understand' => 'bg-blue-100 text-blue-700',
                        'apply' => 'bg-orange-100 text-orange-700',
                        'analyze' => 'bg-red-100 text-red-700',
                        default => 'bg-gray-100 text-gray-700'
                        };
                        @endphp
                        <span class="px-3 py-1 text-[10px] font-bold rounded-full uppercase inline-flex whitespace-nowrap {{ $difficultyClass }}">
                           {{ $question->difficultyLevel->name ?? $question->difficulty }}
                        </span>
                     </td>
                     <td class="px-6 py-4 text-right">
                        <div class="relative inline-flex justify-end" x-data="confirmActionState()" @keydown.escape.window="confirming = false">
                           <div class="flex items-center gap-2" x-show="!confirming">
                              <a href="{{ route('lecturer.questions.edit', $question) }}"
                                 class="p-2 text-secondary hover:text-primary hover:bg-primary/10 rounded-lg transition-all" title="Sửa">
                                 <span class="material-symbols-outlined text-sm" data-icon="edit">edit</span>
                              </a>
                              <button type="button" @click="confirming = true"
                                 class="p-2 text-secondary hover:text-error hover:bg-error-container/20 rounded-lg transition-all" title="Xóa">
                                 <span class="material-symbols-outlined text-sm" data-icon="delete">delete</span>
                              </button>
                           </div>

                           <div x-show="confirming"
                              x-cloak
                              x-transition.opacity.duration.150ms
                              @click.outside="confirming = false"
                              class="absolute right-0 top-full mt-2 z-20 w-[270px] rounded-xl border border-red-200 bg-white p-3 text-left shadow-[0_12px_30px_rgba(15,23,42,0.14)]">
                              <p class="text-[12px] font-bold text-red-700">Xác nhận xóa câu hỏi</p>
                              <p class="mt-1 text-[11px] leading-relaxed text-text-muted">Bạn có chắc chắn muốn xóa câu hỏi này? Hành động này không thể hoàn tác.</p>
                              <div class="mt-3 flex items-center justify-end gap-2">
                                 <button type="button"
                                    @click="confirming = false"
                                    class="inline-flex items-center rounded-md border border-border-clean px-2.5 py-1.5 text-[11px] font-semibold text-text-muted hover:bg-surface-1 transition-colors">
                                    Hủy
                                 </button>
                                 <form method="POST" action="{{ route('lecturer.questions.destroy', $question) }}" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                       class="inline-flex items-center rounded-md bg-red-600 px-2.5 py-1.5 text-[11px] font-semibold text-white hover:bg-red-700 transition-colors">
                                       Xác nhận xóa
                                    </button>
                                 </form>
                              </div>
                           </div>
                        </div>
                     </td>
                  </tr>
                  @endforeach
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
               {{ $questions->links() }}
            </div>
         </div>
      </div>
   </div>
</x-app-layout>