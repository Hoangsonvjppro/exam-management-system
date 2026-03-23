<x-app-layout>
   @section('page-title', 'Câu hỏi')
   <div class="p-8 space-y-8 flex-1 bg-surface-container-low flex-1">
      <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-4">
         <div>
            <nav class="flex text-[10px] font-bold tracking-widest text-secondary uppercase mb-2 gap-2">

               <a href="#" class="text-primary">Dashboard</a>
               <span>/</span>
               <a href="#" class="bg-blue-500">Questions</a>

            </nav>
            <h2 class="text-3xl font-extrabold text-primary font-headline tracking-tight"> Quan ly cau hoi</h3>
               <p class="text-on-surface-variant mt-1"> Danh Sach cac cau hoi co trong he thong </p>
         </div>
         <div class="flex gap-3">
            <button
               class="bg-white text-primary border border-surface-container-high px-5 py-2.5 rounded-xl font-semibold flex items-center gap-2 hover:bg-surface-bright transition-all">
               <span class="material-symbols-outlined text-sm" data-icon="file_download">file_download</span>
               Xuất Excel
            </button>
            <a href="#"
               class="bg-white text-primary border border-surface-container-high px-5 py-2.5 rounded-xl font-semibold flex items-center gap-2 hover:bg-surface-bright transition-all"><span
                  class="material-symbols-outlined text-sm" data-icon="add">add</span>
               Thêm câu hỏi mới</a>
         </div>
      </div>


      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
         <form action="" method="GET">
         <div class="bg-white p-4 rounded-xl flex flex-col gap-1">
            <span class="text-[10px] font-bold text-secondary uppercase tracking-wider">Môn học</span>
            <select onchange="this.form.submit()" name ="sub-sel-ques" id = "sub-sel-ques"  class="border-none bg-transparent p-0 font-semibold text-on-surface focus:ring-0">
               <option value = "">Tất cả môn học</option>
               @foreach ($subjects as $sj)
               <option value = "{{ $sj ->code }}"{{ request()->input('sub-sel-ques') == $sj->code ? ' selected' : '' }}>{{ $sj->name }}</option>
               
               @endforeach
            </select>
         </div>
         <div class="bg-white p-4 rounded-xl flex flex-col gap-1">
            <span class="text-[10px] font-bold text-secondary uppercase tracking-wider">Mức độ</span>
            <select name="diff-sel-ques" id="diff-sel-ques" class="border-none bg-transparent p-0 font-semibold text-on-surface focus:ring-0">
               <option value = "">Tất cả mức độ</option>
               <option value="easy">Dễ</option>
               <option value="medium">Trung bình</option>
               <option value="hard">Khó</option>
            </select>
         </div>
         <div class="bg-white p-4 rounded-xl flex flex-col gap-1">
            <span class="text-[10px] font-bold text-secondary uppercase tracking-wider">Chương</span>
            <select name="chap-sel-ques" id ="chap-sel-ques" class="border-none bg-transparent p-0 font-semibold text-on-surface focus:ring-0">
               <option value = "">Tất cả chương</option>
               @foreach ($chapters as $chap)
               <option value = "{{ $chap ->id }}" >{{ $chap->name }}</option>
               @endforeach
            </select>
         </div>
         <div class="bg-white p-4 rounded-xl flex flex-col gap-1">
            <span class="text-[10px] font-bold text-secondary uppercase tracking-wider">Trạng thái</span>
            <select class="border-none bg-transparent p-0 font-semibold text-on-surface focus:ring-0">
               <option>Đã duyệt</option>
               <option>Chờ duyệt</option>
               <option>Bản nháp</option>
            </select>
         </div>
            </form>
      </div>
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
                  <!-- Row 1 -->
                  <tr class="hover:bg-surface-container-low/30 transition-colors">
                     <td class="px-6 py-4 max-w-md">
                        <p class="text-sm font-medium text-on-surface line-clamp-1">Cho hàm số f(x) = ax^3 + bx^2 + cx +
                           d có đồ thị như hình vẽ bên...</p>
                        <p class="text-[10px] text-secondary mt-1">ID: Q-8821 • Cập nhật: 2 giờ trước</p>
                     </td>
                     <td class="px-6 py-4">
                        <span class="text-sm font-semibold text-primary">Toán học 12</span>
                     </td>
                     <td class="px-6 py-4">
                        <span class="text-sm text-secondary">Hàm số</span>
                     </td>
                     <td class="px-6 py-4">
                        <span
                           class="px-3 bg-red-100 py-1 bg-error-container text-on-error-container text-[10px] font-bold rounded-full uppercase">Khó</span>
                     </td>
                     <td class="px-6 py-4 text-right">
                        <div class="flex justify-end gap-2">
                           <button
                              class="p-2 text-secondary hover:text-primary hover:bg-primary/10 rounded-lg transition-all">
                              <span class="material-symbols-outlined text-sm" data-icon="edit">edit</span>
                           </button>
                           <button
                              class="p-2 text-secondary hover:text-error hover:bg-error-container/20 rounded-lg transition-all">
                              <span class="material-symbols-outlined text-sm" data-icon="delete">delete</span>
                           </button>
                        </div>
                     </td>
                  </tr>
                  <!-- Row 2 -->
                  <tr class="hover:bg-surface-container-low/30 transition-colors">
                     <td class="px-6 py-4 max-w-md">
                        <p class="text-sm font-medium text-on-surface line-clamp-1">Định luật bảo toàn cơ năng áp dụng
                           trong trường hợp nào sau đây?</p>
                        <p class="text-[10px] text-secondary mt-1">ID: Q-7712 • Cập nhật: 5 giờ trước</p>
                     </td>
                     <td class="px-6 py-4">
                        <span class="text-sm font-semibold text-primary">Vật lý 10</span>
                     </td>
                     <td class="px-6 py-4">
                        <span class="text-sm text-secondary">Cơ học</span>
                     </td>
                     <td class="px-6 py-4">
                        <span
                           class="px-3 py-1 bg-green-100 text-green-700 text-[10px] font-bold rounded-full uppercase">Dễ</span>
                     </td>
                     <td class="px-6 py-4 text-right">
                        <div class="flex justify-end gap-2">
                           <button
                              class="p-2 text-secondary hover:text-primary hover:bg-primary/10 rounded-lg transition-all">
                              <span class="material-symbols-outlined text-sm" data-icon="edit">edit</span>
                           </button>
                           <button
                              class="p-2 text-secondary hover:text-error hover:bg-error-container/20 rounded-lg transition-all">
                              <span class="material-symbols-outlined text-sm" data-icon="delete">delete</span>
                           </button>
                        </div>
                     </td>
                  </tr>
                  <!-- Row 3 -->
                  <tr class="hover:bg-surface-container-low/30 transition-colors">
                     <td class="px-6 py-4 max-w-md">
                        <p class="text-sm font-medium text-on-surface line-clamp-1">Tính pH của dung dịch chứa hỗn hợp
                           HCl 0.1M và H2SO4 0.05M.</p>
                        <p class="text-[10px] text-secondary mt-1">ID: Q-6543 • Cập nhật: 1 ngày trước</p>
                     </td>
                     <td class="px-6 py-4">
                        <span class="text-sm font-semibold text-primary">Hóa học 11</span>
                     </td>
                     <td class="px-6 py-4">
                        <span class="text-sm text-secondary">Sự điện li</span>
                     </td>
                     <td class="px-6 py-4">
                        <span
                           class="px-3 py-1 bg-blue-100 text-blue-700 text-[10px] font-bold rounded-full uppercase">Trung
                           bình</span>
                     </td>
                     <td class="px-6 py-4 text-right">
                        <div class="flex justify-end gap-2">
                           <button
                              class="p-2 text-secondary hover:text-primary hover:bg-primary/10 rounded-lg transition-all">
                              <span class="material-symbols-outlined text-sm" data-icon="edit">edit</span>
                           </button>
                           <button
                              class="p-2 text-secondary hover:text-error hover:bg-error-container/20 rounded-lg transition-all">
                              <span class="material-symbols-outlined text-sm" data-icon="delete">delete</span>
                           </button>
                        </div>
                     </td>
                  </tr>
                  <!-- Row 4 -->
                  <tr class="hover:bg-surface-container-low/30 transition-colors">
                     <td class="px-6 py-4 max-w-md">
                        <p class="text-sm font-medium text-on-surface line-clamp-1">Tìm giá trị nhỏ nhất của biểu thức P
                           = x^2 + y^2 - 2x - 4y + 5.</p>
                        <p class="text-[10px] text-secondary mt-1">ID: Q-5432 • Cập nhật: 2 ngày trước</p>
                     </td>
                     <td class="px-6 py-4">
                        <span class="text-sm font-semibold text-primary">Toán học 10</span>
                     </td>
                     <td class="px-6 py-4">
                        <span class="text-sm text-secondary">Đại số</span>
                     </td>
                     <td class="px-6 py-4">
                        <span
                           class="px-3 py-1 bg-blue-100 text-blue-700 text-[10px] font-bold rounded-full uppercase">Trung
                           bình</span>
                     </td>
                     <td class="px-6 py-4 text-right">
                        <div class="flex justify-end gap-2">
                           <button
                              class="p-2 text-secondary hover:text-primary hover:bg-primary/10 rounded-lg transition-all">
                              <span class="material-symbols-outlined text-sm" data-icon="edit">edit</span>
                           </button>
                           <button
                              class="p-2 text-secondary hover:text-error hover:bg-error-container/20 rounded-lg transition-all">
                              <span class="material-symbols-outlined text-sm" data-icon="delete">delete</span>
                           </button>
                        </div>
                     </td>
                  </tr>
               </tbody>
            </table>
         </div>
         <!-- Pagination Component -->
         <div
            class="px-6 py-5 bg-surface-container-lowest flex items-center justify-between border-t border-surface-container-low">
            <p class="text-sm text-secondary">
               Hiển thị <span class="font-bold text-on-surface">1 - 10</span> trong số <span
                  class="font-bold text-on-surface">1,240</span> câu hỏi
            </p>
            <div class="flex items-center gap-2">
               <button
                  class="w-10 h-10 flex items-center justify-center rounded-xl border border-surface-container-high text-secondary hover:bg-surface-container-low transition-all">
                  <span class="material-symbols-outlined" data-icon="chevron_left">chevron_left</span>
               </button>
               <button
                  class="w-10 h-10 flex items-center justify-center rounded-xl bg-primary text-white font-bold shadow-md shadow-primary/20">1</button>
               <button
                  class="w-10 h-10 flex items-center justify-center rounded-xl text-secondary hover:bg-surface-container-low transition-all">2</button>
               <button
                  class="w-10 h-10 flex items-center justify-center rounded-xl text-secondary hover:bg-surface-container-low transition-all">3</button>
               <span class="text-secondary px-1">...</span>
               <button
                  class="w-10 h-10 flex items-center justify-center rounded-xl text-secondary hover:bg-surface-container-low transition-all">124</button>
               <button
                  class="w-10 h-10 flex items-center justify-center rounded-xl border border-surface-container-high text-secondary hover:bg-surface-container-low transition-all">
                  <span class="material-symbols-outlined" data-icon="chevron_right">chevron_right</span>
               </button>
            </div>
         </div>
      </div>
   </div>
</x-app-layout>