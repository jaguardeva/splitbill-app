<script lang="ts">
import AppHead from '@/components/AppHead.svelte';
import Footer from '@/components/Footer.svelte';
import Button from '@/components/ui/Button.svelte';
import Input from '@/components/ui/Input.svelte';
import PasswordInput from '@/components/ui/PasswordInput.svelte';
import { Link, useForm } from '@inertiajs/svelte';

let {
    token,
    email = '',
}: {
    token: string;
    email?: string;
} = $props();

const form = useForm(() => ({
    token,
    email,
    password: '',
    password_confirmation: '',
}));

function submit(e: Event) {
    e.preventDefault();
    form.post('/reset-password', {
        onSuccess: () => form.reset('password', 'password_confirmation'),
    });
}
</script>

<AppHead title="Atur Ulang Kata Sandi" />

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
                <div class="inline-flex size-12 items-center justify-center rounded-lg border-2 border-black bg-blue-600 text-yellow-300 shadow-[2px_2px_0px_0px_#000] mb-3">
                    <svg class="size-6 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <h1 class="text-2xl font-black tracking-tight text-black sm:text-3xl">Atur Ulang Kata Sandi</h1>
                <p class="mt-1 text-xs font-bold text-neutral-600">Masukkan kata sandi baru untuk akun Anda.</p>
            </div>

            <form onsubmit={submit} class="mt-6 flex flex-col gap-4">
                <input type="hidden" name="token" value={form.token} />

                <Input
                    id="email"
                    type="email"
                    label="Alamat Email"
                    bind:value={form.email}
                    required
                    error={form.errors.email}
                />

                <PasswordInput
                    id="password"
                    label="Kata Sandi Baru"
                    bind:value={form.password}
                    placeholder="Minimal 8 karakter"
                    required
                    showStrengthMeter={true}
                    error={form.errors.password}
                />

                <PasswordInput
                    id="password_confirmation"
                    label="Ulangi Kata Sandi Baru"
                    bind:value={form.password_confirmation}
                    placeholder="Ketik ulang kata sandi"
                    required
                />

                <Button
                    type="submit"
                    variant="blue"
                    size="lg"
                    disabled={form.processing}
                    class="mt-2 w-full uppercase tracking-wider"
                >
                    {form.processing ? 'Menyimpan...' : 'Simpan Kata Sandi Baru'}
                </Button>
            </form>
        </div>
    </main>
    <Footer />
</div>
