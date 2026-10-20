<script lang="ts">
import AppHead from '@/components/AppHead.svelte';
import Navbar from '@/components/Navbar.svelte';
import Footer from '@/components/Footer.svelte';
import Badge from '@/components/ui/Badge.svelte';
import CalculatorWorkspace from '@/components/calculator/CalculatorWorkspace.svelte';
import { page } from '@inertiajs/svelte';

const user = $derived((page.props as any).auth?.user);
</script>

<AppHead title="Kalkulator Split Bill" />

<div class="min-h-screen bg-neutral-50 text-black flex flex-col font-sans">
    <Navbar {user} />

    <main class="mx-auto w-full max-w-[1240px] px-4 py-8 sm:px-6 lg:px-8 flex-1">
        <div class="mb-6">
            <Badge variant="yellow" size="sm" class="mb-2">
                Kalkulator Cepat
            </Badge>
            <h1 class="text-2xl font-black tracking-tight text-black sm:text-4xl">
                Kalkulator Bagi Tagihan
            </h1>
            <p class="mt-1 text-sm font-bold text-neutral-600">
                Tambahkan partisipan, catat pesanan, dan dapatkan pembagian yang transparan seketika.
            </p>
        </div>

        <CalculatorWorkspace />
    </main>
    <Footer />
</div>
