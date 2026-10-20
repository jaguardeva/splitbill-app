<script lang="ts">
import type { Snippet } from 'svelte';

let {
    title,
    isOpen = false,
    ontoggle,
    class: customClass = '',
    children,
    ...restProps
}: {
    title: string;
    isOpen?: boolean;
    ontoggle?: () => void;
    class?: string;
    children?: Snippet;
    [key: string]: any;
} = $props();
</script>

<div
    class="rounded-xl border-2 border-black bg-white shadow-[3px_3px_0px_0px_#000] transition-all {customClass}"
    {...restProps}
>
    <button
        type="button"
        onclick={ontoggle}
        aria-expanded={isOpen}
        class="w-full flex items-center justify-between p-4 text-left font-black text-black cursor-pointer select-none transition hover:bg-yellow-100/70"
    >
        <span class="text-sm sm:text-base pr-3">{title}</span>
        <span
            class="flex size-7 shrink-0 items-center justify-center rounded border-2 border-black bg-yellow-400 text-black shadow-[1px_1px_0px_0px_#000] transition-transform duration-200 {isOpen ? 'rotate-180 bg-blue-600 text-white' : ''}"
        >
            <svg class="size-4 stroke-[3]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
        </span>
    </button>

    {#if isOpen}
        <div class="border-t-2 border-black bg-yellow-50/50 p-4 text-xs sm:text-sm font-bold text-neutral-800 leading-relaxed">
            {@render children?.()}
        </div>
    {/if}
</div>
