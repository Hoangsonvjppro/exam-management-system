<x-app-layout>
    @section('title', ($section->name ?? $section->code) . ' — EMS')
    @section('page-title', 'Chi tiết lớp học phần')

    <div class="space-y-6">

        <div class="flex items-center justify-between">
            <a href="{{ route('lecturer.classes.index') }}"
               class="inline-flex items-center gap-1 text-sm font-bold text-slate-500 hover:text-ems-primary">
                ← Danh sách lớp
            </a>
            <div class="flex items-center gap-3">
                <button onclick="document.getElementById('create-notification-modal').classList.remove('hidden')"
                   class="h-9 px-4 flex items-center bg-ems-primary text-white brutal-border font-black text-xs uppercase brutal-btn brutal-shadow">
                    Tạo thông báo
                </button>
                <a href="{{ route('lecturer.classes.edit', $section) }}"
                   class="h-9 px-4 flex items-center bg-white brutal-border font-black text-xs uppercase brutal-btn brutal-shadow">
                    Chỉnh sửa
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 bg-green-100 brutal-border font-semibold text-green-800 text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="p-4 bg-red-100 brutal-border font-semibold text-red-800 text-sm">{{ session('error') }}</div>
        @endif

        {{-- Class Info Card --}}
        <div class="bg-white brutal-border brutal-shadow p-6">
            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                <div>
                    <p class="text-xs font-black uppercase tracking-widest text-slate-400">{{ $section->code }}</p>
                    <h2 class="text-3xl font-black mt-1">{{ $section->name ?? $section->code }}</h2>
                    <span class="inline-block mt-2 uppercase text-xs font-black px-2 py-1 brutal-border
                        @if($section->status === 'active') bg-green-200 text-green-800
                        @elseif($section->status === 'archived') bg-slate-200 text-slate-600
                        @else bg-red-200 text-red-700 @endif">
                        {{ match($section->status) {
                            'active'   => 'Đang mở',
                            'archived' => 'Đã lưu trữ',
                            default    => 'Đã huỷ',
                        } }}
                    </span>
                </div>

                {{-- Invite Code Box --}}
                <div class="brutal-border bg-ems-primary/5 p-5 text-center min-w-[200px]">
                    <p class="text-xs font-black uppercase tracking-widest text-slate-500 mb-2">Mã tham gia lớp</p>
                    <p class="text-4xl font-black tracking-[0.3em] uppercase font-mono">
                        {{ $section->invite_code ?? '——' }}
                    </p>
                    <p class="text-xs text-slate-400 font-semibold mt-2">Chia sẻ mã này cho sinh viên</p>

                    <form method="POST" action="{{ route('lecturer.classes.regenerate-code', $section) }}" class="mt-3">
                        @csrf
                        <button type="submit"
                                onclick="return confirm('Tạo mã mới? Mã cũ sẽ không còn hoạt động.')"
                                class="text-xs font-black uppercase text-ems-primary hover:underline">
                            Tạo mã mới
                        </button>
                    </form>
                </div>
            </div>

            <div class="mt-6 grid grid-cols-2 md:grid-cols-3 gap-4 border-t-4 border-black pt-6">
                <div>
                    <p class="text-xs font-black uppercase text-slate-400">Sĩ số tối đa</p>
                    <p class="text-xl font-black mt-1">{{ $section->max_students }}</p>
                </div>
                <div>
                    <p class="text-xs font-black uppercase text-slate-400">Đang theo học</p>
                    <p class="text-xl font-black mt-1">{{ $section->students->count() }}</p>
                </div>
                <div>
                    <p class="text-xs font-black uppercase text-slate-400">Tạo ngày</p>
                    <p class="text-xl font-black mt-1">{{ $section->created_at->format('d/m/Y') }}</p>
                </div>
            </div>
        </div>

        {{-- Student List --}}
        <div class="bg-white brutal-border brutal-shadow p-6">
            <h3 class="text-xl font-black uppercase mb-4">Danh sách sinh viên ({{ $section->students->count() }})</h3>

            @if($section->students->isEmpty())
                <div class="text-center py-10">
                    <p class="text-slate-400 font-semibold">Chưa có sinh viên nào tham gia lớp này.</p>
                    <p class="text-slate-400 text-sm mt-1">Chia sẻ mã tham gia cho sinh viên của bạn.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b-4 border-black">
                                <th class="text-left py-2 px-3 font-black uppercase text-xs tracking-wide">Họ tên</th>
                                <th class="text-left py-2 px-3 font-black uppercase text-xs tracking-wide">Email</th>
                                <th class="text-left py-2 px-3 font-black uppercase text-xs tracking-wide">MSSV</th>
                                <th class="text-left py-2 px-3 font-black uppercase text-xs tracking-wide">Ngày tham gia</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($section->students->sortBy('name') as $student)
                                <tr class="border-b-2 border-dashed border-slate-200 hover:bg-slate-50">
                                    <td class="py-3 px-3 font-semibold">{{ $student->name }}</td>
                                    <td class="py-3 px-3 text-slate-600">{{ $student->email }}</td>
                                    <td class="py-3 px-3 font-mono font-bold">{{ $student->student_code ?? '—' }}</td>
                                    <td class="py-3 px-3 text-slate-500">{{ $student->pivot->enrolled_at ? \Carbon\Carbon::parse($student->pivot->enrolled_at)->format('d/m/Y') : '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Danger zone --}}
        @if($section->students->isEmpty())
        <div class="bg-white brutal-border border-red-400 p-6">
            <h3 class="text-lg font-black text-red-600 uppercase mb-2">Xoá lớp học phần</h3>
            <p class="text-sm text-slate-600 font-semibold mb-4">Hành động này không thể hoàn tác. Lớp sẽ bị xoá vĩnh viễn.</p>
            <form method="POST" action="{{ route('lecturer.classes.destroy', $section) }}">
                @csrf
                @method('DELETE')
                <button type="submit"
                        onclick="return confirm('Bạn có chắc muốn xoá lớp này không?')"
                        class="h-10 px-6 bg-red-500 text-white brutal-border font-black text-sm uppercase brutal-btn">
                    Xoá lớp
                </button>
            </form>
        </div>
        @endif

    </div>

    {{-- Notification Modal --}}
    <div id="create-notification-modal" class="hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
        <div class="bg-white brutal-border brutal-shadow-lg w-full max-w-lg p-8">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-2xl font-black uppercase">Tạo thông báo</h3>
                <button onclick="document.getElementById('create-notification-modal').classList.add('hidden')" class="font-black text-2xl leading-none hover:text-ems-primary">&times;</button>
            </div>

            <form method="POST" action="{{ route('lecturer.classes.notifications.store', $section) }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-black uppercase mb-2">Tiêu đề</label>
                    <input name="title" type="text" required placeholder="Nhập tiêu đề thông báo" class="w-full h-12 px-4 bg-white brutal-border font-bold focus:ring-0">
                </div>
                <div>
                    <label class="block text-sm font-black uppercase mb-2">Nội dung</label>
                    <textarea name="message" required rows="4" placeholder="Nội dung thông báo..." class="w-full p-4 bg-white brutal-border font-semibold focus:ring-0"></textarea>
                </div>
                <button type="submit" class="w-full h-12 bg-ems-primary text-white brutal-border font-black uppercase tracking-widest hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-none transition-all shadow-brutal">
                    Gửi thông báo
                </button>
            </form>
        </div>
    </div>
</x-app-layout>

