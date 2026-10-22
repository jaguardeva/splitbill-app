<script lang="ts">
import { fade, fly } from 'svelte/transition';
import { CircleAlert, CircleCheck, Info, X } from '@lucide/svelte';

let {
    message = null,
    variant = 'info',
    duration = 4000,
}: {
    message?: string | null;
    variant?: 'success' | 'error' | 'warning' | 'info';
    duration?: number;
} = $props();

let visible = $state(false);
let dismissTimer: ReturnType<typeof setTimeout> | undefined;

const variantClasses = {
    error: 'bg-red-400 text-black',
    success: 'bg-emerald-400 text-black',
    warning: 'bg-yellow-300 text-black',
    info: 'bg-blue-200 text-black',
};

function clearDismissTimer() {
    if (dismissTimer) {
        clearTimeout(dismissTimer);
        dismissTimer = undefined;
    }
}

function dismiss() {
    clearDismissTimer();
    visible = false;
}

$effect(() => {
    if (!message) {
        dismiss();
        return;
    }

    clearDismissTimer();
    visible = true;
    dismissTimer = setTimeout(() => {
        visible = false;
        dismissTimer = undefined;
    }, duration);

    return clearDismissTimer;
});
</script>

{#if visible && message}
    <div
        class="fixed right-4 bottom-4 z-[60] flex w-[calc(100%-2rem)] max-w-sm items-start gap-3 rounded-xl border-2 border-black p-4 text-sm font-bold shadow-[5px_5px_0px_0px_#000] sm:right-6 sm:bottom-6 {variantClasses[variant]}"
        role={variant === 'error' ? 'alert' : 'status'}
        aria-live={variant === 'error' ? 'assertive' : 'polite'}
        in:fly={{ y: 18, duration: 250 }}
        out:fade={{ duration: 180 }}
    >
        {#if variant === 'success'}
            <CircleCheck class="mt-0.5 size-5 shrink-0" strokeWidth={2.5} aria-hidden="true" />
        {:else if variant === 'error' || variant === 'warning'}
            <CircleAlert class="mt-0.5 size-5 shrink-0" strokeWidth={2.5} aria-hidden="true" />
        {:else}
            <Info class="mt-0.5 size-5 shrink-0 text-blue-800" strokeWidth={2.5} aria-hidden="true" />
        {/if}

        <p class="min-w-0 flex-1 leading-relaxed">{message}</p>

        <button
            type="button"
            onclick={dismiss}
            aria-label="Tutup notifikasi"
            class="neo-btn inline-flex size-7 shrink-0 items-center justify-center rounded border-2 border-black bg-white p-0 text-black shadow-[2px_2px_0px_0px_#000] transition-colors hover:bg-black hover:text-white active:translate-x-0.5 active:translate-y-0.5"
        >
            <X class="size-3.5" strokeWidth={2.5} aria-hidden="true" />
        </button>
    </div>
{/if}
