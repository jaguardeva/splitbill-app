<script lang="ts">
import { formatNumber } from '@/lib/formatters';

let {
    id,
    value = $bindable(0),
    placeholder = '0',
    label,
    error,
    prefix = 'Rp',
    suffix,
    required = false,
    disabled = false,
    class: customClass = '',
    inputClass = '',
    ...restProps
}: {
    id?: string;
    value?: number | null;
    placeholder?: string;
    label?: string;
    error?: string;
    prefix?: string;
    suffix?: string;
    required?: boolean;
    disabled?: boolean;
    class?: string;
    inputClass?: string;
    [key: string]: any;
} = $props();

// Display string with dot thousand separator
let displayValue = $state('');

// Sync from external numeric value to formatted string
$effect(() => {
    const numeric = typeof value === 'number' && !isNaN(value) ? value : 0;
    if (numeric === 0 && !displayValue) {
        displayValue = '';
    } else {
        const cleanCurrent = displayValue.replace(/\D/g, '');
        if (cleanCurrent !== String(numeric)) {
            displayValue = numeric > 0 ? formatNumber(numeric) : '';
        }
    }
});

function handleInput(e: Event) {
    const target = e.target as HTMLInputElement;
    // Strip everything except digits
    const rawDigits = target.value.replace(/\D/g, '');

    if (!rawDigits) {
        displayValue = '';
        value = 0;
        return;
    }

    const numeric = parseInt(rawDigits, 10) || 0;
    value = numeric;
    displayValue = formatNumber(numeric);
}

function handleKeyDown(e: KeyboardEvent) {
    // Allow navigation, control keys, backspace, delete, tab, arrows
    const allowed = [
        'Backspace',
        'Delete',
        'Tab',
        'Escape',
        'Enter',
        'ArrowLeft',
        'ArrowRight',
        'ArrowUp',
        'ArrowDown',
        'Home',
        'End',
    ];

    if (allowed.includes(e.key) || e.ctrlKey || e.metaKey) {
        return;
    }

    // Block non-digits
    if (!/^\d$/.test(e.key)) {
        e.preventDefault();
    }
}
</script>

<div class="w-full {customClass}">
    {#if label}
        <label for={id} class="block text-xs font-black uppercase tracking-wider text-black mb-1.5">
            {label}
            {#if required}<span class="text-red-600">*</span>{/if}
        </label>
    {/if}

    <div class="relative w-full">
        {#if prefix}
            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-sm font-black text-neutral-800 select-none pointer-events-none">
                {prefix}
            </span>
        {/if}

        <input
            {id}
            type="text"
            inputmode="numeric"
            value={displayValue}
            oninput={handleInput}
            onkeydown={handleKeyDown}
            {placeholder}
            {required}
            {disabled}
            class="w-full rounded-lg border-2 border-black bg-white py-2.5 text-base font-black text-black shadow-[2px_2px_0px_0px_#000] placeholder:font-normal placeholder:text-neutral-500 focus:border-blue-600 focus:ring-2 focus:ring-blue-600 focus:shadow-[2px_2px_0px_0px_#000] focus:outline-none disabled:opacity-50 {prefix ? 'pl-11' : 'pl-3.5'} {suffix ? 'pr-10' : 'pr-3.5'} {error ? 'border-red-600 shadow-[2px_2px_0px_0px_#dc2626]' : ''} {inputClass}"
            {...restProps}
        />

        {#if suffix}
            <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-xs font-black text-neutral-600 select-none pointer-events-none">
                {suffix}
            </span>
        {/if}
    </div>

    {#if error}
        <p class="mt-1.5 text-xs font-black text-red-600">{error}</p>
    {/if}
</div>
