<script lang="ts">
import { onDestroy } from 'svelte';
import { router } from '@inertiajs/svelte';
import AdminLayout from '@/components/admin/AdminLayout.svelte';
import AdminCard from '@/components/admin/AdminCard.svelte';
import AdminBadge from '@/components/admin/AdminBadge.svelte';
import AdminButton from '@/components/admin/AdminButton.svelte';
import AdminTable from '@/components/admin/AdminTable.svelte';
import AdminTableTh from '@/components/admin/AdminTableTh.svelte';
import AdminSelect from '@/components/admin/AdminSelect.svelte';
import AdminSearchInput from '@/components/admin/AdminSearchInput.svelte';
import AdminPagination from '@/components/admin/AdminPagination.svelte';
import { formatDate, formatRupiah } from '@/lib/formatters';

interface CalculationItem {
    id: string;
    title: string;
    status: string;
    split_mode: string;
    grand_total_minor: number;
    participants_count: number;
    items_count: number;
    created_at: string;
    user?: {
        id: string;
        name: string;
        email: string;
    };
}

let {
    calculations,
    filters = { search: '', status: '', mode: '', sort: 'created_at', direction: 'desc' },
}: {
    calculations: {
        data: CalculationItem[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
        total: number;
        from: number;
        to: number;
    };
    filters?: {
        search?: string;
        status?: string;
        mode?: string;
        sort?: string;
        direction?: 'asc' | 'desc';
    };
} = $props();

let search = $state((() => filters.search || '')());
let selectedStatus = $state((() => filters.status || '')());
let selectedMode = $state((() => filters.mode || '')());
let currentSort = $state((() => filters.sort || 'created_at')());
let currentDirection = $state<'asc' | 'desc'>((() => filters.direction === 'asc' ? 'asc' : 'desc')());
let debounceTimer: ReturnType<typeof setTimeout> | undefined;

const statusFilterOptions = [
    { value: '', label: 'Semua Status' },
    { value: 'completed', label: 'Selesai (Completed)' },
    { value: 'draft', label: 'Draf (Draft)' },
];

const modeFilterOptions = [
    { value: '', label: 'Semua Mode' },
    { value: 'itemized', label: 'Per Item (Itemized)' },
    { value: 'equal', label: 'Bagi Rata (Equal)' },
];

const hasActiveFilters = $derived(
    Boolean(search.trim() || selectedStatus || selectedMode || currentSort !== 'created_at' || currentDirection !== 'desc')
);

function applyFilters() {
    router.get(
        '/admin/calculations',
        {
            ...(search ? { search } : {}),
            ...(selectedStatus ? { status: selectedStatus } : {}),
            ...(selectedMode ? { mode: selectedMode } : {}),
            ...(currentSort && currentSort !== 'created_at' ? { sort: currentSort } : {}),
            ...(currentDirection && currentDirection !== 'desc' ? { direction: currentDirection } : {}),
        },
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
}

function handleSort(field: string) {
    if (currentSort === field) {
        currentDirection = currentDirection === 'asc' ? 'desc' : 'asc';
    } else {
        currentSort = field;
        currentDirection = 'asc';
    }
    applyFilters();
}

function resetAllFilters() {
    search = '';
    selectedStatus = '';
    selectedMode = '';
    currentSort = 'created_at';
    currentDirection = 'desc';
    router.get('/admin/calculations', {}, { preserveState: true, preserveScroll: true });
}

function handleSearchInput(e: Event) {
    search = (e.currentTarget as HTMLInputElement).value;
    if (debounceTimer) clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        applyFilters();
    }, 350);
}

function handleClearSearch() {
    search = '';
    applyFilters();
}

onDestroy(() => {
    if (debounceTimer) clearTimeout(debounceTimer);
});
</script>

<AdminLayout
    title="Semua Tagihan Platform"
    subtitle="Monitoring menyeluruh perhitungan tagihan dan rincian pembagian patungan"
>
    <!-- Filter & Search Controls -->
    <AdminCard class="mb-5 sm:mb-6 relative z-20" overflow="visible">
        <div class="flex flex-col gap-3.5 lg:flex-row lg:items-center lg:justify-between">
            <!-- Search Input -->
            <div class="w-full lg:max-w-xs">
                <AdminSearchInput
                    bind:value={search}
                    placeholder="Cari judul atau pemilik..."
                    oninput={handleSearchInput}
                    onclear={handleClearSearch}
                    class="w-full"
                />
            </div>

            <!-- Custom Selects & Reset Button -->
            <div class="flex flex-wrap items-center gap-2.5 sm:gap-3">
                <!-- Status Custom Select -->
                <div class="w-full sm:w-48">
                    <AdminSelect
                        bind:value={selectedStatus}
                        options={statusFilterOptions}
                        placeholder="Pilih Status"
                        onchange={() => applyFilters()}
                        class="w-full"
                    />
                </div>

                <!-- Mode Custom Select -->
                <div class="w-full sm:w-48">
                    <AdminSelect
                        bind:value={selectedMode}
                        options={modeFilterOptions}
                        placeholder="Pilih Mode"
                        onchange={() => applyFilters()}
                        class="w-full"
                    />
                </div>

                <!-- Reset Filter Button -->
                {#if hasActiveFilters}
                    <button
                        type="button"
                        onclick={resetAllFilters}
                        class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-colors cursor-pointer"
                        title="Reset semua filter"
                    >
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span>Reset</span>
                    </button>
                {/if}
            </div>
        </div>
    </AdminCard>

    <!-- Calculations Table Card -->
    <AdminCard
        title="Daftar Tagihan ({calculations.total})"
        description={calculations.total > 0 ? `Menampilkan data ke-${calculations.from} hingga ke-${calculations.to}` : ''}
        noPadding
        class="relative z-10"
    >
        <AdminTable empty={calculations.data.length === 0} emptyText="Tidak ada tagihan yang cocok dengan kriteria.">
            {#snippet header()}
                <tr>
                    <AdminTableTh
                        label="Judul Tagihan"
                        field="title"
                        sortable
                        {currentSort}
                        {currentDirection}
                        onsort={handleSort}
                        class="px-5 py-3 min-w-[13rem]"
                    />
                    <AdminTableTh
                        label="Pemilik"
                        class="px-4 py-3 min-w-[10rem]"
                    />
                    <AdminTableTh
                        label="Mode & Peserta"
                        class="px-4 py-3 min-w-[8.5rem]"
                    />
                    <AdminTableTh
                        label="Total Nilai"
                        field="grand_total_minor"
                        sortable
                        {currentSort}
                        {currentDirection}
                        onsort={handleSort}
                        align="right"
                        class="px-4 py-3 min-w-[8rem]"
                    />
                    <AdminTableTh
                        label="Status"
                        field="status"
                        sortable
                        {currentSort}
                        {currentDirection}
                        onsort={handleSort}
                        align="center"
                        class="px-4 py-3 min-w-[7rem]"
                    />
                    <AdminTableTh
                        label="Dibuat"
                        field="created_at"
                        sortable
                        {currentSort}
                        {currentDirection}
                        onsort={handleSort}
                        class="px-4 py-3 min-w-[8.5rem]"
                    />
                    <AdminTableTh
                        label="Aksi"
                        align="right"
                        class="px-5 py-3 min-w-[5.5rem]"
                    />
                </tr>
            {/snippet}

            {#each calculations.data as calc}
                <tr class="hover:bg-slate-50/70 transition-colors">
                    <td class="px-5 py-3.5">
                        <p class="font-semibold text-slate-900 truncate">{calc.title}</p>
                        <p class="text-xs text-slate-400 font-mono">ID: {calc.id.slice(0, 8)}...</p>
                    </td>
                    <td class="px-4 py-3.5">
                        {#if calc.user}
                            <p class="text-sm font-medium text-slate-800 truncate">{calc.user.name}</p>
                            <p class="text-xs text-slate-500 truncate">{calc.user.email}</p>
                        {:else}
                            <span class="text-xs italic text-slate-400">Pengguna Tamu</span>
                        {/if}
                    </td>
                    <td class="px-4 py-3.5 text-xs text-slate-600 whitespace-nowrap">
                        <span class="font-medium text-slate-900">
                            {calc.split_mode === 'itemized' ? 'Per Item' : 'Bagi Rata'}
                        </span>
                        <div class="text-[11px] text-slate-500">
                            {calc.participants_count} peserta • {calc.items_count} item
                        </div>
                    </td>
                    <td class="px-4 py-3.5 text-right font-semibold text-slate-900 whitespace-nowrap">
                        {formatRupiah(calc.grand_total_minor)}
                    </td>
                    <td class="px-4 py-3.5 text-center whitespace-nowrap">
                        <AdminBadge
                            variant={calc.status === 'completed' ? 'green' : 'yellow'}
                            size="sm"
                        >
                            {calc.status === 'completed' ? 'Selesai' : 'Draf'}
                        </AdminBadge>
                    </td>
                    <td class="px-4 py-3.5 text-xs text-slate-500 whitespace-nowrap">
                        {formatDate(calc.created_at)}
                    </td>
                    <td class="px-5 py-3.5 text-right whitespace-nowrap">
                        <AdminButton
                            href={`/admin/calculations/${calc.id}`}
                            variant="secondary"
                            size="sm"
                        >
                            Detail
                        </AdminButton>
                    </td>
                </tr>
            {/each}
        </AdminTable>

        {#snippet footer()}
            <AdminPagination links={calculations.links} />
        {/snippet}
    </AdminCard>
</AdminLayout>
