<script lang="ts">
import type { Snippet } from 'svelte';

let {
    class: customClass = '',
    children,
    ...restProps
}: {
    class?: string;
    children?: Snippet;
    [key: string]: any;
} = $props();
</script>

<div class="flex flex-col gap-3.5 {customClass}" {...restProps}>
    {@render children?.()}
</div>
