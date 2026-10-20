<script lang="ts">
import Button from '@/components/ui/Button.svelte';

let {
    isOpen = false,
    title = 'Konfirmasi Tindakan',
    message,
    confirmLabel = 'Konfirmasi',
    cancelLabel = 'Batal',
    processingLabel = 'Memproses...',
    processing = false,
    onConfirm,
    onCancel,
}: {
    isOpen?: boolean;
    title?: string;
    message: string;
    confirmLabel?: string;
    cancelLabel?: string;
    processingLabel?: string;
    processing?: boolean;
    onConfirm: () => void;
    onCancel: () => void;
} = $props();
</script>

{#if isOpen}
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4">
        <div
            role="dialog"
            aria-modal="true"
            aria-labelledby="confirm-modal-title"
            class="w-full max-w-md rounded-xl border-[3px] border-black bg-white p-5 text-black shadow-[8px_8px_0px_0px_#000] animate-in fade-in zoom-in-95 sm:p-6"
        >
            <div class="flex items-start justify-between gap-4 border-b-2 border-black pb-3">
                <div class="flex items-start gap-3">
                    <span class="flex size-9 shrink-0 items-center justify-center rounded-lg border-2 border-black bg-red-500 text-white shadow-[2px_2px_0px_0px_#000]">
                        <svg class="size-5 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86l-8.82 15a2 2 0 001.73 3h17.6a2 2 0 001.73-3l-8.82-15a2 2 0 00-3.46 0z" />
                        </svg>
                    </span>
                    <h2 id="confirm-modal-title" class="pt-1 text-lg font-black tracking-tight">
                        {title}
                    </h2>
                </div>

                <button
                    type="button"
                    onclick={onCancel}
                    disabled={processing}
                    aria-label="Tutup modal"
                    class="neo-btn modal-close-button inline-flex size-8 shrink-0 items-center justify-center overflow-hidden rounded border-2 border-black bg-yellow-400 p-0 text-black shadow-[2px_2px_0px_0px_#000] transition-colors hover:bg-red-500 hover:text-white focus:outline-none focus:ring-2 focus:ring-blue-600 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    <svg class="size-4 shrink-0 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <p class="mt-4 text-sm font-bold leading-relaxed text-neutral-700">
                {message}
            </p>

            <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                <Button
                    type="button"
                    onclick={onCancel}
                    variant="white"
                    size="md"
                    disabled={processing}
                    class="w-full sm:w-auto"
                >
                    {cancelLabel}
                </Button>
                <Button
                    type="button"
                    onclick={onConfirm}
                    variant="red"
                    size="md"
                    disabled={processing}
                    class="w-full sm:w-auto"
                >
                    {processing ? processingLabel : confirmLabel}
                </Button>
            </div>
        </div>
    </div>
{/if}
