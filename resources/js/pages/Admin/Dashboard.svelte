<script lang="ts">
import AdminLayout from '@/components/admin/AdminLayout.svelte';
import AdminCard from '@/components/admin/AdminCard.svelte';
import AdminStatCard from '@/components/admin/AdminStatCard.svelte';
import AdminBadge from '@/components/admin/AdminBadge.svelte';
import AdminButton from '@/components/admin/AdminButton.svelte';
import AdminTable from '@/components/admin/AdminTable.svelte';
import AdminTableTh from '@/components/admin/AdminTableTh.svelte';
import { formatDate, formatNumber, formatRupiah } from '@/lib/formatters';

interface Stats {
    total_users: number;
    total_admins: number;
    total_regular_users: number;
    verified_users_count: number;
    total_calculations: number;
    total_amount_minor: number;
    completed_calculations: number;
    draft_calculations: number;
    new_users_last_7_days: number;
    new_calculations_last_7_days: number;
}

interface RecentCalculation {
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

interface RecentUser {
    id: string;
    name: string;
    email: string;
    role: 'admin' | 'user';
    email_verified_at: string | null;
    calculations_count: number;
    created_at: string;
}

let {
    stats,
    recentCalculations = [],
    recentUsers = [],
}: {
    stats: Stats;
    recentCalculations?: RecentCalculation[];
    recentUsers?: RecentUser[];
} = $props();
</script>

<AdminLayout
    title="Dashboard Ringkasan"
    subtitle="Pantau aktivitas platform, metrik finansial patungan, dan manajemen pengguna"
>
    {#snippet actions()}
        <AdminButton href="/admin/users" variant="secondary" size="sm" title="Kelola Pengguna">
            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <span class="hidden sm:inline">Kelola </span><span>Pengguna</span>
        </AdminButton>
        <AdminButton href="/admin/calculations" variant="primary" size="sm" title="Semua Tagihan">
            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            <span class="hidden sm:inline">Semua </span><span>Tagihan</span>
        </AdminButton>
    {/snippet}

    <!-- KPI Metric Cards Grid -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Total Pengguna -->
        <AdminStatCard
            title="Total Pengguna"
            value={formatNumber(stats.total_users)}
            helper="{stats.verified_users_count} terverifikasi ({stats.total_admins} admin)"
            iconColor="blue"
        >
            {#snippet icon()}
                <svg class="size-6 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            {/snippet}
        </AdminStatCard>

        <!-- Total Tagihan Dibuat -->
        <AdminStatCard
            title="Total Tagihan Dibuat"
            value={formatNumber(stats.total_calculations)}
            helper="{stats.completed_calculations} selesai, {stats.draft_calculations} draf"
            iconColor="yellow"
        >
            {#snippet icon()}
                <svg class="size-6 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            {/snippet}
        </AdminStatCard>

        <!-- Akumulasi Finansial Patungan -->
        <AdminStatCard
            title="Total Nilai Patungan"
            value={formatRupiah(stats.total_amount_minor)}
            helper="Akumulasi seluruh tagihan dihitung"
            iconColor="emerald"
        >
            {#snippet icon()}
                <svg class="size-6 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            {/snippet}
        </AdminStatCard>

        <!-- Pertumbuhan 7 Hari Terakhir -->
        <AdminStatCard
            title="Aktivitas 7 Hari Terakhir"
            value="+{formatNumber(stats.new_users_last_7_days)} User"
            helper="+{stats.new_calculations_last_7_days} tagihan baru"
            iconColor="blue"
        >
            {#snippet icon()}
                <svg class="size-6 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
            {/snippet}
        </AdminStatCard>
    </div>

    <!-- Main Two-Column Grid: Recent Calculations & Recent Users -->
    <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Recent Calculations Table (2 Columns Span) -->
        <div class="lg:col-span-2">
            <AdminCard
                title="Kalkulasi Tagihan Terbaru"
                description="Daftar tagihan yang baru saja dibuat atau diperbarui di platform"
                noPadding
            >
                {#snippet actions()}
                    <AdminButton href="/admin/calculations" variant="ghost" size="sm">
                        Lihat Semua →
                    </AdminButton>
                {/snippet}

                <AdminTable empty={recentCalculations.length === 0} emptyText="Belum ada riwayat tagihan dibuat.">
                    {#snippet header()}
                        <tr>
                            <AdminTableTh label="Tagihan & Pemilik" class="px-5 py-3 min-w-[12rem]" />
                            <AdminTableTh label="Mode" class="px-4 py-3 min-w-[7rem]" />
                            <AdminTableTh label="Total Nilai" align="right" class="px-4 py-3 min-w-[7rem]" />
                            <AdminTableTh label="Status" align="center" class="px-4 py-3 min-w-[6rem]" />
                            <AdminTableTh label="Aksi" align="right" class="px-5 py-3 min-w-[5rem]" />
                        </tr>
                    {/snippet}


                    {#each recentCalculations as calc}
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="px-5 py-3.5">
                                <div class="font-semibold text-slate-900">{calc.title}</div>
                                <div class="text-xs text-slate-500">
                                    {calc.user ? calc.user.name : 'Tamu'} • {formatDate(calc.created_at)}
                                </div>
                            </td>
                            <td class="px-4 py-3.5 text-xs text-slate-600">
                                <span class="capitalize">{calc.split_mode === 'itemized' ? 'Per Item' : 'Rata (Equal)'}</span>
                                <span class="block text-[11px] text-slate-400">{calc.participants_count} peserta</span>
                            </td>
                            <td class="px-4 py-3.5 text-right font-medium text-slate-900">
                                {formatRupiah(calc.grand_total_minor)}
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <AdminBadge
                                    variant={calc.status === 'completed' ? 'green' : 'yellow'}
                                    size="sm"
                                >
                                    {calc.status === 'completed' ? 'Selesai' : 'Draf'}
                                </AdminBadge>
                            </td>
                            <td class="px-5 py-3.5 text-right">
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
            </AdminCard>
        </div>

        <!-- Recent Users (1 Column) -->
        <div>
            <AdminCard
                title="Pengguna Baru"
                description="Pengguna yang baru saja mendaftar akun"
                noPadding
            >
                {#snippet actions()}
                    <AdminButton href="/admin/users" variant="ghost" size="sm">
                        Kelola →
                    </AdminButton>
                {/snippet}

                <div class="divide-y divide-slate-100">
                    {#if recentUsers.length === 0}
                        <div class="py-8 text-center text-xs text-slate-400">Belum ada pengguna.</div>
                    {:else}
                        {#each recentUsers as u}
                            <div class="flex items-center justify-between px-5 py-3.5 hover:bg-slate-50/60 transition-colors">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="flex size-9 shrink-0 items-center justify-center rounded-full bg-blue-100 font-bold text-xs text-blue-700">
                                        {u.name.charAt(0).toUpperCase()}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-slate-900">{u.name}</p>
                                        <p class="truncate text-xs text-slate-500">{u.email}</p>
                                    </div>
                                </div>
                                <div class="flex flex-col items-end gap-1 shrink-0 pl-2">
                                    <AdminBadge
                                        variant={u.role === 'admin' ? 'blue' : 'neutral'}
                                        size="sm"
                                    >
                                        {u.role === 'admin' ? 'Admin' : 'User'}
                                    </AdminBadge>
                                    <span class="text-[10px] text-slate-400">{formatDate(u.created_at)}</span>
                                </div>
                            </div>
                        {/each}
                    {/if}
                </div>
            </AdminCard>
        </div>
    </div>
</AdminLayout>
