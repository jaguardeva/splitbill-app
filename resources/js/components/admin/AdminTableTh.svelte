<script lang="ts">
import type { Snippet } from 'svelte';
import { ArrowDownUp, ChevronDown, ChevronUp } from '@lucide/svelte';

let {
    label,
    field,
    sortable = false,
    currentSort = '',
    currentDirection = 'desc',
    align = 'left',
    class: customClass = '',
    onsort,
    children,
}: {
    label?: string;
    field?: string;
    sortable?: boolean;
    currentSort?: string;
    currentDirection?: 'asc' | 'desc';
    align?: 'left' | 'center' | 'right';
    class?: string;
    onsort?: (field: string) => void;
    children?: Snippet;
} = $props();

const isActive = $derived(sortable && field && currentSort === field);

const alignClasses = {
    left: 'text-left justify-start',
    center: 'text-center justify-center',
    right: 'text-right justify-end',
};

function handleClick() {
    if (sortable && field && onsort) {
        onsort(field);
    }
}
</script>

<th
    scope="col"
    class="px-4 py-3 text-[11px] font-semibold tracking-wider uppercase text-slate-500 {customClass}"
>
    {#if sortable && field}
        <button
            type="button"
            onclick={handleClick}
            title={label || 'Urutkan kolom'}
            class="group inline-flex items-center gap-1.5 transition-colors hover:text-slate-900 cursor-pointer w-full {alignClasses[align]}"
        >
            <span class="line-clamp-2 leading-tight text-left {isActive ? 'font-bold text-blue-700' : ''}">
                {#if children}
                    {@render children()}
                {:else}
                    {label}
                {/if}
            </span>

            <span class="inline-flex shrink-0 items-center text-slate-400">
                {#if isActive}
                    {#if currentDirection === 'asc'}
                        <ChevronUp class="size-3.5 text-blue-600" strokeWidth={2.5} aria-hidden="true" />
                    {:else}
                        <ChevronDown class="size-3.5 text-blue-600" strokeWidth={2.5} aria-hidden="true" />
                    {/if}
                {:else}
                    <ArrowDownUp class="size-3 text-slate-300 opacity-60 transition-opacity group-hover:opacity-100" strokeWidth={2} aria-hidden="true" />
                {/if}
            </span>
        </button>
    {:else}
        <div class="flex items-center {alignClasses[align]}">
            <span class="line-clamp-2 leading-tight" title={label}>
                {#if children}
                    {@render children()}
                {:else}
                    {label}
                {/if}
            </span>
        </div>
    {/if}
</th>
