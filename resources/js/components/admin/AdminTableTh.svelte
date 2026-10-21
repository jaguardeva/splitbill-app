<script lang="ts">
import type { Snippet } from 'svelte';

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
                        <svg class="size-3.5 text-blue-600 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />
                        </svg>
                    {:else}
                        <svg class="size-3.5 text-blue-600 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    {/if}
                {:else}
                    <svg class="size-3 text-slate-300 opacity-60 group-hover:opacity-100 transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                    </svg>
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
