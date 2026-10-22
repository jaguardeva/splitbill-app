<script lang="ts">
import { ClipboardList, Plus, Trash2 } from '@lucide/svelte';
import Button from '@/components/ui/Button.svelte';
import CurrencyInput from '@/components/ui/CurrencyInput.svelte';
import { formatRupiah } from '@/lib/formatters';
import type { BillItemInput, ParticipantInput } from '@/lib/calculator-preview';

let {
    items = $bindable([]),
    participants = [],
}: {
    items: BillItemInput[];
    participants: ParticipantInput[];
} = $props();

function addItem() {
    const id = `item_${Date.now()}_${Math.random().toString(36).slice(2, 6)}`;
    items = [
        ...items,
        {
            id,
            description: '',
            quantity: 1,
            unit_price_minor: 0,
            assigned_participant_ids: participants.map((p) => p.id), // default to all
            position: items.length,
        },
    ];
}

function removeItem(index: number) {
    items = items.filter((_, i) => i !== index);
}

function toggleParticipant(itemIndex: number, pId: string) {
    const item = items[itemIndex];
    const current = item.assigned_participant_ids || [];
    if (current.includes(pId)) {
        item.assigned_participant_ids = current.filter((id) => id !== pId);
    } else {
        item.assigned_participant_ids = [...current, pId];
    }
}

function toggleAllParticipants(itemIndex: number) {
    const item = items[itemIndex];
    if (item.assigned_participant_ids.length === participants.length) {
        item.assigned_participant_ids = [];
    } else {
        item.assigned_participant_ids = participants.map((p) => p.id);
    }
}
</script>

<div class="rounded-xl border-2 border-black bg-white p-5 shadow-[4px_4px_0px_0px_#000]">
    <div class="flex flex-col gap-3 border-b-2 border-black pb-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h3 class="text-base font-black tracking-tight text-black">Daftar Menu & Pesanan</h3>
            <p class="text-xs font-medium text-neutral-600">Masukkan pesanan dan tentukan siapa yang memakannya</p>
        </div>
    </div>

    {#if items.length === 0}
        <div class="mt-4 rounded-lg border-2 border-dashed border-black bg-yellow-50 py-10 text-center">
            <div class="mx-auto flex size-14 items-center justify-center rounded-xl border-2 border-black bg-yellow-400 text-black shadow-[3px_3px_0px_0px_#000]">
                <ClipboardList class="size-7" strokeWidth={2.5} aria-hidden="true" />
            </div>
            <p class="mt-3 text-base font-black text-black">Belum ada item pesanan</p>
            <p class="text-xs font-medium text-neutral-600 mt-1">Gunakan tombol "+ Tambah Item" di bawah untuk mulai mencatat struk.</p>
        </div>
    {:else}
        <div class="mt-4 flex flex-col gap-4">
            {#each items as item, index (item.id || index)}
                <div class="rounded-xl border-2 border-black bg-white p-4 shadow-[3px_3px_0px_0px_#000] transition">
                    <!-- Top row: Number Badge and Name Input -->
                    <div class="flex flex-col gap-2.5 sm:flex-row sm:items-center">
                        <span class="inline-flex size-8 shrink-0 items-center justify-center rounded-lg border-2 border-black bg-yellow-400 text-xs font-black text-black shadow-[1px_1px_0px_0px_#000]">
                            #{index + 1}
                        </span>
                        <div class="flex-1">
                            <label for={`desc-${index}`} class="sr-only">Nama Item</label>
                            <input
                                id={`desc-${index}`}
                                type="text"
                                bind:value={item.description}
                                placeholder="Nama makanan / minuman (mis: Nasi Goreng Spesial)..."
                                class="w-full rounded-lg border-2 border-black bg-white px-3.5 py-2.5 text-sm font-bold text-black placeholder:font-normal placeholder:text-neutral-500 shadow-[2px_2px_0px_0px_#000] focus:border-blue-600 focus:ring-2 focus:ring-blue-600 focus:shadow-[2px_2px_0px_0px_#000] focus:outline-none"
                            />
                        </div>
                    </div>

                    <!-- Middle row: Quantity, Price, and Subtotal (Responsive Grid) -->
                    <div class="mt-3 flex flex-col gap-2.5 sm:flex-row sm:items-center">
                        <!-- Qty -->
                        <div class="w-full sm:w-28">
                            <label for={`qty-${index}`} class="block text-[10px] font-black uppercase tracking-wider text-black mb-1">
                                Jumlah (Qty)
                            </label>
                            <input
                                id={`qty-${index}`}
                                type="number"
                                min="0.1"
                                step="any"
                                bind:value={item.quantity}
                                placeholder="1"
                                class="w-full rounded-lg border-2 border-black bg-white px-3 py-2 text-center text-sm font-bold text-black shadow-[2px_2px_0px_0px_#000] focus:border-blue-600 focus:ring-2 focus:ring-blue-600 focus:shadow-[2px_2px_0px_0px_#000] focus:outline-none"
                            />
                        </div>

                        <!-- Price -->
                        <div class="flex-1">
                            <CurrencyInput
                                id={`price-${index}`}
                                label="Harga Satuan"
                                bind:value={item.unit_price_minor}
                                placeholder="0"
                                prefix="Rp"
                                inputClass="py-2 text-right text-sm"
                            />
                        </div>

                        <!-- Subtotal Badge -->
                        <div class="w-full sm:w-44">
                            <span class="block text-[10px] font-black uppercase tracking-wider text-black mb-1">Total Item</span>
                            <div class="flex h-[42px] items-center justify-end rounded-lg border-2 border-black bg-yellow-300 px-3 text-sm font-black text-black shadow-[2px_2px_0px_0px_#000]">
                                {formatRupiah(Math.round((item.quantity || 0) * (item.unit_price_minor || 0)))}
                            </div>
                        </div>
                    </div>

                    <!-- Participant Assignment Selector -->
                    <div class="mt-3.5 border-t-2 border-black pt-3">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span class="text-xs font-black uppercase tracking-wider text-black">
                                Dibagikan ke:
                            </span>

                            {#if participants.length > 1}
                                <button
                                    type="button"
                                    onclick={() => toggleAllParticipants(index)}
                                    class="rounded border border-black bg-white px-2.5 py-1 text-xs font-black text-blue-600 shadow-[1px_1px_0px_0px_#000] hover:bg-yellow-300 hover:text-black cursor-pointer transition"
                                >
                                    {item.assigned_participant_ids.length === participants.length ? 'Batal Semua' : 'Pilih Semua'}
                                </button>
                            {/if}
                        </div>

                        {#if participants.length === 0}
                            <p class="mt-1.5 text-xs font-bold text-red-600">
                                Tambahkan partisipan terlebih dahulu untuk menugaskan item ini.
                            </p>
                        {:else}
                            <div class="mt-2.5 flex flex-wrap gap-2">
                                {#each participants as p (p.id)}
                                    {@const isAssigned = item.assigned_participant_ids.includes(p.id)}
                                    <button
                                        type="button"
                                        onclick={() => toggleParticipant(index, p.id)}
                                        class="inline-flex items-center gap-1.5 rounded-lg border-2 border-black px-3 py-1.5 text-xs font-black transition cursor-pointer select-none active:translate-x-0.5 active:translate-y-0.5 {isAssigned
                                            ? 'bg-yellow-400 text-black shadow-[2px_2px_0px_0px_#000]'
                                            : 'bg-white text-neutral-600 hover:bg-neutral-100 shadow-[1px_1px_0px_0px_#000]'}"
                                    >
                                        <span class="size-2 rounded-full border border-black {isAssigned ? 'bg-blue-600' : 'bg-neutral-300'}"></span>
                                        {p.name}
                                    </button>
                                {/each}
                            </div>
                        {/if}
                    </div>

                    <!-- Bottom row: Delete action -->
                    <div class="mt-4 flex justify-end border-t-2 border-black pt-3">
                        <Button
                            type="button"
                            onclick={() => removeItem(index)}
                            variant="red"
                            size="sm"
                            aria-label={`Hapus ${item.description || `Item #${index + 1}`}`}
                        >
                            <Trash2 class="mr-1.5 size-4" strokeWidth={2.5} aria-hidden="true" />
                            Hapus Item
                        </Button>
                    </div>
                </div>
            {/each}
        </div>
    {/if}

    <div class="mt-4 border-t-2 border-black pt-4">
        <Button
            type="button"
            onclick={addItem}
            variant="blue"
            size="md"
            class="w-full sm:ml-auto sm:w-auto"
        >
            <Plus class="mr-1.5 size-4" strokeWidth={2.5} aria-hidden="true" />
            Tambah Item
        </Button>
    </div>
</div>
