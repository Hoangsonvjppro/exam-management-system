@props([
    'striped' => false,
    'hoverable' => true,
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-card shadow-card border border-border/60 overflow-hidden']) }}>
    @isset($header)
        <div class="px-6 py-4 border-b border-border flex items-center justify-between">
            {{ $header }}
        </div>
    @endisset

    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            @isset($head)
                <thead class="bg-surface-muted border-b border-border">
                    <tr>
                        {{ $head }}
                    </tr>
                </thead>
            @endisset
            <tbody class="divide-y divide-border/60">
                {{ $slot }}
            </tbody>
        </table>
    </div>

    @isset($footer)
        <div class="px-6 py-4 border-t border-border bg-surface-muted">
            {{ $footer }}
        </div>
    @endisset
</div>
