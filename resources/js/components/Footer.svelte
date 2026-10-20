<script lang="ts">
import { Link, page } from '@inertiajs/svelte';

const user = $derived((page.props as any).auth?.user);
</script>

<footer class="border-t-[3px] border-black bg-white text-black">
    <div class="mx-auto grid max-w-[1240px] grid-cols-1 gap-8 px-4 py-10 sm:px-6 md:grid-cols-2 lg:grid-cols-4 lg:gap-10 lg:px-8">
        <div class="md:col-span-2 lg:col-span-1">
            <Link href="/" class="inline-flex items-center gap-2.5 transition active:translate-x-0.5 active:translate-y-0.5">
                <span class="flex size-9 items-center justify-center rounded-lg border-2 border-black bg-blue-600 text-sm font-black text-yellow-300 shadow-[2px_2px_0px_0px_#000]">
                    S
                </span>
                <span class="text-xl font-black tracking-tight">SplitBill</span>
            </Link>
            <p class="mt-4 max-w-xs text-sm font-bold leading-relaxed text-neutral-600">
                Solusi praktis untuk membagi tagihan dengan adil, presisi, dan transparan.
            </p>
            <span class="mt-4 inline-flex items-center gap-2 rounded-lg border-2 border-black bg-yellow-300 px-3 py-1.5 text-xs font-black shadow-[2px_2px_0px_0px_#000]">
                <span class="size-2 rounded-full border border-black bg-blue-600"></span>
                Gratis & Transparan
            </span>
        </div>

        <div>
            <h2 class="text-xs font-black uppercase tracking-wider text-black">Navigasi</h2>
            <nav class="mt-4 flex flex-col items-start gap-2.5 text-sm font-bold text-neutral-600">
                <Link href="/" class="transition hover:text-blue-600 hover:underline">Beranda</Link>
                <Link href="/calculator" class="transition hover:text-blue-600 hover:underline">Kalkulator</Link>
                <Link href="/#features-section" class="transition hover:text-blue-600 hover:underline">Fitur</Link>
            </nav>
        </div>

        <div>
            <h2 class="text-xs font-black uppercase tracking-wider text-black">Fitur Utama</h2>
            <ul class="mt-4 flex flex-col gap-2.5 text-sm font-bold text-neutral-600">
                <li>Bagi Rata</li>
                <li>Per Item</li>
                <li>Pajak & Diskon</li>
                <li>Bagikan ke WhatsApp</li>
            </ul>
        </div>

        <div>
            <h2 class="text-xs font-black uppercase tracking-wider text-black">Akun</h2>
            <nav class="mt-4 flex flex-col items-start gap-2.5 text-sm font-bold text-neutral-600">
                {#if user}
                    <Link href="/calculations" class="transition hover:text-blue-600 hover:underline">Riwayat Tagihan</Link>
                    <Link href="/calculator" class="transition hover:text-blue-600 hover:underline">Buat Perhitungan</Link>
                {:else}
                    <Link href="/login" class="transition hover:text-blue-600 hover:underline">Masuk</Link>
                    <Link href="/register" class="transition hover:text-blue-600 hover:underline">Daftar Akun</Link>
                {/if}
            </nav>
        </div>
    </div>

    <div class="border-t-2 border-black">
        <div class="mx-auto flex max-w-[1240px] flex-col items-center justify-between gap-2 px-4 py-4 text-center sm:flex-row sm:px-6 lg:px-8 sm:text-left">
            <p class="text-[11px] font-bold text-neutral-500">
                © 2026 SplitBill Calculator · Dibuat untuk pembagian tagihan yang adil & transparan.
            </p>
            <p class="inline-flex items-center gap-1 text-[11px] font-black text-neutral-600">
                Made with <span class="text-red-500" aria-label="love">♥</span> by <span class="text-blue-600">@jaguardeva</span>
            </p>
        </div>
    </div>
</footer>
