<script lang="ts">
import AppHead from '@/components/AppHead.svelte';
import AuthBrand from '@/components/AuthBrand.svelte';
import Button from '@/components/ui/Button.svelte';
import Input from '@/components/ui/Input.svelte';
import PasswordInput from '@/components/ui/PasswordInput.svelte';
import Alert from '@/components/ui/Alert.svelte';
import { Link, useForm } from '@inertiajs/svelte';

let { status }: { status?: string } = $props();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

function submit(e: Event) {
    e.preventDefault();
    form.post('/login', {
        onSuccess: () => form.reset('password'),
    });
}
</script>

<AppHead title="Masuk ke Akun" />

<div class="min-h-screen bg-neutral-50 text-black flex flex-col font-sans">
    <main class="flex flex-1 items-center justify-center p-4 sm:p-6 lg:p-8">
        <div class="w-full max-w-md rounded-xl border-[3px] border-black bg-white p-6 shadow-[8px_8px_0px_0px_#000] sm:p-8">
            <AuthBrand />

            <div class="text-center">
                <h1 class="text-2xl font-black tracking-tight text-black sm:text-3xl">Masuk ke SplitBill</h1>
                <p class="mt-1 text-xs font-bold text-neutral-600">Buka dan kelola riwayat perhitungan Anda.</p>
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

                <div class="relative">
                    <PasswordInput
                        id="password"
                        label="Kata Sandi"
                        bind:value={form.password}
                        placeholder="Masukkan kata sandi"
                        required
                        error={form.errors.password}
                    />
                    <Link href="/forgot-password" class="absolute right-0 top-0.5 text-xs font-bold text-blue-600 hover:underline cursor-pointer">
                        Lupa kata sandi?
                    </Link>
                </div>

                <div class="flex items-center gap-2">
                    <input
                        id="remember"
                        type="checkbox"
                        bind:checked={form.remember}
                        class="size-5 rounded border-2 border-black text-blue-600 focus:ring-0 cursor-pointer"
                    />
                    <label for="remember" class="text-xs font-bold text-black cursor-pointer">Ingat sesi saya</label>
                </div>

                <Button
                    type="submit"
                    variant="blue"
                    size="lg"
                    disabled={form.processing}
                    class="mt-2 w-full uppercase tracking-wider"
                >
                    {form.processing ? 'Memverifikasi...' : 'Masuk Sekarang'}
                </Button>
            </form>

            <div class="mt-6 text-center text-xs font-bold text-neutral-600">
                Belum punya akun?
                <Link href="/register" class="font-black text-blue-600 hover:underline cursor-pointer">
                    Daftar gratis
                </Link>
            </div>
        </div>
    </main>
</div>
