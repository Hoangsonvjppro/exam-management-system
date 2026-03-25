<div
    x-data="{
        show: false,
        message: '',
        type: 'success',
        init() {
            @if(session('status'))
                this.showToast('{{ session('status') }}', 'success');
            @endif
            @if(session('success'))
                this.showToast('{{ session('success') }}', 'success');
            @endif
            @if(session('error'))
                this.showToast('{{ session('error') }}', 'danger');
            @endif
            @if(session('info'))
                this.showToast('{{ session('info') }}', 'info');
            @endif
            @if(session('warning'))
                this.showToast('{{ session('warning') }}', 'warning');
            @endif

            window.addEventListener('toast', (event) => {
                this.showToast(event.detail.message, event.detail.type || 'success');
            });
        },
        showToast(message, type) {
            this.message = message;
            this.type = type === 'error' ? 'danger' : type;
            this.show = true;
            setTimeout(() => {
                this.show = false;
            }, 5000);
        }
    }"
    x-show="show"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
    x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed top-6 right-6 z-[9999] max-w-sm w-full bg-white/95 backdrop-blur-sm border-0.5 border-border-clean rounded-[12px] shadow-[0_10px_40px_rgba(26,58,107,0.15)] overflow-hidden pointer-events-auto"
    style="display: none;"
>
    <div class="p-4 flex items-start gap-3">
        <!-- Icon -->
        <div class="shrink-0 w-8 h-8 rounded-full flex items-center justify-center"
             :class="{
                'bg-success-50 text-success-600': type === 'success',
                'bg-danger-50 text-danger-600': type === 'danger' || type === 'error',
                'bg-navy-50 text-navy-600': type === 'info',
                'bg-warning-50 text-warning-600': type === 'warning'
             }">
            <template x-if="type === 'success'">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
            </template>
            <template x-if="type === 'danger' || type === 'error'">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
            </template>
            <template x-if="type === 'info'">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </template>
            <template x-if="type === 'warning'">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
            </template>
        </div>

        <!-- Content -->
        <div class="flex-1 min-w-0 pt-0.5">
            <p class="text-[13px] font-bold text-navy-900 leading-tight mb-1 uppercase tracking-tight" 
               x-text="type === 'danger' ? 'Lỗi' : (type === 'success' ? 'Thành công' : (type === 'info' ? 'Thông báo' : 'Cảnh báo'))"></p>
            <p class="text-[12px] text-text-muted font-medium leading-relaxed" x-text="message"></p>
        </div>

        <!-- Close -->
        <button @click="show = false" class="shrink-0 p-1 rounded-full hover:bg-surface-1 text-text-muted transition-colors mt-0.5">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>
    </div>
    
    <!-- Progress bar -->
    <div class="h-1 bg-surface-1 w-full">
        <div class="h-full transition-all duration-[5000ms] ease-linear"
             :class="{
                'bg-success-500': type === 'success',
                'bg-danger-500': type === 'danger' || type === 'error',
                'bg-navy-600': type === 'info',
                'bg-warning-500': type === 'warning'
             }"
             :style="show ? 'width: 0%;' : 'width: 100%;'"
             x-init="$watch('show', value => { if(value) { $el.style.width = '100%'; setTimeout(() => $el.style.width = '0%', 10) } })"
        ></div>
    </div>
</div>

