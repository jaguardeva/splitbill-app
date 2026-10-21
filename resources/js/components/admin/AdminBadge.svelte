<script lang="ts">
import type { Snippet } from 'svelte';

let {
    variant = 'neutral',
    size = 'md',
    class: customClass = '',
    children,
    ...restProps
}: {
    variant?: 'blue' | 'yellow' | 'green' | 'red' | 'neutral';
    size?: 'sm' | 'md';
    class?: string;
    children?: Snippet;
    [key: string]: any;
} = $props();

const variantClasses = {
    blue: 'bg-blue-50 text-blue-700 border-blue-200/80',
    yellow: 'bg-amber-50 text-amber-800 border-amber-200/90',
    green: 'bg-emerald-50 text-emerald-700 border-emerald-200/80',
    red: 'bg-rose-50 text-rose-700 border-rose-200/80',
    neutral: 'bg-slate-100 text-slate-700 border-slate-200',
};

const sizeClasses = {
    sm: 'text-[11px] px-2 py-0.5 font-medium',
    md: 'text-xs px-2.5 py-1 font-semibold',
};
</script>

<span
    class="inline-flex items-center gap-1 rounded-md border {variantClasses[variant]} {sizeClasses[size]} {customClass}"
    {...restProps}
>
    {@render children?.()}
</span>
