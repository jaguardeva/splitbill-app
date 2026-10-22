<script lang="ts">
import { CircleAlert, CircleCheck, Info, TriangleAlert } from '@lucide/svelte';
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
        <CircleAlert class="mt-0.5 size-5 shrink-0" strokeWidth={2.5} aria-hidden="true" />
    {:else if variant === 'success'}
        <CircleCheck class="mt-0.5 size-5 shrink-0" strokeWidth={2.5} aria-hidden="true" />
    {:else if variant === 'warning'}
        <TriangleAlert class="mt-0.5 size-5 shrink-0" strokeWidth={2.5} aria-hidden="true" />
    {:else}
        <Info class="mt-0.5 size-5 shrink-0 text-blue-800" strokeWidth={2.5} aria-hidden="true" />
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
