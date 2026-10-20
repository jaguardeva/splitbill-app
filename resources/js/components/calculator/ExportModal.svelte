<script lang="ts">
    import { page } from "@inertiajs/svelte";
    import Button from "@/components/ui/Button.svelte";
    import { formatDate, formatRupiah } from "@/lib/formatters";
    import type { CalculationPreviewResult } from "@/lib/calculator-preview";

    let {
        isOpen = $bindable(false),
        title = "Rincian Patungan Tagihan",
        result,
    }: {
        isOpen: boolean;
        title?: string;
        result: CalculationPreviewResult;
    } = $props();

    let copied = $state(false);

    const appUrl = $derived(
        (page.props as { appUrl?: string }).appUrl ?? "",
    );
    const participantList = $derived(Object.values(result.participants || {}));

    const formattedText = $derived.by(() => {
        const lines: string[] = [];
        lines.push(`🧾 *TAGIHAN: ${title || "Rincian Patungan Tagihan"}*`);
        lines.push(`📅 ${formatDate(new Date().toISOString())}`);
        lines.push(`━━━━━━━━━━━━━━━━━━━━━`);

        lines.push(`👥 *Rincian Pembayaran:*`);
        participantList.forEach((p) => {
            lines.push(`• *${p.name}*: ${formatRupiah(p.final_total_minor)}`);
        });

        lines.push(`━━━━━━━━━━━━━━━━━━━━━`);
        if (
            result.discount_total_minor > 0 ||
            result.tax_total_minor > 0 ||
            result.service_charge_minor > 0 ||
            result.tip_total_minor > 0
        ) {
            const notes: string[] = [];
            if (result.subtotal_minor > 0)
                notes.push(`Subtotal ${formatRupiah(result.subtotal_minor)}`);
            if (result.discount_total_minor > 0)
                notes.push(
                    `Diskon -${formatRupiah(result.discount_total_minor)}`,
                );
            if (result.tax_total_minor > 0)
                notes.push(`Pajak +${formatRupiah(result.tax_total_minor)}`);
            if (result.service_charge_minor > 0)
                notes.push(
                    `Layanan +${formatRupiah(result.service_charge_minor)}`,
                );
            if (result.tip_total_minor > 0)
                notes.push(`Tip +${formatRupiah(result.tip_total_minor)}`);
            lines.push(`ℹ️ _${notes.join(" • ")}_`);
        }
        lines.push(
            `💰 *Total Keseluruhan*: *${formatRupiah(result.grand_total_minor)}*`,
        );
        lines.push(`━━━━━━━━━━━━━━━━━━━━━`);
        lines.push(``);
        lines.push(`⚡ _Dihitung cepat, adil & transparan pakai *SplitBill*_`);
        lines.push(`👉 Hitung patungan grup kamu di sini:`);
        lines.push(appUrl);

        return lines.join("\n");
    });

    async function copyToClipboard() {
        try {
            let copiedSuccessfully = false;

            if (navigator.clipboard?.writeText) {
                try {
                    await navigator.clipboard.writeText(formattedText);
                    copiedSuccessfully = true;
                } catch {
                    copiedSuccessfully = false;
                }
            }

            if (!copiedSuccessfully) {
                const temporaryTextarea = document.createElement("textarea");
                temporaryTextarea.value = formattedText;
                temporaryTextarea.setAttribute("readonly", "");
                temporaryTextarea.style.position = "fixed";
                temporaryTextarea.style.opacity = "0";
                document.body.appendChild(temporaryTextarea);
                temporaryTextarea.select();
                const copiedWithFallback = document.execCommand("copy");
                temporaryTextarea.remove();

                if (!copiedWithFallback) {
                    throw new Error("Gagal menyalin teks.");
                }
            }

            copied = true;
            setTimeout(() => (copied = false), 2500);
        } catch {
            copied = false;
        }
    }

    function shareViaWhatsApp() {
        const encoded = encodeURIComponent(formattedText);
        window.open(`https://api.whatsapp.com/send?text=${encoded}`, "_blank");
    }

    function triggerPrint() {
        const invoice = document.getElementById("printable-invoice");
        if (!invoice) return;

        const originalParent = invoice.parentNode;
        if (!originalParent) return;

        const placeholder = document.createComment(
            "printable-invoice-placeholder",
        );
        originalParent.insertBefore(placeholder, invoice);
        document.body.appendChild(invoice);
        document.body.classList.add("printing-invoice");

        let isRestored = false;
        const restoreInvoice = () => {
            if (isRestored) return;
            isRestored = true;

            document.body.classList.remove("printing-invoice");
            placeholder.parentNode?.insertBefore(invoice, placeholder);
            placeholder.remove();
        };

        window.addEventListener("afterprint", restoreInvoice, { once: true });

        try {
            window.requestAnimationFrame(() => window.print());
        } catch {
            restoreInvoice();
        }
    }
</script>

<!-- Modal Pop-up (Screen Only) -->
{#if isOpen}
    <div
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4 print:hidden"
    >
        <div
            class="w-full max-w-lg rounded-xl border-[3px] border-black bg-white p-6 shadow-[8px_8px_0px_0px_#000] animate-in fade-in zoom-in-95 max-h-[90vh] overflow-y-auto"
        >
            <div
                class="flex items-center justify-between border-b-2 border-black pb-3"
            >
                <div class="flex items-center gap-2">
                    <span
                        class="flex size-7 items-center justify-center rounded border border-black bg-yellow-400 text-sm"
                    >
                        🧾
                    </span>
                    <h3 class="text-base font-black tracking-tight text-black">
                        Bagikan Rincian Patungan
                    </h3>
                </div>
                <button
                    type="button"
                    onclick={() => (isOpen = false)}
                    aria-label="Tutup"
                    class="neo-btn modal-close-button inline-flex size-8 items-center justify-center overflow-hidden rounded border-2 border-black bg-yellow-400 p-0 text-black shadow-[2px_2px_0px_0px_#000] transition-colors hover:bg-red-500 hover:text-white focus:outline-none focus:ring-2 focus:ring-blue-600 cursor-pointer"
                >
                    <svg
                        class="size-4 shrink-0 stroke-[2.5]"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>
            </div>

            <div class="mt-4">
                <p class="text-xs font-bold text-neutral-700">
                    Kirim langsung ke grup WhatsApp atau salin pesan rincian
                    lengkap di bawah:
                </p>

                <textarea
                    readonly
                    rows="9"
                    class="mt-2 w-full rounded-lg border-2 border-black bg-yellow-50 p-3 font-mono text-xs font-bold text-black shadow-[2px_2px_0px_0px_#000] focus:outline-none"
                    value={formattedText}></textarea>
            </div>

            <div class="mt-5 grid grid-cols-2 gap-2.5">
                <div class="contents">
                    <Button
                        type="button"
                        onclick={copyToClipboard}
                        variant="white"
                        size="md"
                        class="h-11 min-h-11 w-full min-w-0 justify-center px-2 sm:px-4"
                    >
                        {#if copied}
                            <svg
                                class="mr-1.5 size-4 shrink-0 text-emerald-600 stroke-[3]"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M5 13l4 4L19 7"
                                />
                            </svg>
                            Tersalin!
                        {:else}
                            <svg
                                class="mr-1.5 size-5 shrink-0 stroke-[2.5]"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M8 5H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-1M8 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M8 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2m0 0h2a2 2 0 0 1 2 2v3m2 4H10m0 0l3-3m-3 3l3 3"
                                />
                            </svg>
                            Salin Teks
                        {/if}
                    </Button>

                    <Button
                        type="button"
                        onclick={shareViaWhatsApp}
                        variant="yellow"
                        size="md"
                        class="h-11 min-h-11 w-full min-w-0 justify-center px-2 sm:px-4"
                    >
                        <svg
                            class="mr-1 size-8 shrink-0 fill-black"
                            viewBox="0 0 24 24"
                        >
                            <path
                                d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.174.086.275.072.376-.044.101-.116.433-.506.549-.68.116-.173.231-.145.39-.086.159.058 1.011.477 1.184.564.173.087.289.13.332.203.043.072.043.419-.101.824z"
                            />
                        </svg>
                        WhatsApp
                    </Button>
                </div>

                <Button
                    type="button"
                    onclick={triggerPrint}
                    variant="blue"
                    size="md"
                    class="col-span-2 h-11 min-h-11 w-full justify-center"
                >
                    <svg
                        class="mr-1.5 size-5 shrink-0 stroke-[2.5]"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M17 17h2a2 2 0 0 0 2-2v-4a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2h2m2 4h6a2 2 0 0 0 2-2v-4H7v4a2 2 0 0 0 2 2zm8-12V5a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v4h10z"
                        />
                    </svg>
                    Cetak / Simpan PDF (Invoice Resmi)
                </Button>
            </div>
        </div>
    </div>
{/if}

<!-- Printable Invoice Document (Hidden on screen, Visible only when printing) -->
<div
    id="printable-invoice"
    class="hidden print:block p-8 font-sans text-black bg-white max-w-[800px] mx-auto"
>
    <!-- Invoice Header -->
    <div class="flex items-start justify-between border-b-2 border-black pb-5">
        <div>
            <div class="flex items-center gap-2">
                <div
                    class="flex size-9 items-center justify-center rounded border-2 border-black bg-black text-white font-black text-lg"
                >
                    S
                </div>
                <h1 class="text-2xl font-black tracking-tight text-black">
                    SplitBill
                </h1>
            </div>
            <p class="text-xs font-bold text-neutral-600 mt-1">
                Sistem Perhitungan Patungan Tagihan Transparan & Adil
            </p>
            <p class="text-[11px] font-medium text-neutral-500">
                {appUrl}
            </p>
        </div>

        <div class="text-right">
            <span
                class="inline-block rounded border border-black bg-neutral-100 px-2 py-0.5 text-xs font-black uppercase tracking-wider"
            >
                INVOICE PATUNGAN
            </span>
            <p class="text-xs font-bold text-neutral-800 mt-1.5">
                Tanggal: {formatDate(new Date().toISOString())}
            </p>
            <p class="text-[11px] font-medium text-neutral-600">
                Status: Selesai / Lunas
            </p>
        </div>
    </div>

    <!-- Bill Title Info -->
    <div class="my-5 rounded-lg border border-neutral-300 bg-neutral-50 p-4">
        <span
            class="text-[10px] font-black uppercase tracking-wider text-neutral-500"
            >Judul Tagihan</span
        >
        <h2 class="text-xl font-black text-black mt-0.5">
            {title || "Rincian Patungan Tagihan"}
        </h2>
        <p class="text-xs font-medium text-neutral-600 mt-1">
            Total Partisipan: <strong>{participantList.length} orang</strong> •
            Total Tagihan:
            <strong>{formatRupiah(result.grand_total_minor)}</strong>
        </p>
    </div>

    <!-- Itemized List Table (if items exist) -->
    {#if result.items && result.items.length > 0}
        <div class="mb-6">
            <h3
                class="text-xs font-black uppercase tracking-wider text-black mb-2"
            >
                Rincian Menu / Item Pesanan
            </h3>
            <table class="w-full text-left text-xs border border-black">
                <thead>
                    <tr class="border-b border-black bg-neutral-100 font-black">
                        <th class="p-2 w-8 text-center">No</th>
                        <th class="p-2">Deskripsi Item</th>
                        <th class="p-2 w-14 text-center">Qty</th>
                        <th class="p-2 w-28 text-right">Harga Satuan</th>
                        <th class="p-2 w-28 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    {#each result.items as item, idx}
                        <tr class="border-b border-neutral-200">
                            <td class="p-2 text-center text-neutral-600"
                                >{idx + 1}</td
                            >
                            <td class="p-2 font-bold"
                                >{item.description || `Item #${idx + 1}`}</td
                            >
                            <td class="p-2 text-center font-bold"
                                >{item.quantity}</td
                            >
                            <td class="p-2 text-right"
                                >{formatRupiah(item.unit_price_minor)}</td
                            >
                            <td class="p-2 text-right font-black"
                                >{formatRupiah(item.line_subtotal_minor)}</td
                            >
                        </tr>
                    {/each}
                </tbody>
            </table>
        </div>
    {/if}

    <!-- Financial Breakdown -->
    <div class="mb-6 flex justify-end">
        <div class="w-72 text-xs">
            <div class="flex justify-between py-1 border-b border-neutral-200">
                <span class="text-neutral-600 font-bold">Subtotal Bersih:</span>
                <span class="font-bold"
                    >{formatRupiah(result.subtotal_minor)}</span
                >
            </div>
            {#if result.discount_total_minor > 0}
                <div
                    class="flex justify-between py-1 border-b border-neutral-200 text-emerald-700"
                >
                    <span class="font-bold">Diskon / Potongan:</span>
                    <span class="font-bold"
                        >-{formatRupiah(result.discount_total_minor)}</span
                    >
                </div>
            {/if}
            {#if result.service_charge_minor > 0}
                <div
                    class="flex justify-between py-1 border-b border-neutral-200"
                >
                    <span class="text-neutral-600 font-bold"
                        >Biaya Layanan:</span
                    >
                    <span class="font-bold"
                        >+{formatRupiah(result.service_charge_minor)}</span
                    >
                </div>
            {/if}
            {#if result.tax_total_minor > 0}
                <div
                    class="flex justify-between py-1 border-b border-neutral-200"
                >
                    <span class="text-neutral-600 font-bold">Pajak (PB1):</span>
                    <span class="font-bold"
                        >+{formatRupiah(result.tax_total_minor)}</span
                    >
                </div>
            {/if}
            {#if result.tip_total_minor > 0}
                <div
                    class="flex justify-between py-1 border-b border-neutral-200"
                >
                    <span class="text-neutral-600 font-bold">Tip Sukarela:</span
                    >
                    <span class="font-bold"
                        >+{formatRupiah(result.tip_total_minor)}</span
                    >
                </div>
            {/if}
            <div
                class="flex justify-between py-2 border-b-2 border-black text-sm font-black mt-1"
            >
                <span>TOTAL AKHIR:</span>
                <span>{formatRupiah(result.grand_total_minor)}</span>
            </div>
        </div>
    </div>

    <!-- Per-Person Settlement Table -->
    <div class="mb-6">
        <h3 class="text-xs font-black uppercase tracking-wider text-black mb-2">
            Rincian Pembayaran Per Orang (Patungan)
        </h3>
        <table class="w-full text-left text-xs border border-black">
            <thead>
                <tr class="border-b border-black bg-neutral-100 font-black">
                    <th class="p-2 w-8 text-center">No</th>
                    <th class="p-2">Nama Partisipan</th>
                    <th class="p-2 w-28 text-right">Subtotal Porsi</th>
                    <th class="p-2 w-28 text-right">Pajak & Biaya</th>
                    <th class="p-2 w-32 text-right">Total Kewajiban Bayar</th>
                </tr>
            </thead>
            <tbody>
                {#each participantList as p, idx}
                    <tr class="border-b border-neutral-200">
                        <td class="p-2 text-center text-neutral-600"
                            >{idx + 1}</td
                        >
                        <td class="p-2 font-black text-black">{p.name}</td>
                        <td class="p-2 text-right"
                            >{formatRupiah(p.subtotal_minor)}</td
                        >
                        <td class="p-2 text-right"
                            >{formatRupiah(
                                (p.tax_minor || 0) +
                                    (p.service_charge_minor || 0) +
                                    (p.tip_minor || 0) -
                                    (p.discount_minor || 0),
                            )}</td
                        >
                        <td class="p-2 text-right font-black text-black text-sm"
                            >{formatRupiah(p.final_total_minor)}</td
                        >
                    </tr>
                {/each}
            </tbody>
        </table>
    </div>

    <!-- Invoice Footer Note -->
    <div class="border-t-2 border-black pt-4 text-center">
        <p class="text-xs font-black text-black">
            Terima kasih atas patungannya!
        </p>
        <p class="text-[11px] text-neutral-600 mt-1">
            Dibuat secara otomatis, presisi & transparan menggunakan aplikasi <strong
                >SplitBill</strong
            > ({appUrl}).
        </p>
    </div>
</div>
