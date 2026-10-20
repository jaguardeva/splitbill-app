<script lang="ts">
let {
    id,
    type = 'text',
    value = $bindable(''),
    placeholder = '',
    label,
    error,
    prefix,
    suffix,
    required = false,
    disabled = false,
    class: customClass = '',
    ...restProps
}: {
    id?: string;
    type?: string;
    value?: any;
    placeholder?: string;
    label?: string;
    error?: string;
    prefix?: string;
    suffix?: string;
    required?: boolean;
    disabled?: boolean;
    class?: string;
    [key: string]: any;
} = $props();
</script>

<div class="w-full">
    {#if label}
        <label for={id} class="block text-xs font-black uppercase tracking-wider text-black mb-1.5">
            {label}
            {#if required}<span class="text-red-600">*</span>{/if}
        </label>
    {/if}

    <div class="relative w-full">
        {#if prefix}
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-black text-neutral-600 select-none">
                {prefix}
            </span>
        {/if}

        <input
            {id}
            {type}
            bind:value
            {placeholder}
            {required}
            {disabled}
            class="w-full rounded-lg border-2 border-black bg-white px-4 py-3 text-sm sm:text-base font-bold text-black shadow-[2px_2px_0px_0px_#000] placeholder:font-normal placeholder:text-neutral-500 focus:border-blue-600 focus:ring-2 focus:ring-blue-600 focus:shadow-[2px_2px_0px_0px_#000] focus:outline-none disabled:opacity-50 {prefix ? 'pl-10' : ''} {suffix ? 'pr-10' : ''} {error ? 'border-red-600 shadow-[2px_2px_0px_0px_#dc2626]' : ''} {customClass}"
            {...restProps}
        />

        {#if suffix}
            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-black text-neutral-600 select-none">
                {suffix}
            </span>
        {/if}
    </div>

    {#if error}
        <p class="mt-1.5 text-xs font-black text-red-600">{error}</p>
    {/if}
</div>
