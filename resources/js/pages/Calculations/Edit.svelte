<script lang="ts">
import AppHead from '@/components/AppHead.svelte';
import Navbar from '@/components/Navbar.svelte';
import Footer from '@/components/Footer.svelte';
import Button from '@/components/ui/Button.svelte';
import CalculatorWorkspace from '@/components/calculator/CalculatorWorkspace.svelte';
import type { AdjustmentInput, BillItemInput, ParticipantInput } from '@/lib/calculator-preview';
import { page } from '@inertiajs/svelte';

interface InitialCalculationData {
    id: string;
    title: string;
    currency: string;
    split_mode: 'equal' | 'itemized';
    status: string;
    notes?: string | null;
    subtotal_minor?: number;
    participants: ParticipantInput[];
    items: BillItemInput[];
    adjustments: AdjustmentInput[];
}

let {
    calculation = null,
}: {
    calculation?: InitialCalculationData | null;
} = $props();

const user = $derived((page.props as any).auth?.user);
</script>

<AppHead title={calculation ? `Ubah - ${calculation.title}` : 'Buat Perhitungan Baru'} />

<div class="min-h-screen bg-neutral-50 text-black flex flex-col font-sans">
    <Navbar {user} />

    <main class="mx-auto w-full max-w-[1240px] px-4 py-8 sm:px-6 lg:px-8 flex-1">
        <div class="mb-6 flex items-center justify-between pb-4 border-b-[3px] border-black">
            <div>
                <Button
                    href="/calculations"
                    variant="white"
                    size="sm"
                    class="mb-2"
                >
                    <svg class="size-3.5 mr-1 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali ke Riwayat
                </Button>
                <h1 class="text-2xl font-black tracking-tight text-black sm:text-4xl mt-1">
                    {calculation ? `Ubah: ${calculation.title}` : 'Buat Perhitungan Baru'}
                </h1>
                <p class="mt-1 text-xs font-bold text-neutral-600">
                    Perubahan akan otomatis dikalkulasi ulang dan disimpan ke akun Anda.
                </p>
            </div>
        </div>

        <CalculatorWorkspace initialData={calculation} isEdit={calculation !== null} />
    </main>
    <Footer />
</div>
