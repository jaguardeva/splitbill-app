<script lang="ts">
import AppHead from '@/components/AppHead.svelte';
import AuthBrand from '@/components/AuthBrand.svelte';
import Button from '@/components/ui/Button.svelte';
import Input from '@/components/ui/Input.svelte';
import PasswordInput from '@/components/ui/PasswordInput.svelte';
import Alert from '@/components/ui/Alert.svelte';
import { Link, page, useForm } from '@inertiajs/svelte';

const hasDraft = $derived(page.url.includes('has_draft=1'));

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

function submit(e: Event) {
    e.preventDefault();
    form.post('/register', {
        onSuccess: () => form.reset('password', 'password_confirmation'),
    });
}
</script>

<AppHead title="Daftar Akun Baru" />

<div class="min-h-screen bg-neutral-50 text-black flex flex-col font-sans">
    <main class="flex flex-1 items-center justify-center p-4 sm:p-6 lg:p-8">
        <div class="w-full max-w-md rounded-xl border-[3px] border-black bg-white p-6 shadow-[8px_8px_0px_0px_#000] sm:p-8">
            <AuthBrand />

            <div class="text-center">
                <h1 class="text-2xl font-black tracking-tight text-black sm:text-3xl">Daftar Akun SplitBill</h1>
                <p class="mt-1 text-xs font-bold text-neutral-600">Simpan riwayat perhitungan tagihan Anda dengan aman.</p>
            </div>

            {#if hasDraft}
                <div class="mt-4">
                    <Alert variant="warning" title="Draf Tersimpan">
                        Perhitungan yang baru saja Anda buat akan langsung disimpan ke akun setelah Anda menyelesaikan pendaftaran!
                    </Alert>
                </div>
            {/if}

            <form onsubmit={submit} class="mt-6 flex flex-col gap-4">
                <Input
                    id="name"
                    type="text"
                    label="Nama Lengkap"
                    bind:value={form.name}
                    placeholder="Contoh: Budi Pratama"
                    required
                    error={form.errors.name}
                />

                <Input
                    id="email"
                    type="email"
                    label="Alamat Email"
                    bind:value={form.email}
                    placeholder="nama@email.com"
                    required
                    error={form.errors.email}
                />

                <PasswordInput
                    id="password"
                    label="Kata Sandi"
                    bind:value={form.password}
                    placeholder="Minimal 8 karakter"
                    required
                    showStrengthMeter={true}
                    error={form.errors.password}
                />

                <PasswordInput
                    id="password_confirmation"
                    label="Konfirmasi Kata Sandi"
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
                    {form.processing ? 'Mendaftarkan Akun...' : 'Daftar Sekarang'}
                </Button>
            </form>

            <div class="mt-6 text-center text-xs font-bold text-neutral-600">
                Sudah punya akun?
                <Link href="/login" class="font-black text-blue-600 hover:underline cursor-pointer">
                    Masuk di sini
                </Link>
            </div>
        </div>
    </main>
</div>
