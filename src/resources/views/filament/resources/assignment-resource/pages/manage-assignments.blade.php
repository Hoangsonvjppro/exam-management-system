<x-filament-panels::page>

    {{-- ─────────────────────────────────────────────
         Block 1: Lecturer Select
    ───────────────────────────────────────────── --}}
    <x-filament::section>
        {{ $this->lecturerForm }}
    </x-filament::section>

    {{-- ─────────────────────────────────────────────
         Block 2: Subject Table (chỉ hiện khi đã chọn GV)
    ───────────────────────────────────────────── --}}
    @if ($selectedLecturerId)
        {{-- Overlay Spinner --}}
        <div wire:loading.delay.shorter wire:target="selectedLecturerId"
            class="absolute inset-0 z-50 flex items-center justify-center
                   bg-white/60 dark:bg-gray-900/60 backdrop-blur-sm">
            <div class="flex flex-col items-center gap-3">
                {{-- Spinner --}}
                <x-filament::loading-indicator class="h-12 w-12" />
                {{-- Text optional --}}
                <span class="text-sm text-gray-600 dark:text-gray-300">
                    Đang tải dữ liệu...
                </span>
            </div>
        </div>
        <x-filament::section wire:target="selectedLecturerId" wire:loading.class="opacity-50 pointer-events-none"
            class="transition duration-200">
            {{ $this->table }}
        </x-filament::section>


        {{-- ─────────────────────────────────────────────
                 Block 3: Action Buttons (bottom)
            ───────────────────────────────────────────── --}}
        <div class="flex items-center gap-3 justify-end pt-2">

            <x-filament::button wire:click="save" wire:loading.attr="disabled" wire:target="save"
                icon="heroicon-m-check-circle" color="primary" size="lg">
                <span wire:loading.remove wire:target="save">Lưu phân công</span>
                <span wire:loading wire:target="save">Đang lưu...</span>
            </x-filament::button>

            <x-filament::button wire:click="cancel" wire:loading.attr="disabled" wire:target="save"
                icon="heroicon-m-arrow-uturn-left" color="gray" size="lg">
                Hủy thay đổi
            </x-filament::button>

        </div>
    @else
        {{-- Placeholder khi chưa chọn GV --}}
        <x-filament::section>
            <div class="flex flex-col items-center justify-center py-12 text-center text-gray-400 dark:text-gray-500">
                <x-filament::icon icon="heroicon-o-user-group" class="h-12 w-12 mb-3 opacity-40" />
                <p class="text-sm font-medium">Vui lòng chọn giảng viên để bắt đầu phân công.</p>
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>
