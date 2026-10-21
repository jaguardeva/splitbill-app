<script lang="ts">
let {
    value = $bindable(''),
    placeholder = 'Cari...',
    class: customClass = '',
    oninput,
    onclear,
}: {
    value?: string;
    placeholder?: string;
    class?: string;
    oninput?: (e: Event) => void;
    onclear?: () => void;
} = $props();

function handleClear() {
    value = '';
    onclear?.();
}
</script>

<div class="relative w-full max-w-sm {customClass}">
    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
            />
        </svg>
    </div>

    <input
        type="text"
        bind:value
        {placeholder}
        {oninput}
        class="w-full rounded-lg border border-slate-300 bg-white py-2 pl-9 pr-8 text-sm text-slate-800 placeholder-slate-400 shadow-xs transition-colors focus:border-blue-500 focus:outline-hidden focus:ring-2 focus:ring-blue-500/20"
    />

    {#if value && value.trim() !== ''}
        <button
            type="button"
            onclick={handleClear}
            aria-label="Bersihkan pencarian"
            class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-slate-400 hover:text-slate-600 cursor-pointer"
        >
            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M6 18L18 6M6 6l12 12"
                />
            </svg>
        </button>
    {/if}
</div>
