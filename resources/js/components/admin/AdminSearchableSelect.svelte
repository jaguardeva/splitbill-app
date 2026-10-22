<script lang="ts">
import { onMount } from 'svelte';
import { Check, ChevronDown, Search } from '@lucide/svelte';

interface OptionItem {
    value: string | number;
    label: string;
    description?: string;
}

let {
    value = $bindable(''),
    options = [],
    placeholder = 'Pilih opsi...',
    searchPlaceholder = 'Cari opsi...',
    disabled = false,
    class: customClass = '',
    align = 'left',
    onchange,
    id,
}: {
    value?: string | number | null;
    options: OptionItem[];
    placeholder?: string;
    searchPlaceholder?: string;
    disabled?: boolean;
    class?: string;
    align?: 'left' | 'right';
    onchange?: (val: any) => void;
    id?: string;
} = $props();

let open = $state(false);
let searchTerm = $state('');
let selectRoot = $state<HTMLDivElement | undefined>(undefined);
let searchInputRef = $state<HTMLInputElement | undefined>(undefined);

const selectedOption = $derived(
    options.find((opt) => String(opt.value) === String(value))
);

const filteredOptions = $derived(
    options.filter((opt) => {
        if (!searchTerm.trim()) return true;
        const term = searchTerm.toLowerCase();
        return (
            opt.label.toLowerCase().includes(term) ||
            (opt.description && opt.description.toLowerCase().includes(term))
        );
    })
);

function toggleDropdown() {
    if (disabled) return;
    open = !open;
    if (open) {
        searchTerm = '';
        setTimeout(() => {
            searchInputRef?.focus();
        }, 50);
    }
}

function selectOption(opt: OptionItem) {
    value = opt.value;
    open = false;
    searchTerm = '';
    onchange?.(opt.value);
}

function handleKeydown(e: KeyboardEvent) {
    if (e.key === 'Escape' && open) {
        open = false;
    }
}

onMount(() => {
    function handlePointerDown(e: PointerEvent) {
        if (selectRoot && !selectRoot.contains(e.target as Node)) {
            open = false;
        }
    }

    document.addEventListener('pointerdown', handlePointerDown);
    return () => {
        document.removeEventListener('pointerdown', handlePointerDown);
    };
});
</script>

<div
    role="presentation"
    bind:this={selectRoot}
    onkeydown={handleKeydown}
    class="relative inline-block text-left {customClass} {open ? 'z-50' : 'z-10'}"
>
    <!-- Trigger Button -->
    <button
        type="button"
        {id}
        aria-haspopup="listbox"
        aria-expanded={open}
        {disabled}
        onclick={toggleDropdown}
        class="flex w-full items-center justify-between gap-2.5 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 shadow-xs transition-colors hover:border-slate-400 focus:border-blue-500 focus:outline-hidden focus:ring-2 focus:ring-blue-500/20 disabled:cursor-not-allowed disabled:bg-slate-50 disabled:text-slate-400 cursor-pointer"
    >
        <span class="truncate font-medium {selectedOption ? 'text-slate-900' : 'text-slate-400'}">
            {selectedOption ? selectedOption.label : placeholder}
        </span>

        <ChevronDown class="size-4 shrink-0 text-slate-400 transition-transform duration-150 {open ? 'rotate-180 text-blue-600' : ''}" aria-hidden="true" />
    </button>

    <!-- Dropdown Popover with Search Field -->
    {#if open}
        <div
            role="listbox"
            tabindex="-1"
            class="absolute {align === 'right' ? 'right-0' : 'left-0'} top-[calc(100%+0.35rem)] z-50 w-full min-w-[13rem] rounded-lg border border-slate-200/90 bg-white p-1.5 shadow-xl ring-1 ring-black/5 focus:outline-hidden"
        >
            <!-- Search Input Box inside Dropdown -->
            <div class="relative mb-1 border-b border-slate-100 pb-1.5">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2 text-slate-400">
                    <Search class="size-3.5" aria-hidden="true" />
                </div>
                <input
                    bind:this={searchInputRef}
                    type="text"
                    bind:value={searchTerm}
                    placeholder={searchPlaceholder}
                    class="w-full rounded-md border border-slate-200 bg-slate-50/70 py-1.5 pl-7 pr-2 text-xs text-slate-800 placeholder-slate-400 focus:border-blue-500 focus:bg-white focus:outline-hidden focus:ring-1 focus:ring-blue-500"
                />
            </div>

            <!-- Options List with Max Height -->
            <div class="max-h-52 overflow-y-auto space-y-0.5">
                {#if filteredOptions.length === 0}
                    <div class="py-4 text-center text-xs text-slate-400">
                        Tidak ada opsi ditemukan
                    </div>
                {:else}
                    {#each filteredOptions as opt}
                        {@const isSelected = String(opt.value) === String(value)}
                        <button
                            type="button"
                            role="option"
                            aria-selected={isSelected}
                            onclick={() => selectOption(opt)}
                            class="flex w-full items-center justify-between rounded-md px-2.5 py-2 text-left text-xs transition-colors cursor-pointer {isSelected
                                ? 'bg-blue-50 font-semibold text-blue-700'
                                : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900'}"
                        >
                            <div class="min-w-0 pr-2">
                                <p class="truncate">{opt.label}</p>
                                {#if opt.description}
                                    <p class="truncate text-[10px] text-slate-400 font-normal">{opt.description}</p>
                                {/if}
                            </div>

                            {#if isSelected}
                                <Check class="size-4 shrink-0 text-blue-600" strokeWidth={2.5} aria-hidden="true" />
                            {/if}
                        </button>
                    {/each}
                {/if}
            </div>
        </div>
    {/if}
</div>
