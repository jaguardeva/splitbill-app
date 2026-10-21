<script lang="ts">
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
                            <svg class="size-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                            </svg>
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
