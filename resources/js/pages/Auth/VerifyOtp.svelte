<script lang="ts">
import { onDestroy, onMount } from 'svelte';
import AppHead from '@/components/AppHead.svelte';
import Footer from '@/components/Footer.svelte';
import Button from '@/components/ui/Button.svelte';
import Badge from '@/components/ui/Badge.svelte';
import Toast from '@/components/ui/Toast.svelte';
import { Link, useForm, router, page } from '@inertiajs/svelte';

let {
    email,
    cooldown_seconds = 0,
}: {
    email: string;
    cooldown_seconds?: number;
} = $props();

const form = useForm({
    otp: '',
});

let countdown = $state((() => cooldown_seconds)());
let timer: ReturnType<typeof setInterval> | null = null;
let isResending = $state(false);

const flashSuccess = $derived((page.props as any).flash?.success);
const flashError = $derived((page.props as any).flash?.error);

onMount(() => {
    if (countdown > 0) {
        startTimer();
    }
});

onDestroy(() => {
    if (timer) clearInterval(timer);
});

function startTimer() {
    if (timer) clearInterval(timer);
    timer = setInterval(() => {
        if (countdown > 0) {
            countdown -= 1;
        } else {
            if (timer) clearInterval(timer);
        }
    }, 1000);
}

function submit(e: Event) {
    e.preventDefault();
    form.post('/email/verify-otp');
}

function resend() {
    if (countdown > 0 || isResending) return;
    isResending = true;
    router.post('/email/resend-otp', {}, {
        onFinish: () => {
            isResending = false;
            countdown = 60;
            startTimer();
        },
    });
}
</script>

<AppHead title="Verifikasi Email OTP" />

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
        <div class="w-full max-w-md rounded-xl border-[3px] border-black bg-white p-6 shadow-[8px_8px_0px_0px_#000] sm:p-8 text-center">
            <div class="inline-flex size-14 items-center justify-center rounded-lg border-2 border-black bg-yellow-400 text-black shadow-[2px_2px_0px_0px_#000] mb-4">
                <svg class="size-7 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </div>

            <h1 class="text-2xl font-black tracking-tight text-black sm:text-3xl">Verifikasi Email Anda</h1>
            <p class="mt-2 text-xs font-bold text-neutral-600 leading-relaxed">
                Kami telah mengirimkan 6 digit kode OTP ke alamat: <br />
                <span class="inline-block mt-1 rounded border border-black bg-yellow-100 px-2 py-0.5 text-xs font-black text-black shadow-[1px_1px_0px_0px_#000]">
                    {email}
                </span>
            </p>

            <form onsubmit={submit} class="mt-6 flex flex-col gap-4">
                <div>
                    <label for="otp" class="sr-only">Kode OTP 6 Digit</label>
                    <input
                        id="otp"
                        type="text"
                        inputmode="numeric"
                        maxlength="6"
                        pattern="[0-9]*"
                        bind:value={form.otp}
                        placeholder="123456"
                        required
                        class="w-full rounded-lg border-2 border-black bg-yellow-50 py-3 text-center text-3xl font-mono font-black tracking-[0.4em] text-black shadow-[3px_3px_0px_0px_#000] focus:border-blue-600 focus:ring-2 focus:ring-blue-600 focus:shadow-[3px_3px_0px_0px_#000] focus:outline-none"
                    />
                    {#if form.errors.otp}
                        <p class="mt-2 text-xs font-black text-red-600">{form.errors.otp}</p>
                    {/if}
                </div>

                <Button
                    type="submit"
                    variant="blue"
                    size="lg"
                    disabled={form.processing || form.otp.length < 6}
                    class="w-full uppercase tracking-wider"
                >
                    {form.processing ? 'Memverifikasi...' : 'Verifikasi Akun'}
                </Button>
            </form>

            <div class="mt-6 border-t-2 border-black pt-4">
                <p class="text-xs font-bold text-neutral-600">
                    Tidak menerima kode email?
                </p>

                {#if countdown > 0}
                    <p class="mt-1 text-xs font-bold text-neutral-600">
                        Kirim ulang dalam <Badge variant="yellow" size="sm">{countdown} detik</Badge>
                    </p>
                {:else}
                    <Button
                        type="button"
                        onclick={resend}
                        disabled={isResending}
                        variant="white"
                        size="md"
                        class="mt-2 uppercase tracking-wider"
                    >
                        {isResending ? 'Mengirim...' : 'Kirim Ulang Kode OTP'}
                    </Button>
                {/if}
            </div>
        </div>
    </main>

    <Footer />

    <Toast message={flashError || flashSuccess} variant={flashError ? 'error' : 'success'} />
</div>
