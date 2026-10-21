<script lang="ts">
import type { Snippet } from 'svelte';

let {
    class: customClass = '',
    title,
    description,
    actions,
    children,
    footer,
    noPadding = false,
    overflow = noPadding ? 'hidden' : 'visible',
    ...restProps
}: {
    class?: string;
    title?: string;
    description?: string;
    actions?: Snippet;
    children?: Snippet;
    footer?: Snippet;
    noPadding?: boolean;
    overflow?: 'visible' | 'hidden';
    [key: string]: any;
} = $props();
</script>

<div
    class="rounded-lg border border-slate-200/90 bg-white shadow-xs {overflow === 'hidden' ? 'overflow-hidden' : 'overflow-visible'} {customClass}"
    {...restProps}
>
    {#if title || actions}
        <div class="flex flex-col gap-2.5 sm:flex-row sm:items-center sm:justify-between border-b border-slate-100 px-4 sm:px-5 py-3.5 sm:py-4">
            <div class="min-w-0 flex-1">
                {#if title}
                    <h3 class="text-sm sm:text-base font-semibold text-slate-900 leading-snug">{title}</h3>
                {/if}
                {#if description}
                    <p class="mt-0.5 text-xs text-slate-500 leading-normal">{description}</p>
                {/if}
            </div>
            {#if actions}
                <div class="flex items-center gap-2 shrink-0">
                    {@render actions()}
                </div>
            {/if}
        </div>
    {/if}

    <div class={noPadding ? '' : 'p-5'}>
        {@render children?.()}
    </div>

    {#if footer}
        <div class="border-t border-slate-100 bg-slate-50/50 px-5 py-3">
            {@render footer()}
        </div>
    {/if}
</div>
