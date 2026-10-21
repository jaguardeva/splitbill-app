<script lang="ts">
import AdminLayout from '@/components/admin/AdminLayout.svelte';
import AdminCard from '@/components/admin/AdminCard.svelte';
import AdminBadge from '@/components/admin/AdminBadge.svelte';
import AdminButton from '@/components/admin/AdminButton.svelte';
import AdminTable from '@/components/admin/AdminTable.svelte';
import AdminTableTh from '@/components/admin/AdminTableTh.svelte';
import { formatDate, formatNumber, formatPercent, formatRupiah } from '@/lib/formatters';

interface Participant {
    id: string;
    name: string;
    position: number;
}

interface ItemAssignment {
    id: string;
    bill_item_id: string;
    participant_id: string;
    participant?: Participant;
}

interface BillItem {
    id: string;
    description: string;
    quantity: number;
    unit_price_minor: number;
    line_subtotal_minor: number;
    assignments?: ItemAssignment[];
}

interface Adjustment {
    id: string;
    type: string;
    applies_to: string;
    calculation_basis: string;
    amount_minor: number;
    percentage_rate: number | null;
}

interface CalculationDetail {
    id: string;
    title: string;
    currency: string;
    split_mode: string;
    status: string;
    subtotal_minor: number;
    discount_total_minor: number;
    tax_total_minor: number;
    service_charge_minor: number;
    tip_total_minor: number;
    grand_total_minor: number;
    notes: string | null;
    created_at: string;
    updated_at: string;
    user?: {
        id: string;
        name: string;
        email: string;
    };
    participants: Participant[];
    items: BillItem[];
    adjustments: Adjustment[];
}

let {
    calculation,
}: {
    calculation: CalculationDetail;
} = $props();

function getAdjustmentLabel(type: string): string {
    switch (type) {
        case 'discount':
            return 'Diskon';
        case 'tax':
            return 'Pajak (Tax)';
        case 'service_charge':
            return 'Biaya Layanan (Service)';
        case 'tip':
            return 'Tip Sukarela';
        default:
            return type;
    }
}
</script>

<AdminLayout
    title="Inspeksi Tagihan: {calculation.title}"
    subtitle="Audit rincian transaksi, penugasan per peserta, dan penyesuaian biaya"
>
    {#snippet actions()}
        <AdminButton href="/admin/calculations" variant="secondary" size="sm" title="Kembali ke Daftar Tagihan">
            <span class="hidden sm:inline">← Kembali ke Daftar</span>
            <span class="sm:hidden">← Kembali</span>
        </AdminButton>
    {/snippet}

    <!-- Mobile In-Page Back Button -->
    <div class="mb-4 sm:hidden">
        <AdminButton href="/admin/calculations" variant="secondary" size="sm">
            ← Kembali ke Daftar Tagihan
        </AdminButton>
    </div>

    <!-- Top Summary Banner -->
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Subtotal -->
        <div class="rounded-lg border border-slate-200/90 bg-white p-4 shadow-xs">
            <span class="text-xs font-medium text-slate-500">Subtotal Item</span>
            <p class="mt-1 text-xl font-bold text-slate-900">{formatRupiah(calculation.subtotal_minor)}</p>
            <p class="mt-0.5 text-xs text-slate-400">{calculation.items.length} item terdaftar</p>
        </div>

        <!-- Diskon -->
        <div class="rounded-lg border border-slate-200/90 bg-white p-4 shadow-xs">
            <span class="text-xs font-medium text-slate-500">Total Diskon</span>
            <p class="mt-1 text-xl font-bold text-amber-600">-{formatRupiah(calculation.discount_total_minor)}</p>
            <p class="mt-0.5 text-xs text-slate-400">Pengurang tagihan</p>
        </div>

        <!-- Pajak & Layanan & Tip -->
        <div class="rounded-lg border border-slate-200/90 bg-white p-4 shadow-xs">
            <span class="text-xs font-medium text-slate-500">Pajak, Service & Tip</span>
            <p class="mt-1 text-xl font-bold text-slate-900">
                +{formatRupiah(calculation.tax_total_minor + calculation.service_charge_minor + calculation.tip_total_minor)}
            </p>
            <p class="mt-0.5 text-xs text-slate-400">Biaya penyesuaian</p>
        </div>

        <!-- Grand Total -->
        <div class="rounded-lg border border-blue-200 bg-blue-50/50 p-4 shadow-xs">
            <span class="text-xs font-semibold text-blue-700">Grand Total Akhir</span>
            <p class="mt-1 text-2xl font-bold text-blue-700">{formatRupiah(calculation.grand_total_minor)}</p>
            <p class="mt-0.5 text-xs text-blue-600 font-medium">Bagi {calculation.participants.length} peserta</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Main 2-Cols: Line Items & Adjustments -->
        <div class="space-y-6 lg:col-span-2">
            <!-- Items Table Card -->
            <AdminCard title="Rincian Item Tagihan" description="Daftar item dan alokasi ke tiap peserta" noPadding>
                <AdminTable empty={calculation.items.length === 0} emptyText="Tidak ada item dalam tagihan ini.">
                    {#snippet header()}
                        <tr>
                            <AdminTableTh label="Nama Item" class="px-5 py-3 min-w-[10rem]" />
                            <AdminTableTh label="Qty" align="center" class="px-3 py-3 min-w-[3.5rem]" />
                            <AdminTableTh label="Harga Satuan" align="right" class="px-4 py-3 min-w-[6.5rem]" />
                            <AdminTableTh label="Subtotal" align="right" class="px-4 py-3 min-w-[6.5rem]" />
                            <AdminTableTh label="Ditugaskan Kepada" class="px-5 py-3 min-w-[10rem]" />
                        </tr>
                    {/snippet}

                    {#each calculation.items as item}
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="px-5 py-3.5 font-medium text-slate-900">
                                {item.description}
                            </td>
                            <td class="px-3 py-3.5 text-center text-xs text-slate-600 font-mono">
                                {Number(item.quantity)}
                            </td>
                            <td class="px-4 py-3.5 text-right text-xs text-slate-600">
                                {formatRupiah(item.unit_price_minor)}
                            </td>
                            <td class="px-4 py-3.5 text-right font-semibold text-slate-900">
                                {formatRupiah(item.line_subtotal_minor)}
                            </td>
                            <td class="px-5 py-3.5">
                                {#if item.assignments && item.assignments.length > 0}
                                    <div class="flex flex-wrap gap-1">
                                        {#each item.assignments as a}
                                            <span class="rounded bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-700">
                                                {a.participant?.name || 'Peserta'}
                                            </span>
                                        {/each}
                                    </div>
                                {:else}
                                    <span class="text-xs italic text-amber-600">Belum ditugaskan</span>
                                {/if}
                            </td>
                        </tr>
                    {/each}
                </AdminTable>
            </AdminCard>

            <!-- Adjustments Table Card -->
            {#if calculation.adjustments && calculation.adjustments.length > 0}
                <AdminCard title="Penyesuaian Biaya (Adjustments)" description="Diskon, Pajak, Service Charge, dan Tip" noPadding>
                    <AdminTable>
                        {#snippet header()}
                            <tr>
                                <AdminTableTh label="Jenis Biaya" class="px-5 py-3 min-w-[8rem]" />
                                <AdminTableTh label="Dasar Perhitungan" class="px-4 py-3 min-w-[8rem]" />
                                <AdminTableTh label="Tarif / Nilai" class="px-4 py-3 min-w-[7rem]" />
                                <AdminTableTh label="Nominal Akhir" align="right" class="px-5 py-3 min-w-[7rem]" />
                            </tr>
                        {/snippet}


                        {#each calculation.adjustments as adj}
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="px-5 py-3.5 font-medium text-slate-900">
                                    {getAdjustmentLabel(adj.type)}
                                </td>
                                <td class="px-4 py-3.5 text-xs text-slate-500 capitalize">
                                    {adj.calculation_basis === 'percentage' ? 'Persentase' : 'Nominal Tetap'}
                                </td>
                                <td class="px-4 py-3.5 text-xs text-slate-600 font-medium">
                                    {#if adj.calculation_basis === 'percentage'}
                                        {formatPercent(adj.percentage_rate)}
                                    {:else}
                                        {formatRupiah(adj.amount_minor)}
                                    {/if}
                                </td>
                                <td class="px-5 py-3.5 text-right font-semibold {adj.type === 'discount' ? 'text-amber-600' : 'text-slate-900'}">
                                    {adj.type === 'discount' ? '-' : '+'}{formatRupiah(adj.amount_minor)}
                                </td>
                            </tr>
                        {/each}
                    </AdminTable>
                </AdminCard>
            {/if}
        </div>

        <!-- 1-Col: Metadata & Participants -->
        <div class="space-y-6">
            <!-- Metadata Card -->
            <AdminCard title="Informasi Tagihan">
                <dl class="divide-y divide-slate-100 text-sm">
                    <div class="flex justify-between py-2.5">
                        <dt class="text-xs text-slate-500">Status</dt>
                        <dd>
                            <AdminBadge variant={calculation.status === 'completed' ? 'green' : 'yellow'} size="sm">
                                {calculation.status === 'completed' ? 'Selesai' : 'Draf'}
                            </AdminBadge>
                        </dd>
                    </div>
                    <div class="flex justify-between py-2.5">
                        <dt class="text-xs text-slate-500">Mode Patungan</dt>
                        <dd class="text-xs font-semibold text-slate-800">
                            {calculation.split_mode === 'itemized' ? 'Per Item (Itemized)' : 'Bagi Rata (Equal)'}
                        </dd>
                    </div>
                    <div class="flex justify-between py-2.5">
                        <dt class="text-xs text-slate-500">Mata Uang</dt>
                        <dd class="text-xs font-mono font-semibold text-slate-800">{calculation.currency}</dd>
                    </div>
                    <div class="flex justify-between py-2.5">
                        <dt class="text-xs text-slate-500">Pemilik Tagihan</dt>
                        <dd class="text-right">
                            {#if calculation.user}
                                <p class="text-xs font-semibold text-slate-800">{calculation.user.name}</p>
                                <p class="text-[11px] text-slate-400">{calculation.user.email}</p>
                            {:else}
                                <span class="text-xs italic text-slate-400">Tamu</span>
                            {/if}
                        </dd>
                    </div>
                    <div class="flex justify-between py-2.5">
                        <dt class="text-xs text-slate-500">Tanggal Dibuat</dt>
                        <dd class="text-xs text-slate-600">{formatDate(calculation.created_at)}</dd>
                    </div>
                    <div class="flex justify-between py-2.5">
                        <dt class="text-xs text-slate-500">Terakhir Diperbarui</dt>
                        <dd class="text-xs text-slate-600">{formatDate(calculation.updated_at)}</dd>
                    </div>
                </dl>

                {#if calculation.notes}
                    <div class="mt-4 border-t border-slate-100 pt-3">
                        <p class="text-xs font-medium text-slate-500">Catatan Pemilik:</p>
                        <p class="mt-1 rounded-md bg-slate-50 p-2.5 text-xs text-slate-700 italic">
                            "{calculation.notes}"
                        </p>
                    </div>
                {/if}
            </AdminCard>

            <!-- Participants Card -->
            <AdminCard title="Daftar Peserta ({calculation.participants.length})">
                <div class="divide-y divide-slate-100">
                    {#each calculation.participants as p, i}
                        <div class="flex items-center justify-between py-2.5">
                            <div class="flex items-center gap-2">
                                <span class="flex size-6 items-center justify-center rounded-full bg-slate-100 text-[11px] font-bold text-slate-600">
                                    {i + 1}
                                </span>
                                <span class="text-sm font-semibold text-slate-800">{p.name}</span>
                            </div>
                        </div>
                    {/each}
                </div>
            </AdminCard>
        </div>
    </div>
</AdminLayout>
