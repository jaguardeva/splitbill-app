<script lang="ts">
import type { Snippet } from 'svelte';

let {
    title,
    value,
    helper,
    iconColor = 'blue',
    icon,
    class: customClass = '',
}: {
    title: string;
    value: string | number;
    helper?: string;
    iconColor?: 'blue' | 'yellow' | 'emerald';
    icon?: Snippet;
    class?: string;
} = $props();

const iconBgClasses = {
    blue: 'bg-blue-50 text-blue-600 border border-blue-100',
    yellow: 'bg-amber-50 text-amber-600 border border-amber-100',
    emerald: 'bg-emerald-50 text-emerald-600 border border-emerald-100',
};
</script>

<div
    class="rounded-lg border border-slate-200/90 bg-white p-4 sm:p-5 shadow-xs transition-shadow hover:shadow-sm {customClass}"
>
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <p class="truncate text-[11px] sm:text-xs font-semibold uppercase tracking-wider text-slate-500">{title}</p>
            <p class="mt-1.5 sm:mt-2 text-xl sm:text-2xl font-bold tracking-tight text-slate-900 truncate">{value}</p>
            {#if helper}
                <p class="mt-1 text-xs text-slate-500 line-clamp-1">{helper}</p>
            {/if}
        </div>
        {#if icon}
            <div
                class="flex size-9 sm:size-11 shrink-0 items-center justify-center rounded-lg {iconBgClasses[iconColor]}"
            >
                {@render icon()}
            </div>
        {/if}
    </div>
</div>

