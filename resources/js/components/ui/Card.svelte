<script lang="ts">
import type { Snippet } from 'svelte';

let {
    variant = 'white',
    shadow = 'md',
    class: customClass = '',
    header,
    footer,
    children,
    ...restProps
}: {
    variant?: 'white' | 'yellow' | 'blue';
    shadow?: 'sm' | 'md' | 'lg' | 'xl';
    class?: string;
    header?: Snippet;
    footer?: Snippet;
    children?: Snippet;
    [key: string]: any;
} = $props();

const variantClasses = {
    white: 'bg-white text-black border-2 border-black',
    yellow: 'bg-yellow-400 text-black border-2 border-black',
    blue: 'bg-blue-600 text-white border-2 border-black',
};

const shadowClasses = {
    sm: 'shadow-[2px_2px_0px_0px_#000]',
    md: 'shadow-[4px_4px_0px_0px_#000]',
    lg: 'shadow-[6px_6px_0px_0px_#000]',
    xl: 'shadow-[8px_8px_0px_0px_#000]',
};
</script>

<div
    class="rounded-xl {variantClasses[variant]} {shadowClasses[shadow]} {customClass}"
    {...restProps}
>
    {#if header}
        <div class="border-b-2 border-black p-5">
            {@render header()}
        </div>
    {/if}

    <div class="p-5">
        {@render children?.()}
    </div>

    {#if footer}
        <div class="border-t-2 border-black p-5">
            {@render footer()}
        </div>
    {/if}
</div>
