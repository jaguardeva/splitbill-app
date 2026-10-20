<script lang="ts">
import type { Snippet } from 'svelte';

let {
    variant = 'yellow',
    size = 'sm',
    class: customClass = '',
    children,
    ...restProps
}: {
    variant?: 'yellow' | 'blue' | 'white' | 'red';
    size?: 'sm' | 'md';
    class?: string;
    children?: Snippet;
    [key: string]: any;
} = $props();

const variantClasses = {
    yellow: 'bg-yellow-400 text-black border-2 border-black',
    blue: 'bg-blue-600 text-white border-2 border-black',
    white: 'bg-white text-black border-2 border-black',
    red: 'bg-red-500 text-white border-2 border-black',
};

const sizeClasses = {
    sm: 'px-2 py-0.5 text-[11px] font-black uppercase tracking-wider shadow-[1px_1px_0px_0px_#000]',
    md: 'px-3 py-1 text-xs font-black uppercase tracking-wider shadow-[2px_2px_0px_0px_#000]',
};
</script>

<span
    class="inline-flex items-center rounded-md select-none {variantClasses[variant]} {sizeClasses[size]} {customClass}"
    {...restProps}
>
    {@render children?.()}
</span>
