<script lang="ts">
import { formatRupiah } from '@/lib/formatters';
import type { CalculationPreviewResult } from '@/lib/calculator-preview';

import Button from '@/components/ui/Button.svelte';
import Badge from '@/components/ui/Badge.svelte';

let {
    result,
    splitMode = 'itemized',
    onSave,
    onShare,
    isSaving = false,
}: {
    result: CalculationPreviewResult;
    splitMode?: 'equal' | 'itemized';
    onSave?: () => void;
    onShare?: () => void;
    isSaving?: boolean;
} = $props();

const participantList = $derived(Object.values(result.participants || {}));
</script>

<div class="flex flex-col gap-6">
    <!-- Grand Total & Overall Breakdown Card (Neo-Brutalist Sunny Yellow) -->
    <div class="rounded-xl border-2 border-black bg-yellow-400 p-5 shadow-[6px_6px_0px_0px_#000] text-black">
        <div class="flex items-center justify-between border-b-2 border-black pb-3">
            <div class="flex items-center gap-2">
                <span class="rounded border border-black bg-black px-2.5 py-0.5 text-xs font-black uppercase tracking-wider text-yellow-400 select-none">
                    Ringkasan Tagihan
                </span>
                <span class="rounded border border-black bg-white px-2 py-0.5 text-[10px] font-black uppercase text-black shadow-[1px_1px_0px_0px_#000]">
                    {splitMode === 'equal' ? '⚖️ Bagi Rata' : '🍽️ Per Item'}
                </span>
            </div>
            <Badge variant="white" size="sm">
                {participantList.length} Orang
            </Badge>
        </div>

        <div class="mt-4 flex flex-col gap-2 text-sm font-bold">
            <div class="flex items-center justify-between text-neutral-800">
                <span>{splitMode === 'equal' ? 'Subtotal Bersih' : 'Subtotal Pesanan'}</span>
                <span class="font-black text-black">{formatRupiah(result.subtotal_minor)}</span>
            </div>

            {#if result.discount_total_minor > 0}
                <div class="flex items-center justify-between text-black">
                    <span class="inline-flex items-center gap-1">
                        <span class="rounded bg-black px-1.5 py-0.2 text-[10px] font-black text-yellow-300">DISKON</span>
                        Potongan
                    </span>
                    <span class="font-black">- {formatRupiah(result.discount_total_minor)}</span>
                </div>
            {/if}

            {#if result.tax_total_minor > 0}
                <div class="flex items-center justify-between text-neutral-800">
                    <span>Pajak Restoran</span>
                    <span class="font-black text-black">+ {formatRupiah(result.tax_total_minor)}</span>
                </div>
            {/if}

            {#if result.service_charge_minor > 0}
                <div class="flex items-center justify-between text-neutral-800">
                    <span>Biaya Layanan</span>
                    <span class="font-black text-black">+ {formatRupiah(result.service_charge_minor)}</span>
                </div>
            {/if}

            {#if result.tip_total_minor > 0}
                <div class="flex items-center justify-between text-neutral-800">
                    <span>Uang Tip</span>
                    <span class="font-black text-black">+ {formatRupiah(result.tip_total_minor)}</span>
                </div>
            {/if}

            <div class="my-2 border-t-2 border-black"></div>

            <div class="flex items-baseline justify-between">
                <span class="text-base font-black uppercase text-black">Total Akhir</span>
                <span class="text-3xl font-black tracking-tight text-black">
                    {formatRupiah(result.grand_total_minor)}
                </span>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-5 grid grid-cols-2 gap-3">
            {#if onShare}
                <Button
                    type="button"
                    onclick={onShare}
                    variant="white"
                    size="md"
                    class="uppercase tracking-wider"
                >
                    <svg class="mr-1.5 size-4 shrink-0 text-black stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 1 1 0-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 1 0 5.367-2.684 3 3 0 0 0-5.367 2.684zm0 9.316a3 3 0 1 0 5.368 2.684 3 3 0 0 0-5.368-2.684z" />
                    </svg>
                    Bagi Rincian
                </Button>
            {/if}

            {#if onSave}
                <Button
                    type="button"
                    disabled={isSaving}
                    onclick={onSave}
                    variant="blue"
                    size="md"
                    class="uppercase tracking-wider"
                >
                    <svg class="mr-1.5 size-4 shrink-0 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                    </svg>
                    {isSaving ? 'Menyimpan...' : 'Simpan Tagihan'}
                </Button>
            {/if}
        </div>
    </div>

    <!-- Per-Person Breakdown List -->
    <div class="flex flex-col gap-3">
        <h4 class="text-sm font-black uppercase tracking-wider text-black">
            Rincian Bayar Per Orang
        </h4>

        {#if participantList.length === 0}
            <div class="rounded-xl border-2 border-dashed border-black bg-yellow-50 py-6 text-center text-xs font-bold text-black">
                Belum ada rincian pembagian.
            </div>
        {:else}
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                {#each participantList as p (p.id)}
                    <div class="flex flex-col justify-between rounded-xl border-2 border-black bg-white p-4 shadow-[3px_3px_0px_0px_#000]">
                        <!-- Header -->
                        <div class="flex items-center justify-between border-b-2 border-black pb-2">
                            <div class="flex items-center gap-2">
                                <div class="flex size-7 items-center justify-center rounded border border-black bg-blue-600 text-xs font-black text-white">
                                    {p.name.charAt(0).toUpperCase()}
                                </div>
                                <span class="font-black text-black text-sm">{p.name}</span>
                            </div>
                            <span class="text-sm font-black text-blue-600">
                                {formatRupiah(p.final_total_minor)}
                            </span>
                        </div>

                        <!-- Breakdown details -->
                        <div class="mt-2.5 flex flex-col gap-1 text-[11px] font-bold text-neutral-600">
                            <div class="flex justify-between">
                                <span>Porsi Makanan</span>
                                <span class="text-black">{formatRupiah(p.subtotal_minor)}</span>
                            </div>

                            {#if p.discount_minor > 0}
                                <div class="flex justify-between text-black">
                                    <span class="font-black text-red-600">Porsi Diskon</span>
                                    <span class="font-black text-red-600">- {formatRupiah(p.discount_minor)}</span>
                                </div>
                            {/if}

                            {#if p.tax_minor > 0}
                                <div class="flex justify-between">
                                    <span>Porsi Pajak</span>
                                    <span class="text-black">+ {formatRupiah(p.tax_minor)}</span>
                                </div>
                            {/if}

                            {#if p.service_charge_minor > 0}
                                <div class="flex justify-between">
                                    <span>Porsi Layanan</span>
                                    <span class="text-black">+ {formatRupiah(p.service_charge_minor)}</span>
                                </div>
                            {/if}

                            {#if p.tip_minor > 0}
                                <div class="flex justify-between">
                                    <span>Porsi Tip</span>
                                    <span class="text-black">+ {formatRupiah(p.tip_minor)}</span>
                                </div>
                            {/if}
                        </div>
                    </div>
                {/each}
            </div>
        {/if}
    </div>
</div>
