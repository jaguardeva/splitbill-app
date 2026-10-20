<script lang="ts">
import Button from '@/components/ui/Button.svelte';
import Badge from '@/components/ui/Badge.svelte';
import type { ParticipantInput } from '@/lib/calculator-preview';

let {
    participants = $bindable([]),
    onDeleteWarning,
}: {
    participants: ParticipantInput[];
    onDeleteWarning?: (participant: ParticipantInput) => void;
} = $props();

let newName = $state('');
let editingId = $state<string | null>(null);
let editName = $state('');

function addParticipant() {
    const trimmed = newName.trim();
    if (!trimmed) return;

    const id = `p_${Date.now()}_${Math.random().toString(36).slice(2, 7)}`;
    participants = [
        ...participants,
        {
            id,
            name: trimmed,
            position: participants.length,
        },
    ];
    newName = '';
}

function handleKeyDown(e: KeyboardEvent) {
    if (e.key === 'Enter') {
        e.preventDefault();
        addParticipant();
    }
}

function startEdit(p: ParticipantInput) {
    editingId = p.id;
    editName = p.name;
}

function saveEdit() {
    if (!editingId) return;
    const trimmed = editName.trim();
    if (trimmed) {
        participants = participants.map((p) =>
            p.id === editingId ? { ...p, name: trimmed } : p
        );
    }
    editingId = null;
}

function removeParticipant(p: ParticipantInput) {
    if (onDeleteWarning) {
        onDeleteWarning(p);
    } else {
        participants = participants.filter((item) => item.id !== p.id);
    }
}
</script>

<div class="rounded-xl border-2 border-black bg-white p-5 shadow-[4px_4px_0px_0px_#000]">
    <div class="flex flex-col items-start gap-2 border-b-2 border-black pb-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h3 class="text-base font-black tracking-tight text-black">Daftar Partisipan</h3>
            <p class="text-xs font-medium text-neutral-600">Siapa saja yang ikut berbagi tagihan ini?</p>
        </div>
        <Badge variant="yellow" size="md">
            {participants.length} Orang
        </Badge>
    </div>

    <!-- Input Tambah Partisipan -->
    <div class="mt-4 flex flex-col gap-2.5 sm:flex-row">
        <input
            type="text"
            bind:value={newName}
            onkeydown={handleKeyDown}
            placeholder="Ketik nama orang (mis. Budi, Siti)..."
            class="flex-1 rounded-lg border-2 border-black bg-white px-4 py-2.5 text-sm font-bold text-black shadow-[2px_2px_0px_0px_#000] placeholder:font-normal placeholder:text-neutral-500 focus:border-blue-600 focus:ring-2 focus:ring-blue-600 focus:shadow-[2px_2px_0px_0px_#000] focus:outline-none"
        />
        <Button
            type="button"
            onclick={addParticipant}
            variant="blue"
            size="md"
            class="w-full sm:w-auto"
        >
            <svg class="size-4 mr-1.5 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Tambah
        </Button>
    </div>

    <!-- Badges / List of participants -->
    {#if participants.length === 0}
        <div class="mt-4 rounded-lg border-2 border-dashed border-black bg-yellow-50 py-6 text-center text-xs font-bold text-black">
            Belum ada partisipan. Tambahkan minimal 1 orang di atas.
        </div>
    {:else}
        <div class="mt-4 flex flex-wrap gap-2.5">
            {#each participants as p, index (p.id)}
                <div class="flex items-center gap-2 rounded-lg border-2 border-black bg-yellow-300 px-3.5 py-1.5 shadow-[2px_2px_0px_0px_#000]">
                    <!-- Initial Avatar -->
                    <div class="flex size-7 items-center justify-center rounded border border-black bg-blue-600 text-xs font-black text-white select-none shadow-[1px_1px_0px_0px_#000]">
                        {p.name.charAt(0).toUpperCase()}
                    </div>

                    {#if editingId === p.id}
                        <input
                            type="text"
                            bind:value={editName}
                            onblur={saveEdit}
                            onkeydown={(e) => e.key === 'Enter' && saveEdit()}
                            class="h-7 w-28 rounded border-2 border-black bg-white px-1.5 text-xs font-bold text-black focus:outline-none"
                        />
                    {:else}
                        <button
                            type="button"
                            onclick={() => startEdit(p)}
                            title="Klik untuk mengubah nama"
                            class="text-xs font-black text-black hover:underline cursor-pointer"
                        >
                            {p.name}
                        </button>
                    {/if}

                    <button
                        type="button"
                        onclick={() => removeParticipant(p)}
                        title="Hapus partisipan"
                        aria-label={`Hapus ${p.name}`}
                        class="p-0.5 text-black transition hover:text-red-600 cursor-pointer"
                    >
                        <svg class="size-4 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            {/each}
        </div>
    {/if}
</div>
