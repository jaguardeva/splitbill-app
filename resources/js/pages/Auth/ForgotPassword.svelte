<script lang="ts">
import AppHead from '@/components/AppHead.svelte';
import Footer from '@/components/Footer.svelte';
import Button from '@/components/ui/Button.svelte';
import Input from '@/components/ui/Input.svelte';
import Alert from '@/components/ui/Alert.svelte';
import { Link, useForm } from '@inertiajs/svelte';

let { status }: { status?: string } = $props();

const form = useForm({
    email: '',
});

function submit(e: Event) {
    e.preventDefault();
    form.post('/forgot-password');
}
</script>

<AppHead title="Lupa Kata Sandi" />

<div class="min-h-screen bg-neutral-50 text-black flex flex-col font-sans">
    <!-- Focused Header (No Navbar) -->
    <header class="py-6 flex justify-center">
        <Link href="/" class="flex items-center gap-2.5 transition active:translate-x-0.5 active:translate-y-0.5 cursor-pointer">
            <div class="flex size-10 items-center justify-center rounded-lg border-2 border-black bg-blue-600 text-yellow-300 shadow-[2px_2px_0px_0px_#000]">
                <svg class="size-6 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>
            <div class="flex flex-col">
                <span class="text-xl font-black tracking-tight text-black">SplitBill</span>
                <span class="-mt-1 text-[10px] font-black uppercase tracking-wider text-blue-600">Hitung Patungan Adil</span>
            </div>
        </Link>
    </header>

    <main class="flex flex-1 items-center justify-center p-4 sm:p-6 lg:p-8">
        <div class="w-full max-w-md rounded-xl border-[3px] border-black bg-white p-6 shadow-[8px_8px_0px_0px_#000] sm:p-8">
            <div class="text-center">
                <div class="inline-flex size-12 items-center justify-center rounded-lg border-2 border-black bg-yellow-400 text-black shadow-[2px_2px_0px_0px_#000] mb-3">
                    <svg class="size-6 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                </div>
                <h1 class="text-2xl font-black tracking-tight text-black sm:text-3xl">Pemulihan Kata Sandi</h1>
                <p class="mt-1 text-xs font-bold text-neutral-600">Masukkan email akun Anda untuk menerima tautan atur ulang kata sandi.</p>
            </div>

            {#if status}
                <div class="mt-4">
                    <Alert variant="warning">{status}</Alert>
                </div>
            {/if}

            <form onsubmit={submit} class="mt-6 flex flex-col gap-4">
                <Input
                    id="email"
                    type="email"
                    label="Alamat Email"
                    bind:value={form.email}
                    placeholder="nama@email.com"
                    required
                    error={form.errors.email}
                />

                <Button
                    type="submit"
                    variant="blue"
                    size="lg"
                    disabled={form.processing}
                    class="mt-2 w-full uppercase tracking-wider"
                >
                    {form.processing ? 'Mengirim...' : 'Kirim Tautan Pemulihan'}
                </Button>
            </form>

            <div class="mt-6 text-center text-xs font-bold text-neutral-600">
                Ingat kata sandi Anda?
                <Link href="/login" class="font-black text-blue-600 hover:underline cursor-pointer">
                    Kembali ke halaman masuk
                </Link>
            </div>
        </div>
    </main>
    <Footer />
</div>
