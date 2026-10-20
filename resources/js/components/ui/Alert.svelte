<script lang="ts">
import type { Snippet } from 'svelte';

let {
    variant = 'info',
    title,
    message,
    class: customClass = '',
    children,
    ...restProps
}: {
    variant?: 'error' | 'success' | 'warning' | 'info';
    title?: string;
    message?: string;
    class?: string;
    children?: Snippet;
    [key: string]: any;
} = $props();

const variantClasses = {
    error: 'bg-red-400 text-black border-2 border-black shadow-[4px_4px_0px_0px_#000]',
    success: 'bg-emerald-400 text-black border-2 border-black shadow-[4px_4px_0px_0px_#000]',
    warning: 'bg-yellow-300 text-black border-2 border-black shadow-[4px_4px_0px_0px_#000]',
    info: 'bg-blue-200 text-black border-2 border-black shadow-[4px_4px_0px_0px_#000]',
};
</script>

<div
    class="flex items-start gap-3 rounded-xl p-4 text-sm font-bold {variantClasses[variant]} {customClass}"
    role="alert"
    {...restProps}
>
    {#if variant === 'error'}
        <svg class="size-5 shrink-0 stroke-[2.5] mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
    {:else if variant === 'success'}
        <svg class="size-5 shrink-0 stroke-[2.5] mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
        </svg>
    {:else if variant === 'warning'}
        <svg class="size-5 shrink-0 stroke-[2.5] mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
    {:else}
        <svg class="size-5 shrink-0 stroke-[2.5] mt-0.5 text-blue-800" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
    {/if}

    <div class="flex-1">
        {#if title}
            <h4 class="font-black text-sm uppercase tracking-tight mb-0.5">{title}</h4>
        {/if}
        {#if message}
            <p class="leading-relaxed">{message}</p>
        {/if}
        {@render children?.()}
    </div>
</div>
