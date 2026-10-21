<script lang="ts">
import type { Snippet } from 'svelte';
import { Link } from '@inertiajs/svelte';

let {
    variant = 'primary',
    size = 'md',
    href,
    type = 'button',
    disabled = false,
    class: customClass = '',
    children,
    onclick,
    ...restProps
}: {
    variant?: 'primary' | 'warning' | 'secondary' | 'danger' | 'ghost';
    size?: 'sm' | 'md' | 'lg';
    href?: string;
    type?: 'button' | 'submit' | 'reset';
    disabled?: boolean;
    class?: string;
    children?: Snippet;
    onclick?: (e: MouseEvent) => void;
    [key: string]: any;
} = $props();

const baseClasses =
    'inline-flex items-center justify-center font-medium rounded-lg transition-colors focus:outline-hidden focus:ring-2 focus:ring-blue-500/20 disabled:opacity-50 disabled:pointer-events-none cursor-pointer';

const sizeClasses = {
    sm: 'text-xs px-2.5 py-1.5 gap-1.5',
    md: 'text-sm px-3.5 py-2 gap-2',
    lg: 'text-base px-4 py-2.5 gap-2.5',
};

const variantClasses = {
    primary:
        'bg-blue-600 text-white hover:bg-blue-700 active:bg-blue-800 shadow-xs border border-blue-600',
    warning:
        'bg-amber-400 text-slate-900 hover:bg-amber-500 active:bg-amber-600 shadow-xs border border-amber-400 font-semibold',
    secondary:
        'bg-white text-slate-700 hover:bg-slate-50 active:bg-slate-100 border border-slate-300 shadow-xs',
    danger:
        'bg-red-600 text-white hover:bg-red-700 active:bg-red-800 shadow-xs border border-red-600',
    ghost:
        'bg-transparent text-slate-600 hover:text-slate-900 hover:bg-slate-100 active:bg-slate-200',
};
</script>

{#if href}
    <Link
        {href}
        class="{baseClasses} {sizeClasses[size]} {variantClasses[variant]} {customClass}"
        {...restProps}
    >
        {@render children?.()}
    </Link>
{:else}
    <button
        {type}
        {disabled}
        {onclick}
        class="{baseClasses} {sizeClasses[size]} {variantClasses[variant]} {customClass}"
        {...restProps}
    >
        {@render children?.()}
    </button>
{/if}
