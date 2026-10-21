<script lang="ts">
import { Link } from '@inertiajs/svelte';

let {
    links = [],
    class: customClass = '',
}: {
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
    class?: string;
} = $props();

function cleanLabel(label: string): string {
    return label
        .replace('&laquo; Previous', '← Sebelumnya')
        .replace('Next &raquo;', 'Selanjutnya →')
        .replace('&laquo;', '←')
        .replace('&raquo;', '→');
}
</script>

{#if links.length > 3}
    <nav class="flex items-center justify-center gap-1 py-4 {customClass}" aria-label="Pagination">
        {#each links as link}
            {#if link.url}
                <Link
                    href={link.url}
                    preserveScroll
                    preserveState
                    class="inline-flex min-h-[36px] min-w-[36px] items-center justify-center rounded-md px-3 text-sm font-medium transition-colors cursor-pointer {link.active
                        ? 'bg-blue-600 text-white font-semibold shadow-xs'
                        : 'bg-white text-slate-700 hover:bg-slate-50 border border-slate-200'}"
                >
                    {cleanLabel(link.label)}
                </Link>
            {:else}
                <span
                    class="inline-flex min-h-[36px] min-w-[36px] items-center justify-center rounded-md border border-slate-100 bg-slate-50 px-3 text-sm text-slate-400 select-none"
                >
                    {cleanLabel(link.label)}
                </span>
            {/if}
        {/each}
    </nav>
{/if}
