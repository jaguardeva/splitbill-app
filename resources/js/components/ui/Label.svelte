<script lang="ts">
import type { Snippet } from 'svelte';

let {
    for: htmlFor,
    class: customClass = '',
    children,
    ...restProps
}: {
    for?: string;
    class?: string;
    children?: Snippet;
    [key: string]: any;
} = $props();
</script>

<label
    for={htmlFor}
    class="block text-xs font-black uppercase tracking-wider text-black mb-1.5 {customClass}"
    {...restProps}
>
    {@render children?.()}
</label>
