@props([
'striped' => false,
'hoverable' => true,
])

<div {{ $attributes->merge(['class' => 'bg-white border-[0.5px] border-border-clean rounded-[10px] overflow-hidden']) }}>
    @isset($header)
    <div class="px-4 py-3 border-b-[0.5px] border-border-clean bg-surface-0 flex items-center justify-between">
        {{ $header }}
    </div>
    @endisset

    <div class="overflow-x-auto">
        <table class="w-full text-left">
            @isset($head)
            <thead>
                <tr>
                    {{ $head }}
                </tr>
            </thead>
            @endisset
            <tbody>
                {{ $slot }}
            </tbody>
        </table>
    </div>

    @isset($footer)
    <div class="px-4 py-3 border-t-[0.5px] border-border-clean bg-surface-0">
        {{ $footer }}
    </div>
    @endisset
</div>