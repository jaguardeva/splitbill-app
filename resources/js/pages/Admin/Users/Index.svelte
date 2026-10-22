<script lang="ts">
import { onDestroy } from 'svelte';
import { page, router } from '@inertiajs/svelte';
import AdminLayout from '@/components/admin/AdminLayout.svelte';
import AdminCard from '@/components/admin/AdminCard.svelte';
import AdminBadge from '@/components/admin/AdminBadge.svelte';
import AdminButton from '@/components/admin/AdminButton.svelte';
import AdminTable from '@/components/admin/AdminTable.svelte';
import AdminTableTh from '@/components/admin/AdminTableTh.svelte';
import AdminSelect from '@/components/admin/AdminSelect.svelte';
import AdminSearchableSelect from '@/components/admin/AdminSearchableSelect.svelte';
import AdminSearchInput from '@/components/admin/AdminSearchInput.svelte';
import AdminPagination from '@/components/admin/AdminPagination.svelte';
import { formatDate } from '@/lib/formatters';
import { RotateCcw } from '@lucide/svelte';

interface UserItem {
    id: string;
    name: string;
    email: string;
    role: 'admin' | 'user';
    email_verified_at: string | null;
    calculations_count: number;
    created_at: string;
}

let {
    users,
    filters = { search: '', role: '', verified: '', sort: 'created_at', direction: 'desc' },
}: {
    users: {
        data: UserItem[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
        total: number;
        from: number;
        to: number;
    };
    filters?: {
        search?: string;
        role?: string;
        verified?: string;
        sort?: string;
        direction?: 'asc' | 'desc';
    };
} = $props();

const currentAuthUser = $derived((page.props as any).auth?.user);

let search = $state((() => filters.search || '')());
let selectedRole = $state((() => filters.role || '')());
let selectedVerified = $state((() => filters.verified || '')());
let currentSort = $state((() => filters.sort || 'created_at')());
let currentDirection = $state<'asc' | 'desc'>((() => filters.direction === 'asc' ? 'asc' : 'desc')());
let debounceTimer: ReturnType<typeof setTimeout> | undefined;

// Modal state for role update
let roleModalOpen = $state(false);
let targetUser = $state<UserItem | null>(null);
let newRoleSelection = $state<'admin' | 'user'>('user');
let roleProcessing = $state(false);

const roleFilterOptions = [
    { value: '', label: 'Semua Peran' },
    { value: 'admin', label: 'Administrator' },
    { value: 'user', label: 'Pengguna Biasa' },
];

const verifiedFilterOptions = [
    { value: '', label: 'Semua Verifikasi' },
    { value: 'verified', label: 'Terverifikasi' },
    { value: 'unverified', label: 'Belum Verifikasi' },
];

const roleModalOptions = [
    {
        value: 'user',
        label: 'Pengguna Biasa (User)',
        description: 'Akses standar kalkulator & riwayat tagihan pribadi',
    },
    {
        value: 'admin',
        label: 'Administrator (Admin)',
        description: 'Akses penuh konsol admin, manajemen pengguna & audit platform',
    },
];

const hasActiveFilters = $derived(
    Boolean(search.trim() || selectedRole || selectedVerified || currentSort !== 'created_at' || currentDirection !== 'desc')
);

function applyFilters() {
    router.get(
        '/admin/users',
        {
            ...(search ? { search } : {}),
            ...(selectedRole ? { role: selectedRole } : {}),
            ...(selectedVerified ? { verified: selectedVerified } : {}),
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
    selectedRole = '';
    selectedVerified = '';
    currentSort = 'created_at';
    currentDirection = 'desc';
    router.get('/admin/users', {}, { preserveState: true, preserveScroll: true });
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

function openRoleModal(user: UserItem) {
    targetUser = user;
    newRoleSelection = user.role;
    roleModalOpen = true;
}

function submitRoleChange() {
    if (!targetUser) return;

    roleProcessing = true;
    router.patch(
        `/admin/users/${targetUser.id}/role`,
        { role: newRoleSelection },
        {
            preserveScroll: true,
            onFinish: () => {
                roleProcessing = false;
                roleModalOpen = false;
            },
        }
    );
}
</script>

<AdminLayout
    title="Manajemen Pengguna"
    subtitle="Kelola pengguna platform, verifikasi akun, dan pembagian hak akses peran (Role)"
>
    <!-- Filter & Search Controls -->
    <AdminCard class="mb-5 sm:mb-6 relative z-20" overflow="visible">
        <div class="flex flex-col gap-3.5 lg:flex-row lg:items-center lg:justify-between">
            <!-- Search bar -->
            <div class="w-full lg:max-w-xs">
                <AdminSearchInput
                    bind:value={search}
                    placeholder="Cari nama atau email..."
                    oninput={handleSearchInput}
                    onclear={handleClearSearch}
                    class="w-full"
                />
            </div>

            <!-- Dropdown Filters & Actions -->
            <div class="flex flex-wrap items-center gap-2.5 sm:gap-3">
                <!-- Role Custom Select -->
                <div class="w-full sm:w-44">
                    <AdminSelect
                        bind:value={selectedRole}
                        options={roleFilterOptions}
                        placeholder="Pilih Peran"
                        onchange={() => applyFilters()}
                        class="w-full"
                    />
                </div>

                <!-- Verification Custom Select -->
                <div class="w-full sm:w-48">
                    <AdminSelect
                        bind:value={selectedVerified}
                        options={verifiedFilterOptions}
                        placeholder="Pilih Verifikasi"
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
                        <RotateCcw class="size-3.5" aria-hidden="true" />
                        <span>Reset</span>
                    </button>
                {/if}
            </div>
        </div>
    </AdminCard>

    <!-- Users Table Card -->
    <AdminCard
        title="Daftar Pengguna ({users.total})"
        description={users.total > 0 ? `Menampilkan data ke-${users.from} hingga ke-${users.to}` : ''}
        noPadding
        class="relative z-10"
    >
        <AdminTable empty={users.data.length === 0} emptyText="Tidak ada pengguna yang cocok dengan kriteria.">
            {#snippet header()}
                <tr>
                    <AdminTableTh
                        label="Pengguna"
                        field="name"
                        sortable
                        {currentSort}
                        {currentDirection}
                        onsort={handleSort}
                        class="px-5 py-3 min-w-[14rem]"
                    />
                    <AdminTableTh
                        label="Peran (Role)"
                        field="role"
                        sortable
                        {currentSort}
                        {currentDirection}
                        onsort={handleSort}
                        class="px-4 py-3 min-w-[8rem]"
                    />
                    <AdminTableTh
                        label="Verifikasi Email"
                        class="px-4 py-3 min-w-[9rem]"
                    />
                    <AdminTableTh
                        label="Total Tagihan Dibuat"
                        field="calculations_count"
                        sortable
                        {currentSort}
                        {currentDirection}
                        onsort={handleSort}
                        align="center"
                        class="px-4 py-3 min-w-[7.5rem]"
                    />
                    <AdminTableTh
                        label="Tanggal Bergabung"
                        field="created_at"
                        sortable
                        {currentSort}
                        {currentDirection}
                        onsort={handleSort}
                        class="px-4 py-3 min-w-[9rem]"
                    />
                    <AdminTableTh
                        label="Aksi"
                        align="right"
                        class="px-5 py-3 min-w-[6rem]"
                    />
                </tr>
            {/snippet}

            {#each users.data as u}
                <tr class="hover:bg-slate-50/70 transition-colors">
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-3">
                            <div class="flex size-9 shrink-0 items-center justify-center rounded-full bg-blue-100 font-bold text-xs text-blue-700">
                                {u.name.charAt(0).toUpperCase()}
                            </div>
                            <div class="min-w-0">
                                <p class="font-semibold text-slate-900 truncate">{u.name}</p>
                                <p class="text-xs text-slate-500 truncate">{u.email}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3.5 whitespace-nowrap">
                        <AdminBadge
                            variant={u.role === 'admin' ? 'blue' : 'neutral'}
                        >
                            {u.role === 'admin' ? 'Administrator' : 'Pengguna'}
                        </AdminBadge>
                    </td>
                    <td class="px-4 py-3.5 whitespace-nowrap">
                        <AdminBadge
                            variant={u.email_verified_at ? 'green' : 'yellow'}
                            size="sm"
                        >
                            {u.email_verified_at ? 'Terverifikasi' : 'Belum Verifikasi'}
                        </AdminBadge>
                    </td>
                    <td class="px-4 py-3.5 text-center font-medium text-slate-700 whitespace-nowrap">
                        {u.calculations_count}
                    </td>
                    <td class="px-4 py-3.5 text-xs text-slate-500 whitespace-nowrap">
                        {formatDate(u.created_at)}
                    </td>
                    <td class="px-5 py-3.5 text-right whitespace-nowrap">
                        <AdminButton
                            variant="secondary"
                            size="sm"
                            onclick={() => openRoleModal(u)}
                        >
                            Ubah Role
                        </AdminButton>
                    </td>
                </tr>
            {/each}
        </AdminTable>

        {#snippet footer()}
            <AdminPagination links={users.links} />
        {/snippet}
    </AdminCard>
</AdminLayout>

<!-- Modal Ubah Role Pengguna -->
{#if roleModalOpen && targetUser}
    <div
        role="presentation"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-xs"
    >
        <div class="w-full max-w-md rounded-lg border border-slate-200 bg-white p-5 sm:p-6 shadow-xl">
            <h3 class="text-base font-bold text-slate-900">Ubah Peran (Role) Pengguna</h3>
            <p class="mt-1 text-sm text-slate-500">
                Pilih tingkatan hak akses untuk pengguna <strong>{targetUser.name}</strong> ({targetUser.email}).
            </p>

            <div class="mt-5 space-y-3">
                <label class="block">
                    <span class="text-xs font-semibold text-slate-700 mb-1.5 block">Pilih Role Baru:</span>
                    <AdminSearchableSelect
                        bind:value={newRoleSelection}
                        options={roleModalOptions}
                        placeholder="Pilih Peran"
                        searchPlaceholder="Cari peran..."
                        class="w-full"
                    />
                </label>

                {#if currentAuthUser?.id === targetUser.id && newRoleSelection !== 'admin'}
                    <div class="rounded-lg border border-rose-200 bg-rose-50 p-3 text-xs text-rose-700">
                        ⚠️ <strong>Peringatan:</strong> Ini adalah akun Anda sendiri. Anda tidak diperkenankan mencabut hak admin akun Anda sendiri untuk menghindari hilangnya akses administrator platform.
                    </div>
                {/if}
            </div>

            <div class="mt-6 flex items-center justify-end gap-2.5 sm:gap-3">
                <AdminButton
                    variant="secondary"
                    size="sm"
                    disabled={roleProcessing}
                    onclick={() => (roleModalOpen = false)}
                >
                    Batal
                </AdminButton>
                <AdminButton
                    variant="primary"
                    size="sm"
                    disabled={roleProcessing || (currentAuthUser?.id === targetUser.id && newRoleSelection !== 'admin')}
                    onclick={submitRoleChange}
                >
                    {roleProcessing ? 'Menyimpan...' : 'Simpan Perubahan'}
                </AdminButton>
            </div>
        </div>
    </div>
{/if}
