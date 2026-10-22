<script lang="ts">
import { Link, useForm } from '@inertiajs/svelte';
import { ShieldCheck } from '@lucide/svelte';
import AppHead from '@/components/AppHead.svelte';
import AuthBrand from '@/components/AuthBrand.svelte';
import Button from '@/components/ui/Button.svelte';
import Input from '@/components/ui/Input.svelte';
import Alert from '@/components/ui/Alert.svelte';
import twoFactor from '@/routes/two-factor';

let recovery = $state(false);

const form = useForm({
    code: '',
    recovery_code: '',
});

function toggleMode() {
    recovery = !recovery;
    form.clearErrors();
    form.code = '';
    form.recovery_code = '';
}

function submit(e: Event) {
    e.preventDefault();
    form.post(twoFactor.login.url());
}
</script>

<AppHead title="Autentikasi Dua Faktor — SplitBill" />

<div class="min-h-screen bg-neutral-50 text-black flex flex-col font-sans">
    <main class="flex flex-1 items-center justify-center p-4 sm:p-6 lg:p-8">
        <div class="w-full max-w-md rounded-xl border-[3px] border-black bg-white p-6 shadow-[8px_8px_0px_0px_#000] sm:p-8">
            <AuthBrand />

            <div class="text-center">
                <div class="mx-auto mb-3 flex size-12 items-center justify-center rounded-xl border-2 border-black bg-yellow-400 text-xl shadow-[3px_3px_0px_0px_#000]">
                    <ShieldCheck class="size-6" aria-hidden="true" />
                </div>
                <h1 class="text-2xl font-black tracking-tight text-black sm:text-3xl">Autentikasi Dua Faktor</h1>
                <p class="mt-1 text-xs font-bold text-neutral-600">
                    {#if recovery}
                        Masukkan salah satu kode pemulihan darurat akun Anda.
                    {:else}
                        Buka aplikasi autentikator Anda dan masukkan kode 6 digit verifikasi.
                    {/if}
                </p>
            </div>

            <form onsubmit={submit} class="mt-6 flex flex-col gap-4">
                {#if recovery}
                    <Input
                        id="recovery_code"
                        label="Kode Pemulihan Cadangan"
                        bind:value={form.recovery_code}
                        placeholder="Contoh: AB12C-DE34F"
                        required
                        autofocus
                        error={form.errors.recovery_code}
                        class="text-center font-mono font-bold tracking-wider"
                    />
                {:else}
                    <Input
                        id="code"
                        label="Kode 6 Digit Authenticator"
                        bind:value={form.code}
                        placeholder="000000"
                        maxlength={6}
                        required
                        autofocus
                        error={form.errors.code}
                        class="text-center font-mono text-xl tracking-widest font-black"
                    />
                {/if}

                <Button
                    type="submit"
                    variant="yellow"
                    size="lg"
                    disabled={form.processing || (!recovery && form.code.length !== 6) || (recovery && !form.recovery_code)}
                    class="mt-2 w-full"
                >
                    {form.processing ? 'Memverifikasi...' : 'Verifikasi & Masuk'}
                </Button>

                <div class="mt-2 flex flex-col items-center gap-3 border-t-2 border-black pt-4 text-center">
                    <button
                        type="button"
                        onclick={toggleMode}
                        class="cursor-pointer text-xs font-black text-blue-600 hover:underline"
                    >
                        {#if recovery}
                            Masuk dengan kode aplikasi authenticator
                        {:else}
                            Tidak membawa perangkat? Gunakan recovery code cadangan
                        {/if}
                    </button>

                    <Link
                        href="/login"
                        class="text-xs font-bold text-neutral-500 hover:text-black"
                    >
                        Batal dan kembali ke halaman masuk
                    </Link>
                </div>
            </form>
        </div>
    </main>
</div>



