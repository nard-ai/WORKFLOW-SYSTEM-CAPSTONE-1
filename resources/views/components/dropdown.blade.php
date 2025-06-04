@props(['align' => 'right', 'width' => '48', 'contentClasses' => 'py-1 bg-white'])

@php
$alignmentClasses = match ($align) {
    'left' => 'ltr:origin-top-left rtl:origin-top-right start-0',
    'top' => 'origin-top',
    default => 'ltr:origin-top-right rtl:origin-top-left end-0',
};

$width = match ($width) {
    '48' => 'w-48',
    default => $width,
};
@endphp

<div class="relative" x-data="dropdownComponent()">
    <div @click="toggle" 
         class="cursor-pointer select-none" 
         role="button" 
         tabindex="0" 
         @keydown.enter.prevent="toggle"
         @keydown.space.prevent="toggle">
        {{ $trigger }}
    </div>

    <template x-teleport="body">
        <div x-show="isOpen" 
             x-transition.opacity
             @click.away="close"
             @keydown.escape.window="close"
             class="fixed inset-0 z-40 bg-black bg-opacity-20"
             style="display: none;">
        </div>
    </template>

    <div x-show="isOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         class="absolute z-50 mt-2 {{ $width }} rounded-md shadow-lg {{ $alignmentClasses }}"
         @click.away="close"
         style="display: none;">
        <div class="rounded-md ring-1 ring-black ring-opacity-5 {{ $contentClasses }} bg-white">
            {{ $content }}
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('dropdownComponent', () => ({
        isOpen: false,
        init() {
            this.$watch('isOpen', value => {
                if (value) {
                    document.body.style.overflow = 'hidden';
                } else {
                    document.body.style.overflow = '';
                }
            });
        },
        toggle() {
            this.isOpen = !this.isOpen;
        },
        close() {
            this.isOpen = false;
        }
    }));
});
</script>

{{-- Fallback for when JavaScript is disabled --}}
<noscript>
    <style>
        .dropdown-fallback:hover .dropdown-fallback-content {
            display: block !important;
        }
    </style>
    <div class="relative dropdown-fallback">
        <div class="cursor-pointer">
            {{ $trigger }}
        </div>
        <div class="dropdown-fallback-content hidden absolute z-50 mt-2 {{ $width }} rounded-md shadow-lg {{ $alignmentClasses }}">
            <div class="rounded-md ring-1 ring-black ring-opacity-5 {{ $contentClasses }} bg-white">
                {{ $content }}
            </div>
        </div>
    </div>
</noscript>

