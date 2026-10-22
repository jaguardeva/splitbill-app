<script lang="ts">
import AppHead from '@/components/AppHead.svelte';
import Navbar from '@/components/Navbar.svelte';
import Footer from '@/components/Footer.svelte';
import Button from '@/components/ui/Button.svelte';
import Badge from '@/components/ui/Badge.svelte';
import Toast from '@/components/ui/Toast.svelte';
import ExportModal from '@/components/calculator/ExportModal.svelte';
import { formatDate, formatRupiah } from '@/lib/formatters';
import type { CalculationPreviewResult } from '@/lib/calculator-preview';
import { Link, page, router } from '@inertiajs/svelte';
import { ArrowLeft, Copy, Pencil, Share2 } from '@lucide/svelte';

interface CalculationModel {
    id: string;
    title: string;
    currency: string;
    split_mode: string;
    status: string;
    notes?: string | null;
    created_at: string;
    updated_at: string;
    items?: Array<{
        id: string;
        description: string;
        quantity: number;
        unit_price_minor: number;
        line_subtotal_minor: number;
        participants: Array<{ id: string; name: string }>;
    }>;
}

let {
    calculation,
    breakdown,
}: {
    calculation: CalculationModel;
    breakdown: CalculationPreviewResult;
} = $props();

const user = $derived((page.props as any).auth?.user);
const successMessage = $derived((page.props as any).flash?.success);

let isShareModalOpen = $state(false);

const participantList = $derived(Object.values(breakdown.participants || {}));

function handleDuplicate() {
    router.post(`/calculations/${calculation.id}/duplicate`);
}
</script>

<AppHead title={`${calculation.title} - Rincian Patungan`} />

<div class="min-h-screen bg-neutral-50 text-black flex flex-col font-sans">
    <Navbar {user} />

    <main class="mx-auto w-full max-w-[1240px] px-4 py-8 sm:px-6 lg:px-8 flex-1">
        <!-- Back Navigation & Title -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between pb-6 border-b-[3px] border-black">
            <div>
                <Button
                    href="/calculations"
                    variant="white"
                    size="sm"
                    class="mb-2"
                >
                    <ArrowLeft class="mr-1 size-3.5" strokeWidth={2.5} aria-hidden="true" />
                    Kembali ke Riwayat
                </Button>
                <h1 class="text-2xl font-black tracking-tight text-black sm:text-4xl mt-1">
                    {calculation.title}
                </h1>
                <p class="mt-1 text-xs font-bold text-neutral-600">
                    Terakhir diperbarui: {formatDate(calculation.updated_at)} • Mode: {calculation.split_mode === 'itemized' ? 'Per Item' : 'Bagi Rata'}
                </p>
            </div>

            <!-- Header Action Buttons -->
            <div class="flex flex-wrap items-center gap-2.5">
                <Button
                    type="button"
                    onclick={() => (isShareModalOpen = true)}
                    variant="white"
                    size="md"
                >
                    <Share2 class="mr-1.5 size-5 text-black" strokeWidth={2.5} aria-hidden="true" />
                    Bagikan
                </Button>

                <Button
                    type="button"
                    onclick={handleDuplicate}
                    variant="yellow"
                    size="md"
                >
                    <Copy class="mr-1.5 size-5 text-black" strokeWidth={2.5} aria-hidden="true" />
                    Duplikat
                </Button>

                <Button
                    href={`/calculations/${calculation.id}/edit`}
                    variant="blue"
                    size="md"
                >
                    <Pencil class="mr-1.5 size-5" strokeWidth={2.5} aria-hidden="true" />
                    Ubah Data
                </Button>
            </div>
        </div>

        <!-- Content Grid: Summary & Breakdown -->
        <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-12">
            <!-- Left: Breakdown Summary Cards -->
            <div class="lg:col-span-7 flex flex-col gap-6">
                <!-- Per-person breakdown cards -->
                <div class="rounded-xl border-2 border-black bg-white p-6 shadow-[4px_4px_0px_0px_#000]">
                    <h2 class="text-base font-black uppercase tracking-tight text-black mb-4">
                        Rincian Pembagian Tiap Partisipan
                    </h2>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        {#each participantList as p (p.id)}
                            <div class="flex flex-col justify-between rounded-xl border-2 border-black bg-white p-4 shadow-[3px_3px_0px_0px_#000]">
                                <div class="flex items-center justify-between border-b-2 border-black pb-2">
                                    <div class="flex items-center gap-2">
                                        <div class="flex size-8 items-center justify-center rounded border border-black bg-blue-600 text-xs font-black text-white">
                                            {p.name.charAt(0).toUpperCase()}
                                        </div>
                                        <span class="font-black text-sm text-black">{p.name}</span>
                                    </div>
                                    <span class="text-base font-black text-blue-600">
                                        {formatRupiah(p.final_total_minor)}
                                    </span>
                                </div>

                                <div class="mt-3 flex flex-col gap-1.5 text-xs font-bold text-neutral-600">
                                    <div class="flex justify-between">
                                        <span>Subtotal Pesanan</span>
                                        <span class="text-black">{formatRupiah(p.subtotal_minor)}</span>
                                    </div>

                                    {#if p.discount_minor > 0}
                                        <div class="flex justify-between text-black">
                                            <span class="font-black text-red-600">Diskon</span>
                                            <span class="font-black text-red-600">- {formatRupiah(p.discount_minor)}</span>
                                        </div>
                                    {/if}

                                    {#if p.tax_minor > 0}
                                        <div class="flex justify-between">
                                            <span>Pajak (PB1)</span>
                                            <span class="text-black">+ {formatRupiah(p.tax_minor)}</span>
                                        </div>
                                    {/if}

                                    {#if p.service_charge_minor > 0}
                                        <div class="flex justify-between">
                                            <span>Layanan</span>
                                            <span class="text-black">+ {formatRupiah(p.service_charge_minor)}</span>
                                        </div>
                                    {/if}

                                    {#if p.tip_minor > 0}
                                        <div class="flex justify-between">
                                            <span>Tip</span>
                                            <span class="text-black">+ {formatRupiah(p.tip_minor)}</span>
                                        </div>
                                    {/if}
                                </div>
                            </div>
                        {/each}
                    </div>
                </div>

                <!-- Itemized Menu List if available -->
                {#if calculation.split_mode === 'itemized' && calculation.items && calculation.items.length > 0}
                    <div class="rounded-xl border-2 border-black bg-white p-6 shadow-[4px_4px_0px_0px_#000]">
                        <h2 class="text-base font-black uppercase tracking-tight text-black mb-4">
                            Daftar Menu & Alokasi
                        </h2>

                        <div class="divide-y-2 divide-black">
                            {#each calculation.items as item}
                                <div class="py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                    <div>
                                        <span class="font-black text-sm text-black">{item.description}</span>
                                        <span class="text-xs font-bold text-neutral-600 ml-2">({item.quantity} × {formatRupiah(item.unit_price_minor)})</span>
                                        <div class="mt-1.5 flex flex-wrap gap-1.5">
                                            {#each item.participants as p}
                                                <Badge variant="yellow" size="sm">
                                                    {p.name}
                                                </Badge>
                                            {/each}
                                        </div>
                                    </div>
                                    <span class="font-black text-sm text-black self-end sm:self-center">
                                        {formatRupiah(item.line_subtotal_minor)}
                                    </span>
                                </div>
                            {/each}
                        </div>
                    </div>
                {/if}

                {#if calculation.notes}
                    <div class="rounded-xl border-2 border-black bg-white p-5 shadow-[4px_4px_0px_0px_#000]">
                        <h3 class="text-xs font-black uppercase tracking-wider text-black">Catatan</h3>
                        <p class="mt-1 text-sm font-bold text-neutral-700 whitespace-pre-line">{calculation.notes}</p>
                    </div>
                {/if}
            </div>

            <!-- Right: Bill Total Summary Card (Sunny Yellow) -->
            <div class="lg:col-span-5">
                <div class="sticky top-20 rounded-xl border-2 border-black bg-yellow-400 p-5 shadow-[6px_6px_0px_0px_#000] text-black">
                    <h3 class="text-xs font-black uppercase tracking-wider text-black pb-3 border-b-2 border-black">
                        Total Pembayaran
                    </h3>

                    <div class="mt-4 flex flex-col gap-2.5 text-sm font-bold">
                        <div class="flex justify-between text-neutral-800">
                            <span>Subtotal</span>
                            <span class="font-black text-black">{formatRupiah(breakdown.subtotal_minor)}</span>
                        </div>

                        {#if breakdown.discount_total_minor > 0}
                            <div class="flex justify-between text-black">
                                <span class="font-black">Total Diskon</span>
                                <span class="font-black text-red-600">- {formatRupiah(breakdown.discount_total_minor)}</span>
                            </div>
                        {/if}

                        {#if breakdown.tax_total_minor > 0}
                            <div class="flex justify-between text-neutral-800">
                                <span>Total Pajak</span>
                                <span class="font-black text-black">+ {formatRupiah(breakdown.tax_total_minor)}</span>
                            </div>
                        {/if}

                        {#if breakdown.service_charge_minor > 0}
                            <div class="flex justify-between text-neutral-800">
                                <span>Total Layanan</span>
                                <span class="font-black text-black">+ {formatRupiah(breakdown.service_charge_minor)}</span>
                            </div>
                        {/if}

                        {#if breakdown.tip_total_minor > 0}
                            <div class="flex justify-between text-neutral-800">
                                <span>Total Tip</span>
                                <span class="font-black text-black">+ {formatRupiah(breakdown.tip_total_minor)}</span>
                            </div>
                        {/if}

                        <div class="my-2 border-t-2 border-black"></div>

                        <div class="flex items-baseline justify-between">
                            <span class="text-base font-black uppercase text-black">Grand Total</span>
                            <span class="text-3xl font-black tracking-tight text-black">
                                {formatRupiah(breakdown.grand_total_minor)}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <Footer />

    <Toast message={successMessage} variant="success" />

    <!-- Share Modal -->
    <ExportModal
        bind:isOpen={isShareModalOpen}
        title={calculation.title}
        result={breakdown}
    />
</div>
