<script lang="ts">
import type { Snippet } from 'svelte';
import { Link } from '@inertiajs/svelte';

let {
    variant = 'blue',
    size = 'md',
    type = 'button',
    href,
    disabled = false,
    onclick,
    class: customClass = '',
    children,
    ...restProps
}: {
    variant?: 'blue' | 'yellow' | 'white' | 'red' | 'outline';
    size?: 'sm' | 'md' | 'lg';
    type?: 'button' | 'submit' | 'reset';
    href?: string;
    disabled?: boolean;
    onclick?: (e: MouseEvent) => void;
    class?: string;
    children?: Snippet;
    [key: string]: any;
} = $props();

const baseClasses = 'neo-btn rounded-lg cursor-pointer select-none';

const sizeClasses = {
    sm: 'px-3 py-1.5 text-xs font-black shadow-[2px_2px_0px_0px_#000] min-h-[32px] sm:min-h-[34px] inline-flex items-center justify-center',
    md: 'px-4 py-2 text-sm font-black shadow-[3px_3px_0px_0px_#000] min-h-[38px] sm:min-h-[42px] inline-flex items-center justify-center',
    lg: 'px-5 py-2.5 text-sm sm:text-base sm:px-6 sm:py-3 font-black shadow-[4px_4px_0px_0px_#000] min-h-[42px] sm:min-h-[48px] inline-flex items-center justify-center',
};

const variantClasses = {
    blue: 'neo-btn-blue',
    yellow: 'neo-btn-yellow',
    white: 'neo-btn-white',
    red: 'border-2 border-black bg-red-500 text-white shadow-[3px_3px_0px_0px_#000] hover:bg-red-600',
    outline: 'border-2 border-black bg-transparent text-black hover:bg-neutral-100 shadow-[2px_2px_0px_0px_#000]',
};

const computedClasses = $derived(
    `${baseClasses} ${sizeClasses[size]} ${variantClasses[variant]} ${disabled ? 'opacity-50 cursor-not-allowed pointer-events-none' : ''} ${customClass}`
);
</script>

{#if href}
    <Link {href} class={computedClasses} {...restProps}>
        {@render children?.()}
    </Link>
{:else}
    <button {type} {disabled} {onclick} class={computedClasses} {...restProps}>
        {@render children?.()}
    </button>
{/if}
