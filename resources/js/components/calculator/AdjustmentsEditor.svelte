<script lang="ts">
import Button from '@/components/ui/Button.svelte';
import CurrencyInput from '@/components/ui/CurrencyInput.svelte';
import type { AdjustmentInput } from '@/lib/calculator-preview';

let {
    adjustments = $bindable([]),
}: {
    adjustments: AdjustmentInput[];
} = $props();

const defaultPresets = [
    { type: 'discount' as const, label: 'Diskon / Voucher', value_type: 'percentage' as const, rate: 0, minor: 0 },
    { type: 'tax' as const, label: 'Pajak (PB1 10%)', value_type: 'percentage' as const, rate: 10, minor: 0 },
    { type: 'service_charge' as const, label: 'Layanan (Service 5%)', value_type: 'percentage' as const, rate: 5, minor: 0 },
    { type: 'tip' as const, label: 'Uang Tip Sukarela', value_type: 'fixed' as const, rate: 0, minor: 0 },
];

function addPreset(preset: typeof defaultPresets[0]) {
    const id = `adj_${Date.now()}_${Math.random().toString(36).slice(2, 6)}`;
    adjustments = [
        ...adjustments,
        {
            id,
            type: preset.type,
            label: preset.label,
            value_type: preset.value_type,
            percentage_rate: preset.rate > 0 ? preset.rate : null,
            value_minor: preset.minor > 0 ? preset.minor : null,
            base_type: 'after_discount',
            is_enabled: true,
            position: adjustments.length,
        },
    ];
}

function removeAdjustment(index: number) {
    adjustments = adjustments.filter((_, i) => i !== index);
}
</script>

<div class="rounded-xl border-2 border-black bg-white p-5 shadow-[4px_4px_0px_0px_#000]">
    <div class="flex items-center justify-between border-b-2 border-black pb-3">
        <div>
            <h3 class="text-base font-black tracking-tight text-black">Biaya Tambahan & Diskon</h3>
            <p class="text-xs font-medium text-neutral-600">Pajak restoran PB1, service charge, tip, atau voucher promo</p>
        </div>
    </div>

    <!-- Quick Add Presets -->
    <div class="mt-4 flex flex-wrap gap-2.5">
        {#each defaultPresets as preset}
            {@const isAlreadyAdded = adjustments.some((a) => a.type === preset.type)}
            {#if !isAlreadyAdded}
                <Button
                    type="button"
                    onclick={() => addPreset(preset)}
                    variant="white"
                    size="md"
                    class="hover:bg-yellow-300"
                >
                    <svg class="size-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    + {preset.label}
                </Button>
            {/if}
        {/each}
    </div>

    <!-- Adjustment List -->
    {#if adjustments.length > 0}
        <div class="mt-4 flex flex-col gap-3">
            {#each adjustments as adj, index (adj.id || index)}
                <div class="flex flex-col gap-3 rounded-xl border-2 border-black bg-white p-4 shadow-[3px_3px_0px_0px_#000] sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex min-w-0 items-start gap-3">
                        <input
                            type="checkbox"
                            bind:checked={adj.is_enabled}
                            class="size-5 rounded border-2 border-black text-blue-600 focus:ring-0 cursor-pointer"
                        />
                        <div class="min-w-0 flex-1">
                            {#if adj.type === 'tax'}
                                <label for={`adjustment-label-${index}`} class="mb-1 block text-[10px] font-black uppercase tracking-wider text-black">
                                    Nama Pajak
                                </label>
                            {/if}
                            <input
                                id={adj.type === 'tax' ? `adjustment-label-${index}` : undefined}
                                type="text"
                                bind:value={adj.label}
                                placeholder={adj.type === 'tax' ? 'Contoh: PPN Restoran' : undefined}
                                aria-label={adj.type === 'tax' ? 'Nama Pajak' : 'Nama Biaya atau Diskon'}
                                class="w-full min-w-0 rounded border-2 border-black bg-yellow-100 px-2.5 py-1 text-sm font-black text-black shadow-[1px_1px_0px_0px_#000] placeholder:font-normal placeholder:text-neutral-500 focus:bg-white focus:outline-none"
                            />
                            <span class="text-[10px] font-black uppercase tracking-wider text-neutral-600 mt-1">
                                {adj.type === 'discount' ? 'Potongan Harga' : adj.type === 'tax' ? 'Pajak' : adj.type === 'service_charge' ? 'Layanan' : 'Tip'}
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2.5 self-end sm:self-center">
                        <!-- Value type toggle: Fixed vs % -->
                        <div class="inline-flex rounded-lg border-2 border-black bg-neutral-100 p-1 shadow-[2px_2px_0px_0px_#000]">
                            <button
                                type="button"
                                onclick={() => { adj.value_type = 'percentage'; adj.percentage_rate = 10; adj.value_minor = null; }}
                                class="rounded px-3 py-1.5 text-xs font-black transition cursor-pointer select-none active:translate-x-0.5 active:translate-y-0.5 {adj.value_type === 'percentage' ? 'border border-black bg-yellow-400 text-black shadow-[1px_1px_0px_0px_#000]' : 'text-neutral-600 hover:text-black'}"
                            >
                                %
                            </button>
                            <button
                                type="button"
                                onclick={() => { adj.value_type = 'fixed'; adj.value_minor = 10000; adj.percentage_rate = null; }}
                                class="rounded px-3 py-1.5 text-xs font-black transition cursor-pointer select-none active:translate-x-0.5 active:translate-y-0.5 {adj.value_type === 'fixed' ? 'border border-black bg-yellow-400 text-black shadow-[1px_1px_0px_0px_#000]' : 'text-neutral-600 hover:text-black'}"
                            >
                                Rp
                            </button>
                        </div>

                        <!-- Amount / Rate Input -->
                        {#if adj.value_type === 'percentage'}
                            <div class="relative w-28">
                                <input
                                    type="number"
                                    min="0"
                                    max="100"
                                    step="0.5"
                                    bind:value={adj.percentage_rate}
                                    class="w-full rounded-lg border-2 border-black bg-white pl-3 pr-7 py-2 text-right text-sm font-black text-black shadow-[2px_2px_0px_0px_#000] focus:border-blue-600 focus:ring-2 focus:ring-blue-600 focus:shadow-[2px_2px_0px_0px_#000] focus:outline-none"
                                />
                                <span class="absolute right-2.5 top-1/2 -translate-y-1/2 text-xs font-black text-neutral-600">%</span>
                            </div>
                        {:else}
                            <div class="w-36">
                                <CurrencyInput
                                    bind:value={adj.value_minor}
                                    placeholder="0"
                                    prefix="Rp"
                                    inputClass="py-2 text-right text-sm"
                                />
                            </div>
                        {/if}

                        <button
                            type="button"
                            onclick={() => removeAdjustment(index)}
                            aria-label="Hapus penyesuaian"
                            class="neo-btn rounded-lg border-2 border-black bg-red-100 p-2.5 text-black shadow-[2px_2px_0px_0px_#000] hover:bg-red-500 hover:text-white cursor-pointer transition active:translate-x-0.5 active:translate-y-0.5"
                        >
                            <svg class="size-4 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            {/each}
        </div>
    {/if}
</div>
