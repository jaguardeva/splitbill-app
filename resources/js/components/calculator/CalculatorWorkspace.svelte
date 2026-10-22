<script lang="ts">
import { onMount } from 'svelte';
import { Pencil } from '@lucide/svelte';
import { page, router } from '@inertiajs/svelte';
import { calculatePreview, type AdjustmentInput, type BillItemInput, type ParticipantInput } from '@/lib/calculator-preview';
import { formatRupiah } from '@/lib/formatters';
import Button from '@/components/ui/Button.svelte';
import Alert from '@/components/ui/Alert.svelte';
import CurrencyInput from '@/components/ui/CurrencyInput.svelte';
import ParticipantList from './ParticipantList.svelte';
import BillItemEditor from './BillItemEditor.svelte';
import AdjustmentsEditor from './AdjustmentsEditor.svelte';
import CalculationSummary from './CalculationSummary.svelte';
import ExportModal from './ExportModal.svelte';

let {
    initialData = null,
    isEdit = false,
}: {
    initialData?: {
        id?: string;
        title?: string;
        split_mode?: 'equal' | 'itemized';
        status?: string;
        notes?: string | null;
        subtotal_minor?: number;
        participants?: ParticipantInput[];
        items?: BillItemInput[];
        adjustments?: AdjustmentInput[];
    } | null;
    isEdit?: boolean;
} = $props();

const user = $derived((page.props as any).auth?.user);

let title = $state((() => initialData?.title || 'Perhitungan Makan Bersama')());
let splitMode = $state<'equal' | 'itemized'>((() => initialData?.split_mode || 'itemized')());
let equalSubtotal = $state<number>((() => initialData?.subtotal_minor || 100000)());
let notes = $state((() => initialData?.notes || '')());

let participants = $state<ParticipantInput[]>(
    (() =>
        initialData?.participants && initialData.participants.length > 0
            ? initialData.participants
            : [
                  { id: 'p1', name: 'Saya', position: 0 },
                  { id: 'p2', name: 'Teman 1', position: 1 },
              ])()
);

let items = $state<BillItemInput[]>(
    (() =>
        initialData?.items && initialData.items.length > 0
            ? initialData.items
            : [
                  {
                      id: 'item_1',
                      description: 'Makanan 1',
                      quantity: 1,
                      unit_price_minor: 35000,
                      assigned_participant_ids: ['p1', 'p2'],
                      position: 0,
                  },
              ])()
);

let adjustments = $state<AdjustmentInput[]>(
    (() =>
        initialData?.adjustments || [
            {
                id: 'adj_tax',
                type: 'tax',
                label: 'Pajak (PB1 10%)',
                value_type: 'percentage',
                percentage_rate: 10,
                value_minor: null,
                base_type: 'after_discount',
                is_enabled: true,
                position: 0,
            },
        ])()
);

let isShareModalOpen = $state(false);
let isSaving = $state(false);
let errorMessage = $state<string | null>(null);

// Instant reactive calculation
const calculationResult = $derived(
    calculatePreview({
        split_mode: splitMode,
        participants,
        items: splitMode === 'itemized' ? items : [],
        adjustments,
        equal_subtotal_minor: equalSubtotal,
    })
);

function setSplitMode(mode: 'equal' | 'itemized') {
    if (splitMode === mode) return;
    splitMode = mode;
    if (mode === 'equal') {
        const currentItemsSum = items.reduce(
            (sum, it) => sum + Math.round((Number(it.quantity) || 0) * (Number(it.unit_price_minor) || 0)),
            0
        );
        if (currentItemsSum > 0) {
            equalSubtotal = currentItemsSum;
        }
    }
}

onMount(() => {
    // If not in edit mode, check if there is an unsaved guest draft to resume
    if (!isEdit) {
        try {
            const savedDraft = sessionStorage.getItem('splitbill_draft');
            if (savedDraft) {
                const parsed = JSON.parse(savedDraft);
                if (parsed && parsed.participants) {
                    title = parsed.title || title;
                    splitMode = parsed.split_mode || splitMode;
                    equalSubtotal = parsed.equal_subtotal_minor || parsed.subtotal_minor || equalSubtotal;
                    notes = parsed.notes || notes;
                    participants = parsed.participants;
                    items = parsed.items || items;
                    adjustments = parsed.adjustments || adjustments;
                }
            }
        } catch {
            // Ignore corrupted session draft
        }
    }
});

function scrollToSummary() {
    const summaryEl = document.getElementById('summary-section');
    if (summaryEl) {
        summaryEl.scrollIntoView({ behavior: 'smooth' });
    }
}

function handleSave() {
    errorMessage = null;

    if (!title.trim()) {
        errorMessage = 'Mohon masukkan judul perhitungan terlebih dahulu.';
        return;
    }

    if (participants.length === 0) {
        errorMessage = 'Harus ada minimal 1 partisipan.';
        return;
    }

    if (splitMode === 'itemized' && items.length === 0) {
        errorMessage = 'Tambahkan minimal 1 item pesanan pada mode itemized.';
        return;
    }

    const payload = {
        title: title.trim(),
        split_mode: splitMode,
        subtotal_minor: splitMode === 'equal' ? Number(equalSubtotal) : calculationResult.subtotal_minor,
        equal_subtotal_minor: splitMode === 'equal' ? Number(equalSubtotal) : null,
        notes: notes ? notes.trim() : null,
        participants: participants.map((p, idx) => ({
            id: p.id,
            name: p.name,
            position: idx,
        })),
        items:
            splitMode === 'itemized'
                ? items.map((it, idx) => ({
                      description: it.description || `Item #${idx + 1}`,
                      quantity: Number(it.quantity) || 1,
                      unit_price_minor: Number(it.unit_price_minor) || 0,
                      position: idx,
                      assigned_participant_ids: it.assigned_participant_ids || [],
                      assignments: (it.assigned_participant_ids || []).map((pId) => ({
                          participant_id: pId,
                          share_units: 1,
                      })),
                  }))
                : [],
        adjustments: adjustments.map((adj, idx) => ({
            type: adj.type,
            label: adj.label,
            value_type: adj.value_type,
            percentage_rate: adj.value_type === 'percentage' ? Number(adj.percentage_rate) : null,
            value_minor: adj.value_type === 'fixed' ? Number(adj.value_minor) : null,
            base_type: adj.base_type || 'after_discount',
            is_enabled: Boolean(adj.is_enabled),
            position: idx,
        })),
    };

    // If user is guest, persist draft in sessionStorage and redirect to register
    if (!user) {
        sessionStorage.setItem('splitbill_draft', JSON.stringify(payload));
        router.visit('/register?has_draft=1');
        return;
    }

    isSaving = true;
    if (isEdit && initialData?.id) {
        router.put(`/calculations/${initialData.id}`, payload as any, {
            onFinish: () => (isSaving = false),
            onError: (errors) => {
                errorMessage = Object.values(errors)[0] as string;
            },
        });
    } else {
        router.post('/calculations', payload as any, {
            onFinish: () => {
                isSaving = false;
                sessionStorage.removeItem('splitbill_draft');
            },
            onError: (errors) => {
                errorMessage = Object.values(errors)[0] as string;
            },
        });
    }
}
</script>

<div class="flex flex-col gap-6 pb-20 lg:pb-0">
    <!-- Calculation Title & Mode Bar -->
    <div class="rounded-xl border-2 border-black bg-white p-5 shadow-[4px_4px_0px_0px_#000]">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex-1">
                <label for="bill-title" class="flex items-center gap-1.5 text-xs font-black uppercase tracking-wider text-black">
                    <Pencil class="size-4 text-blue-600" strokeWidth={2.5} aria-hidden="true" />
                    Judul Tagihan / Acara
                </label>
                <input
                    id="bill-title"
                    type="text"
                    bind:value={title}
                    placeholder="Contoh: Makan Siang di Warung Padang"
                    class="mt-2 w-full rounded-lg border-2 border-black bg-white px-4 py-2.5 text-base font-bold text-black shadow-[2px_2px_0px_0px_#000] placeholder:font-normal placeholder:text-neutral-500 focus:border-blue-600 focus:ring-2 focus:ring-blue-600 focus:shadow-[2px_2px_0px_0px_#000] focus:outline-none"
                />
            </div>

            <!-- Segmented Mode Tabs (Large & Ergonomic) -->
            <div>
                <span class="block text-xs font-black uppercase tracking-wider text-black mb-2">
                    Metode Pembagian
                </span>
                <div
                    role="tablist"
                    aria-label="Pilih Metode Pembagian"
                    class="grid w-full grid-cols-2 gap-1.5 rounded-xl border-2 border-black bg-neutral-100 p-1.5 shadow-[3px_3px_0px_0px_#000] sm:inline-grid sm:w-auto"
                >
                    <button
                        type="button"
                        role="tab"
                        aria-selected={splitMode === 'itemized'}
                        onclick={() => setSplitMode('itemized')}
                        class="flex min-w-0 items-center justify-center whitespace-nowrap rounded-lg px-2 py-2.5 text-xs font-black transition cursor-pointer select-none active:translate-x-0.5 active:translate-y-0.5 sm:px-4 sm:text-sm {splitMode === 'itemized'
                            ? 'border-2 border-black bg-yellow-400 text-black shadow-[2px_2px_0px_0px_#000]'
                            : 'border-2 border-transparent text-neutral-600 hover:text-black hover:bg-neutral-200/70'}"
                    >
                        Per Item (Rincian)
                    </button>
                    <button
                        type="button"
                        role="tab"
                        aria-selected={splitMode === 'equal'}
                        onclick={() => setSplitMode('equal')}
                        class="flex min-w-0 items-center justify-center whitespace-nowrap rounded-lg px-2 py-2.5 text-xs font-black transition cursor-pointer select-none active:translate-x-0.5 active:translate-y-0.5 sm:px-4 sm:text-sm {splitMode === 'equal'
                            ? 'border-2 border-black bg-yellow-400 text-black shadow-[2px_2px_0px_0px_#000]'
                            : 'border-2 border-transparent text-neutral-600 hover:text-black hover:bg-neutral-200/70'}"
                    >
                        Bagi Rata (Equal)
                    </button>
                </div>
            </div>
        </div>
    </div>

    {#if errorMessage}
        <Alert variant="error" message={errorMessage} />
    {/if}

    <!-- Main Workspace Grid: Left Inputs, Right Sticky Summary -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
        <!-- Left Column: Inputs (7 cols) -->
        <div class="flex flex-col gap-6 lg:col-span-7">
            <!-- 1. Participants -->
            <ParticipantList bind:participants />

            <!-- 2. Split Mode Details -->
            {#if splitMode === 'equal'}
                <div class="rounded-xl border-2 border-black bg-white p-5 shadow-[4px_4px_0px_0px_#000]">
                    <div class="border-b-2 border-black pb-3">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex size-6 items-center justify-center rounded border border-black bg-yellow-400 text-xs font-black">
                                =
                            </span>
                            <h3 class="text-base font-black tracking-tight text-black">Total Tagihan Bersih (Bagi Rata)</h3>
                        </div>
                        <p class="mt-1 text-xs font-medium text-neutral-600">
                            Masukkan nominal total struk sebelum biaya pajak, layanan, atau diskon. Tagihan akan dibagi sama rata ke seluruh partisipan.
                        </p>
                    </div>

                    <div class="mt-4">
                        <CurrencyInput
                            id="equal-subtotal"
                            label="Subtotal Bersih"
                            bind:value={equalSubtotal}
                            placeholder="0"
                            prefix="Rp"
                            inputClass="text-lg sm:text-xl"
                        />

                        {#if participants.length > 0}
                            <div class="mt-3 flex items-center gap-2 rounded-lg border border-black bg-yellow-100 px-3 py-2 text-xs font-bold text-black shadow-[1px_1px_0px_0px_#000]">
                                <span>💡</span>
                                <span>
                                    Estimasi dasar: <strong>{formatRupiah(Math.round(equalSubtotal / Math.max(1, participants.length)))}</strong> per orang ({participants.length} partisipan, sebelum biaya tambahan).
                                </span>
                            </div>
                        {/if}
                    </div>
                </div>
            {:else}
                <BillItemEditor bind:items {participants} />
            {/if}

            <!-- 3. Adjustments -->
            <AdjustmentsEditor bind:adjustments />

            <!-- 4. Optional Notes -->
            <div class="rounded-xl border-2 border-black bg-white p-5 shadow-[4px_4px_0px_0px_#000]">
                <h3 class="text-sm font-black uppercase tracking-wider text-black">Catatan Tambahan (Opsional)</h3>
                <textarea
                    rows="2"
                    bind:value={notes}
                    placeholder="Contoh: Ditraktir kopi oleh Budi, transfer ke BCA 12345678"
                    class="mt-2 w-full rounded-lg border-2 border-black bg-white p-3 text-sm font-bold text-black shadow-[2px_2px_0px_0px_#000] placeholder:font-normal placeholder:text-neutral-500 focus:border-blue-600 focus:ring-2 focus:ring-blue-600 focus:shadow-[2px_2px_0px_0px_#000] focus:outline-none"
                ></textarea>
            </div>
        </div>

        <!-- Right Column: Results Summary (5 cols) -->
        <div id="summary-section" class="lg:col-span-5">
            <div class="sticky top-20">
                <CalculationSummary
                    result={calculationResult}
                    {splitMode}
                    onSave={handleSave}
                    onShare={() => (isShareModalOpen = true)}
                    {isSaving}
                />
            </div>
        </div>
    </div>

    <!-- Export Modal -->
    <ExportModal
        bind:isOpen={isShareModalOpen}
        {title}
        result={calculationResult}
    />

    <!-- Mobile Quick Total Bottom Bar (Sticky) -->
    <div class="fixed bottom-0 inset-x-0 z-30 flex items-center justify-between border-t-[3px] border-black bg-yellow-400 p-3 shadow-[0_-4px_0px_0px_#000] lg:hidden">
        <div>
            <span class="block text-[10px] font-black uppercase tracking-wider text-black">Total Tagihan</span>
            <span class="text-lg font-black text-black">{formatRupiah(calculationResult.grand_total_minor)}</span>
        </div>
        <div class="flex items-center gap-2">
            <Button
                type="button"
                onclick={scrollToSummary}
                variant="white"
                size="md"
            >
                Rincian
            </Button>
            <Button
                type="button"
                onclick={handleSave}
                disabled={isSaving}
                variant="blue"
                size="md"
            >
                Simpan
            </Button>
        </div>
    </div>
</div>
