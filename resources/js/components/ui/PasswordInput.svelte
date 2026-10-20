<script lang="ts">
let {
    id,
    value = $bindable(''),
    placeholder = 'Minimal 8 karakter',
    label = 'Kata Sandi',
    error,
    required = false,
    disabled = false,
    showStrengthMeter = false,
    class: customClass = '',
    ...restProps
}: {
    id?: string;
    value?: string;
    placeholder?: string;
    label?: string;
    error?: string;
    required?: boolean;
    disabled?: boolean;
    showStrengthMeter?: boolean;
    class?: string;
    [key: string]: any;
} = $props();

let showPassword = $state(false);

// Security Rules
const hasMinLength = $derived((value || '').length >= 8);
const hasNoSpaces = $derived((value || '').length > 0 && !/\s/.test(value || ''));
const hasMixedCase = $derived(/[a-z]/.test(value || '') && /[A-Z]/.test(value || ''));
const hasNumberOrSymbol = $derived(/\d/.test(value || '') || /[^a-zA-Z0-9\s]/.test(value || ''));

// Calculate strength score: 0 to 4
const score = $derived.by(() => {
    if (!value) return 0;
    let s = 0;
    if (hasMinLength) s += 1;
    if (hasNoSpaces) s += 1;
    if (hasMixedCase) s += 1;
    if (hasNumberOrSymbol) s += 1;
    return s;
});

const strengthInfo = $derived.by(() => {
    if (!value) return { text: '', color: 'bg-neutral-200', textColor: 'text-neutral-500' };
    switch (score) {
        case 1:
            return { text: 'Sangat Lemah', color: 'bg-red-500', textColor: 'text-red-600' };
        case 2:
            return { text: 'Cukup', color: 'bg-amber-400', textColor: 'text-amber-600' };
        case 3:
            return { text: 'Kuat', color: 'bg-blue-600', textColor: 'text-blue-600' };
        case 4:
            return { text: 'Sangat Kuat', color: 'bg-emerald-500', textColor: 'text-emerald-600' };
        default:
            return { text: 'Sangat Lemah', color: 'bg-red-500', textColor: 'text-red-600' };
    }
});
</script>

<div class="w-full {customClass}">
    {#if label}
        <label for={id} class="block text-xs font-black uppercase tracking-wider text-black mb-1.5">
            {label}
            {#if required}<span class="text-red-600">*</span>{/if}
        </label>
    {/if}

    <div class="relative w-full">
        <input
            {id}
            type={showPassword ? 'text' : 'password'}
            bind:value
            {placeholder}
            {required}
            {disabled}
            class="w-full rounded-lg border-2 border-black bg-white px-4 py-3 pr-12 text-sm sm:text-base font-bold text-black shadow-[2px_2px_0px_0px_#000] placeholder:font-normal placeholder:text-neutral-500 focus:border-blue-600 focus:ring-2 focus:ring-blue-600 focus:shadow-[2px_2px_0px_0px_#000] focus:outline-none disabled:opacity-50 {error ? 'border-red-600 shadow-[2px_2px_0px_0px_#dc2626]' : ''}"
            {...restProps}
        />

        <button
            type="button"
            onclick={() => (showPassword = !showPassword)}
            tabindex="-1"
            aria-label={showPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'}
            class="absolute right-3 top-1/2 -translate-y-1/2 rounded p-1.5 text-neutral-600 hover:text-black hover:bg-neutral-100 transition cursor-pointer select-none"
        >
            {#if showPassword}
                <!-- Eye Off Icon -->
                <svg class="size-5 stroke-[2.2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                </svg>
            {:else}
                <!-- Eye Icon -->
                <svg class="size-5 stroke-[2.2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            {/if}
        </button>
    </div>

    <!-- Password Strength Tracker (Segmented / Dashed) -->
    {#if showStrengthMeter && value}
        <div class="mt-2.5 rounded-lg border-2 border-black bg-neutral-50 p-2.5 shadow-[2px_2px_0px_0px_#000]">
            <!-- Segmented Progress Bar (4 dashes) -->
            <div class="flex items-center gap-1.5">
                {#each [1, 2, 3, 4] as step}
                    <div
                        class="h-2 flex-1 rounded border border-black transition-colors {step <= score ? strengthInfo.color : 'bg-neutral-200'}"
                    ></div>
                {/each}
            </div>

            <!-- Strength Label & Indicator -->
            <div class="mt-2 flex items-center justify-between text-xs font-black">
                <span class="text-neutral-700">Keamanan:</span>
                <span class="{strengthInfo.textColor} uppercase tracking-wider">{strengthInfo.text}</span>
            </div>

            <!-- Rules checklist -->
            <div class="mt-2 grid grid-cols-2 gap-1 pt-1.5 border-t border-neutral-300 text-[11px] font-bold">
                <div class="flex items-center gap-1 {hasMinLength ? 'text-emerald-700 font-black' : 'text-neutral-500'}">
                    <span>{hasMinLength ? '✓' : '•'}</span>
                    <span>Min. 8 Karakter</span>
                </div>
                <div class="flex items-center gap-1 {hasNoSpaces ? 'text-emerald-700 font-black' : 'text-neutral-500'}">
                    <span>{hasNoSpaces ? '✓' : '•'}</span>
                    <span>Tanpa Spasi</span>
                </div>
                <div class="flex items-center gap-1 {hasMixedCase ? 'text-emerald-700 font-black' : 'text-neutral-500'}">
                    <span>{hasMixedCase ? '✓' : '•'}</span>
                    <span>Huruf Besar & Kecil</span>
                </div>
                <div class="flex items-center gap-1 {hasNumberOrSymbol ? 'text-emerald-700 font-black' : 'text-neutral-500'}">
                    <span>{hasNumberOrSymbol ? '✓' : '•'}</span>
                    <span>Angka / Simbol</span>
                </div>
            </div>
        </div>
    {/if}

    {#if error}
        <p class="mt-1.5 text-xs font-black text-red-600">{error}</p>
    {/if}
</div>
