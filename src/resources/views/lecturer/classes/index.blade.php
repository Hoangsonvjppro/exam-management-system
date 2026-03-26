<x-app-layout>
    @section('title', 'Lớp học phần của tôi — EMS')
    @section('page-title', 'Lớp học phần')

    <div class="space-y-6" x-data="courseSectionManager()">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-navy-900 leading-tight">Lớp học phần của tôi</h2>
                <p class="text-sm font-medium text-text-muted mt-1">Quản lý và tạo các lớp học phần bạn phụ trách.</p>
            </div>
            <x-button variant="primary" @click="$dispatch('open-slide-over', 'create-section-slide')" class="flex items-center gap-2 text-sm">
                <x-ui-icon name="plus" class="w-4 h-4" />
                Tạo lớp mới
            </x-button>
        </div>

        {{-- Toolbar: Search & Action --}}
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white p-4 rounded-xl border border-border-clean/50 shadow-sm">
            <x-search-input x-model="searchQuery" placeholder="Tìm kiếm lớp học phần..." class="!max-w-md" />
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-text-muted uppercase tracking-wider">Trạng thái:</span>
                    <select class="text-sm border-none bg-transparent font-bold text-navy-900 focus:ring-0 cursor-pointer">
                        <option value="all">Tất cả</option>
                        <option value="active">Đang mở</option>
                        <option value="archived">Lưu trữ</option>
                    </select>
                </div>
            </div>
        </div>


        {{-- Class Grid --}}
        @if($sections->isEmpty())
            <div class="text-center py-20 bg-surface-0 border-[0.5px] border-border-clean border-dashed rounded-xl">
                <x-ui-icon name="rectangle-group" class="mx-auto w-12 h-12 text-blue-100 mb-4" />
                <p class="font-bold text-navy-900 text-lg">Chưa có lớp học phần nào</p>
                <p class="text-sm text-text-muted mt-2 mb-6">Tạo lớp học phần đầu tiên và chia sẻ mã mời cho sinh viên.</p>
                <x-button variant="primary" @click="$dispatch('open-slide-over', 'create-section-slide')">
                    Tạo lớp ngay
                </x-button>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6" id="sections-grid">
                @foreach($sections as $section)
                    @include('lecturer.classes.partials._section_card', ['section' => $section])
                @endforeach
            </div>

            <div class="mt-6">{{ $sections->links() }}</div>
        @endif

        {{-- Slide-over: Tạo lớp mới --}}
        <x-slide-over name="create-section-slide" title="Tạo Lớp Học Phần Mới">
            <form @submit.prevent="submitCreateForm($el)" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-[12px] font-medium text-navy-900 mb-1.5" for="slide-name">
                        Tên lớp học phần <span class="text-red-500">*</span>
                    </label>
                    <x-text-input id="slide-name" name="name" type="text" required placeholder="VD: Lập trình Java — Nhóm 1" />
                    <p class="mt-1.5 text-[11px] font-medium text-red-600 hidden" data-error="name"></p>
                </div>

                <div>
                    <label class="block text-[12px] font-medium text-navy-900 mb-1.5" for="slide-subject">
                        Môn học <span class="text-red-500">*</span>
                    </label>
                    <select id="slide-subject" name="subject_id" required class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">-- Chọn môn học --</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}">{{ $subject->code }} - {{ $subject->name }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1.5 text-[11px] font-medium text-red-600 hidden" data-error="subject_id"></p>
                </div>

                <div>
                    <label class="block text-[12px] font-medium text-navy-900 mb-1.5" for="slide-semester">
                        Học kỳ <span class="text-red-500">*</span>
                    </label>
                    <select id="slide-semester" name="semester_id" required class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">-- Chọn học kỳ --</option>
                        @foreach($semesters as $semester)
                            <option value="{{ $semester->id }}">{{ $semester->name }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1.5 text-[11px] font-medium text-red-600 hidden" data-error="semester_id"></p>
                </div>

                <div>
                    <p class="text-[12px] font-medium text-text-muted">Mã lớp (nội bộ) sẽ được tự sinh theo môn học, nhóm, học kỳ và năm học.</p>
                </div>

                <div>
                    <label class="block text-[12px] font-medium text-navy-900 mb-1.5" for="slide-max-students">
                        Sĩ số tối đa
                    </label>
                    <x-text-input id="slide-max-students" name="max_students" type="number" value="100" min="1" max="500" />
                    <p class="mt-1.5 text-[11px] font-medium text-red-600 hidden" data-error="max_students"></p>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-border-clean">
                    <x-button type="button" variant="ghost" @click="$dispatch('close-slide-over', 'create-section-slide')">Huỷ</x-button>
                    <x-button type="submit" variant="primary" x-bind:disabled="isSubmitting">
                        <span x-show="!isSubmitting">Tạo lớp</span>
                        <span x-show="isSubmitting" class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            Đang tạo...
                        </span>
                    </x-button>
                </div>
            </form>
        </x-slide-over>

    </div>

    <script>
        function courseSectionManager() {
            return {
                searchQuery: '',
                isSubmitting: false,

                clearErrors(formElement) {
                    formElement.querySelectorAll('[data-error]').forEach(el => {
                        el.textContent = '';
                        el.classList.add('hidden');
                    });
                    formElement.querySelectorAll('.border-red-400').forEach(el => {
                        el.classList.remove('border-red-400');
                    });
                },

                showErrors(formElement, errors) {
                    for (const [field, messages] of Object.entries(errors)) {
                        const errorEl = formElement.querySelector(`[data-error="${field}"]`);
                        if (errorEl) {
                            errorEl.textContent = messages[0];
                            errorEl.classList.remove('hidden');
                        }
                        // Highlight input
                        const input = formElement.querySelector(`[name="${field}"]`);
                        if (input) {
                            input.classList.add('border-red-400');
                        }
                    }
                },

                async submitCreateForm(formElement) {
                    this.isSubmitting = true;
                    this.clearErrors(formElement);

                    const formData = new FormData(formElement);

                    try {
                        const response = await fetch("{{ route('lecturer.classes.store') }}", {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            },
                            body: formData,
                        });

                        const result = await response.json();

                        if (response.ok && result.success) {
                            // Chèn card mới vào đầu grid
                            const grid = document.getElementById('sections-grid');
                            if (grid) {
                                grid.insertAdjacentHTML('afterbegin', result.html);
                            } else {
                                // Grid chưa tồn tại (danh sách trống) → reload trang
                                window.location.reload();
                                return;
                            }

                            // Đóng slide-over
                            this.$dispatch('close-slide-over', 'create-section-slide');

                            // Reset form
                            formElement.reset();

                            // Toast thông báo
                            window.dispatchEvent(new CustomEvent('toast', {
                                detail: { message: result.message, type: 'success' }
                            }));
                        } else if (response.status === 422 && result.errors) {
                            // Validation errors
                            this.showErrors(formElement, result.errors);
                        } else {
                            window.dispatchEvent(new CustomEvent('toast', {
                                detail: { message: result.message || 'Có lỗi xảy ra.', type: 'error' }
                            }));
                        }
                    } catch (error) {
                        window.dispatchEvent(new CustomEvent('toast', {
                            detail: { message: 'Có lỗi hệ thống xảy ra!', type: 'error' }
                        }));
                    } finally {
                        this.isSubmitting = false;
                    }
                }
            }
        }
    </script>
</x-app-layout>
