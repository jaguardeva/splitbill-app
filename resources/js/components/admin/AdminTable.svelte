<script lang="ts">
import { Inbox } from '@lucide/svelte';
import type { Snippet } from 'svelte';

let {
    class: customClass = '',
    header,
    children,
    empty = false,
    emptyText = 'Tidak ada data ditemukan.',
}: {
    class?: string;
    header?: Snippet;
    children?: Snippet;
    empty?: boolean;
    emptyText?: string;
} = $props();
</script>

<div class="w-full overflow-x-auto rounded-lg border border-slate-200/90 bg-white shadow-xs {customClass}">
    <table class="w-full text-left text-sm text-slate-700">
        {#if header}
            <thead class="border-b border-slate-200 bg-slate-50/80 text-xs font-semibold uppercase tracking-wider text-slate-500">
                {@render header()}
            </thead>
        {/if}

        <tbody class="divide-y divide-slate-100">
            {#if empty}
                <tr>
                    <td colspan="100" class="py-12 text-center text-slate-400">
                        <div class="flex flex-col items-center justify-center gap-2">
                            <Inbox class="size-8 text-slate-300" strokeWidth={1.5} aria-hidden="true" />
                            <p class="text-sm font-medium text-slate-500">{emptyText}</p>
                        </div>
                    </td>
                </tr>
            {:else}
                {@render children?.()}
            {/if}
        </tbody>
    </table>
</div>
