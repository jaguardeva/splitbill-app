<script lang="ts">
import { router } from '@inertiajs/svelte';
import { Check, Copy } from '@lucide/svelte';
import Button from '@/components/ui/Button.svelte';
import Input from '@/components/ui/Input.svelte';
import PasswordInput from '@/components/ui/PasswordInput.svelte';
import twoFactor from '@/routes/two-factor';
import passwordConfirm from '@/routes/password/confirm';
import { copyText } from '@/lib/clipboard';

let {
    enabled = false,
    qrCodeSvg = null,
    setupKey = null,
    recoveryCodes = null,
}: {
    enabled?: boolean;
    qrCodeSvg?: string | null;
    setupKey?: string | null;
    recoveryCodes?: string[] | null;
} = $props();

type SetupStep = 'idle' | 'password' | 'scan' | 'verify' | 'recovery';

let setupStep = $state<SetupStep>('idle');
let code = $state('');
let password = $state('');
let passwordError = $state<string | undefined>();
let codeError = $state<string | undefined>();
let requestError = $state<string | undefined>();
let processing = $state(false);
let recoveryProcessing = $state(false);
let recoveryCodesVisible = $state(false);
let recoveryCodesAcknowledged = $state(false);
let copyKeySuccess = $state(false);
let copyCodesSuccess = $state(false);
let copyProcessing = $state(false);
let copyError = $state<string | undefined>();
let localRecoveryCodes = $state<string[] | null>(null);
let disableRequested = $state(false);

const formattedSetupKey = $derived(setupKey ? setupKey.match(/.{1,4}/g)?.join(' ') || setupKey : '');
const currentStep = $derived(
    setupStep === 'password' ? 1 : setupStep === 'scan' ? 2 : setupStep === 'verify' ? 3 : setupStep === 'recovery' ? 4 : 0
);

function getCsrfToken(): string {
    if (typeof document === 'undefined') return '';
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

function getXsrfToken(): string {
    if (typeof document === 'undefined') return '';
    const match = document.cookie.match(/(^|;)\s*XSRF-TOKEN\s*=\s*([^;]+)/);
    return match ? decodeURIComponent(match[2]) : '';
}

async function confirmCurrentPassword(): Promise<boolean> {
    try {
        const csrfToken = getCsrfToken();
        const xsrfToken = getXsrfToken();

        const headers: Record<string, string> = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        };

        if (csrfToken) {
            headers['X-CSRF-TOKEN'] = csrfToken;
        }

        if (xsrfToken) {
            headers['X-XSRF-TOKEN'] = xsrfToken;
        }

        const response = await fetch(passwordConfirm.store.url(), {
            method: 'POST',
            credentials: 'same-origin',
            headers,
            body: JSON.stringify({
                password,
                ...(csrfToken ? { _token: csrfToken } : {}),
            }),
        });

        if (response.status === 422) {
            const data = await response.json().catch(() => null);
            passwordError = data?.errors?.password?.[0] || 'Kata sandi yang Anda masukkan salah.';
            return false;
        }

        if (response.status === 419) {
            requestError = 'Sesi keamanan telah kedaluwarsa. Muat ulang halaman lalu coba lagi.';
            return false;
        }

        if (!response.ok) {
            requestError = 'Verifikasi kata sandi gagal. Silakan coba lagi.';
            return false;
        }

        return true;
    } catch {
        requestError = 'Terjadi kesalahan jaringan. Silakan coba lagi.';
        return false;
    }
}

$effect(() => {
    if (enabled && setupStep !== 'recovery') {
        setupStep = 'idle';
        code = '';
        password = '';
    } else if (qrCodeSvg && (setupStep === 'idle' || setupStep === 'password')) {
        setupStep = 'scan';
    }

    if (recoveryCodes) {
        localRecoveryCodes = recoveryCodes;
    }
});

function startSetup(): void {
    setupStep = 'password';
    password = '';
    passwordError = undefined;
    requestError = undefined;
}

function cancelSetup(): void {
    setupStep = 'idle';
    code = '';
    password = '';
    passwordError = undefined;
    codeError = undefined;
    requestError = undefined;
}

async function confirmPassword(event?: Event): Promise<void> {
    event?.preventDefault();
    if (!password || processing) return;

    processing = true;
    passwordError = undefined;
    requestError = undefined;

    if (!await confirmCurrentPassword()) {
        processing = false;
        return;
    }

    password = '';
    router.post(
        twoFactor.enable.url(),
        {},
        {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                setupStep = 'scan';
            },
            onError: (errors) => {
                requestError = errors.two_factor || 'Persiapan 2FA gagal. Silakan coba lagi.';
            },
            onFinish: () => {
                processing = false;
            },
        }
    );
}

function confirmCode(event: Event): void {
    event.preventDefault();
    if (code.length !== 6) return;

    processing = true;
    codeError = undefined;

    router.post(
        twoFactor.confirm.url(),
        { code },
        {
            preserveScroll: true,
            onSuccess: () => {
                setupStep = 'recovery';
                code = '';
                loadRecoveryCodes();
            },
            onError: (errors) => {
                codeError = errors.code || 'Kode verifikasi tidak valid.';
            },
            onFinish: () => {
                processing = false;
            },
        }
    );
}

function loadRecoveryCodes(): void {
    recoveryProcessing = true;
    requestError = undefined;

    fetch(twoFactor.recoveryCodes.url(), {
        credentials: 'same-origin',
        headers: { Accept: 'application/json' },
    })
        .then(async (response) => {
            if (!response.ok) throw new Error('Recovery codes unavailable');
            return response.json() as Promise<string[]>;
        })
        .then((codes) => {
            localRecoveryCodes = codes.length > 0 ? codes : null;
            recoveryCodesVisible = true;
        })
        .catch(() => {
            requestError = 'Kode pemulihan tidak dapat dimuat. Silakan coba lagi.';
        })
        .finally(() => {
            recoveryProcessing = false;
        });
}

function regenerateRecoveryCodes(): void {
    processing = true;
    requestError = undefined;

    router.post(
        twoFactor.regenerateRecoveryCodes.url(),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                loadRecoveryCodes();
            },
            onError: () => {
                requestError = 'Kode pemulihan baru tidak dapat dibuat.';
            },
            onFinish: () => {
                processing = false;
            },
        }
    );
}

function disableTwoFactor(): void {
    disableRequested = true;
    password = '';
    passwordError = undefined;
    requestError = undefined;
}

async function confirmDisable(event?: Event): Promise<void> {
    event?.preventDefault();
    if (!password || processing) return;

    processing = true;
    passwordError = undefined;
    requestError = undefined;

    if (!await confirmCurrentPassword()) {
        processing = false;
        return;
    }

    router.delete(twoFactor.disable.url(), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            setupStep = 'idle';
            disableRequested = false;
            password = '';
        },
        onError: () => {
            requestError = '2FA tidak dapat dinonaktifkan.';
        },
        onFinish: () => {
            processing = false;
        },
    });
}

async function copySetupKey(): Promise<void> {
    if (!setupKey) return;

    copyProcessing = true;
    copyError = undefined;
    const copied = await copyText(setupKey);
    copyProcessing = false;

    if (!copied) {
        copyError = 'Secret key tidak dapat disalin. Silakan pilih dan salin secara manual.';

        return;
    }

    copyKeySuccess = true;
    setTimeout(() => (copyKeySuccess = false), 2000);
}

async function copyAllRecoveryCodes(): Promise<void> {
    if (!localRecoveryCodes) return;

    copyProcessing = true;
    copyError = undefined;
    const copied = await copyText(localRecoveryCodes.join('\n'));
    copyProcessing = false;

    if (!copied) {
        copyError = 'Recovery codes tidak dapat disalin. Silakan pilih dan salin secara manual.';

        return;
    }

    copyCodesSuccess = true;
    setTimeout(() => (copyCodesSuccess = false), 2000);
}

function downloadRecoveryCodes(): void {
    if (!localRecoveryCodes) return;
    const content = `KODE PEMULIHAN CADANGAN SPLITBILL (2FA)\n\n${localRecoveryCodes.join('\n')}\n\nSimpan kode ini di tempat yang aman.`;
    const blob = new Blob([content], { type: 'text/plain;charset=utf-8' });
    const url = URL.createObjectURL(blob);
    const anchor = document.createElement('a');
    anchor.href = url;
    anchor.download = 'splitbill-recovery-codes.txt';
    anchor.click();
    URL.revokeObjectURL(url);
}
</script>

<div class="mt-6 space-y-6">
    {#if enabled && !disableRequested}
        <div class="rounded-xl border-2 border-emerald-500 bg-emerald-50 p-4 text-emerald-950 sm:p-5">
            <p class="text-sm font-black text-emerald-900">Autentikasi Dua Faktor Sedang Aktif</p>
            <p class="mt-1 text-xs font-bold text-emerald-800">Akun Anda akan meminta kode authenticator setiap kali login.</p>
        </div>

        <div class="rounded-xl border-2 border-black bg-neutral-50 p-4 sm:p-5">
            <h3 class="text-sm font-black">Kode Pemulihan</h3>
            <p class="mt-1 text-xs font-bold text-neutral-600">Gunakan recovery code jika kehilangan akses ke aplikasi authenticator.</p>
            <div class="mt-4 flex flex-wrap gap-2.5">
                <Button onclick={() => { recoveryCodesVisible = true; loadRecoveryCodes(); }} variant="white" size="sm" disabled={recoveryProcessing}>Lihat Kode Pemulihan</Button>
                <Button onclick={regenerateRecoveryCodes} variant="white" size="sm" disabled={processing || recoveryProcessing}>{processing ? 'Membuat...' : 'Regenerasi Kode Baru'}</Button>
            </div>
        </div>

        <div class="flex justify-end border-t-2 border-neutral-200 pt-4">
            <Button onclick={disableTwoFactor} variant="red" size="sm">Nonaktifkan 2FA</Button>
        </div>
    {:else if enabled && disableRequested}
        <div class="rounded-xl border-2 border-red-600 bg-red-50 p-4 sm:p-5">
            <h3 class="text-base font-black text-red-700">Konfirmasi Nonaktifkan 2FA</h3>
            <p class="mt-1 text-xs font-bold text-red-800">Masukkan password untuk menonaktifkan perlindungan dua faktor.</p>
            <form onsubmit={confirmDisable} class="mt-4">
                <PasswordInput id="disable_two_factor_password" label="Password Saat Ini" bind:value={password} error={passwordError} disabled={processing} required />
                <div class="mt-5 flex justify-end gap-2 border-t-2 border-red-200 pt-4">
                    <Button type="button" onclick={() => (disableRequested = false)} variant="white" size="sm" disabled={processing}>Batal</Button>
                    <Button type="submit" variant="red" size="sm" disabled={processing || !password}>{processing ? 'Memproses...' : 'Ya, Nonaktifkan'}</Button>
                </div>
            </form>
        </div>
    {:else if setupStep === 'idle'}
        <div class="rounded-xl border-2 border-black bg-yellow-50 p-4 sm:p-5">
            <p class="text-sm font-black">Tingkatkan Keamanan Akun Anda</p>
            <p class="mt-1 text-xs font-bold text-neutral-700">2FA menambahkan lapisan perlindungan ekstra saat login.</p>
            <div class="mt-5"><Button onclick={startSetup} variant="yellow" size="md">Mulai Siapkan 2FA</Button></div>
        </div>
    {:else}
        <div class="rounded-xl border-2 border-black bg-white p-4 sm:p-6 shadow-[3px_3px_0px_0px_#000]">
            <div class="mb-6 grid grid-cols-4 gap-2">
                {#each ['Password', 'Scan QR', 'Verifikasi', 'Recovery'] as label, index}
                    <div class="text-center">
                        <div class="mx-auto flex size-8 items-center justify-center rounded-full border-2 border-black text-xs font-black {currentStep >= index + 1 ? 'bg-yellow-400' : 'bg-neutral-100'}">{index + 1}</div>
                        <span class="mt-1 block text-[10px] font-black uppercase text-neutral-600">{label}</span>
                    </div>
                {/each}
            </div>

            {#if setupStep === 'password'}
                <h3 class="text-base font-black">Langkah 1: Konfirmasi Password</h3>
                <p class="mt-1 text-xs font-bold text-neutral-600">Konfirmasi password sebelum mengubah keamanan akun.</p>
                <form onsubmit={confirmPassword} class="mt-4">
                    <PasswordInput id="two_factor_password" label="Password Saat Ini" bind:value={password} error={passwordError} disabled={processing} required />
                    <div class="mt-5 flex justify-end gap-2 border-t-2 border-black pt-4">
                        <Button type="button" onclick={cancelSetup} variant="white" size="sm" disabled={processing}>Batal</Button>
                        <Button type="submit" variant="yellow" size="sm" disabled={processing || !password}>{processing ? 'Memeriksa...' : 'Lanjutkan'}</Button>
                    </div>
                </form>
            {:else if setupStep === 'scan'}
                <h3 class="text-base font-black">Langkah 2: Hubungkan Aplikasi Authenticator</h3>
                <p class="mt-1 text-xs font-bold text-neutral-600">Pindai QR code dengan Google Authenticator, Microsoft Authenticator, atau aplikasi sejenis.</p>
                <div class="mt-5 flex flex-col items-center gap-5 sm:flex-row">
                    <div class="flex size-52 items-center justify-center rounded-xl border-2 border-black bg-white p-3 shadow-[3px_3px_0px_0px_#000]">{@html qrCodeSvg || ''}</div>
                    <div class="flex-1 space-y-3">
                        <p class="text-xs font-bold leading-relaxed text-neutral-700">Jika tidak bisa memindai QR, masukkan secret key secara manual.</p>
                        {#if setupKey}
                            <div class="rounded-lg border-2 border-neutral-300 bg-neutral-50 p-3">
                                <p class="text-[11px] font-bold uppercase text-neutral-500">Secret key</p>
                                <div class="mt-1 flex items-center justify-between gap-2"><code class="break-all font-mono text-xs font-black tracking-wider">{formattedSetupKey}</code><button type="button" onclick={copySetupKey} disabled={copyProcessing} aria-label="Salin secret key" class="inline-flex items-center gap-1 rounded border border-black bg-white px-2 py-1 text-[11px] font-black disabled:cursor-not-allowed disabled:opacity-50">{#if copyKeySuccess}<Check class="size-3.5 text-emerald-600" strokeWidth={3} aria-hidden="true" />{:else}<Copy class="size-3.5" aria-hidden="true" />{/if}{copyProcessing ? 'Menyalin...' : copyKeySuccess ? 'Tersalin!' : 'Salin'}</button></div>
                            </div>
                        {/if}
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-2 border-t-2 border-black pt-4"><Button onclick={cancelSetup} variant="white" size="sm">Batal</Button><Button onclick={() => (setupStep = 'verify')} variant="yellow" size="sm">Lanjutkan</Button></div>
            {:else if setupStep === 'verify'}
                <h3 class="text-base font-black">Langkah 3: Verifikasi Kode</h3>
                <p class="mt-1 text-xs font-bold text-neutral-600">Masukkan kode 6 digit yang sedang ditampilkan aplikasi authenticator.</p>
                <form onsubmit={confirmCode} class="mt-5">
                    <Input id="two_factor_code" placeholder="000000" maxlength={6} bind:value={code} error={codeError} disabled={processing} class="text-center font-mono text-lg font-black tracking-widest" />
                    <div class="mt-5 flex justify-end gap-2 border-t-2 border-black pt-4"><Button type="button" onclick={() => (setupStep = 'scan')} variant="white" size="sm" disabled={processing}>Kembali</Button><Button type="submit" variant="yellow" size="sm" disabled={processing || code.length !== 6}>{processing ? 'Memverifikasi...' : 'Konfirmasi Kode'}</Button></div>
                </form>
            {:else if setupStep === 'recovery'}
                <h3 class="text-base font-black">Langkah 4: Simpan Recovery Codes</h3>
                <p class="mt-1 text-xs font-bold text-neutral-600">Simpan kode pemulihan di tempat aman. Setiap kode hanya dapat digunakan satu kali.</p>
                {#if recoveryProcessing}<p class="mt-5 text-sm font-bold">Memuat recovery codes...</p>{:else if localRecoveryCodes}
                    <div class="mt-5 grid grid-cols-2 gap-2 rounded-lg border-2 border-black bg-neutral-100 p-4 font-mono text-xs font-black">{#each localRecoveryCodes as recoveryCode}<div class="rounded border border-neutral-300 bg-white p-2 text-center select-all">{recoveryCode}</div>{/each}</div>
                    <div class="mt-4 flex flex-wrap gap-2"><button type="button" onclick={copyAllRecoveryCodes} disabled={copyProcessing} aria-label="Salin semua recovery codes" class="inline-flex items-center gap-1 rounded-lg border-2 border-black bg-white px-3 py-1.5 text-xs font-black disabled:cursor-not-allowed disabled:opacity-50"><Copy class="size-3.5" aria-hidden="true" />{copyProcessing ? 'Menyalin...' : copyCodesSuccess ? 'Tersalin!' : 'Salin Semua'}</button><button type="button" onclick={downloadRecoveryCodes} class="rounded-lg border-2 border-black bg-yellow-400 px-3 py-1.5 text-xs font-black">Unduh .txt</button></div>
                    <label class="mt-5 flex items-start gap-2 text-xs font-bold"><input type="checkbox" bind:checked={recoveryCodesAcknowledged} class="mt-0.5 size-4 accent-yellow-400" /> Saya sudah menyimpan recovery codes di tempat yang aman.</label>
                {/if}
                <div class="mt-5 flex justify-end border-t-2 border-black pt-4"><Button onclick={() => (setupStep = 'idle')} variant="yellow" size="sm" disabled={!recoveryCodesAcknowledged}>Selesai</Button></div>
            {/if}

            {#if requestError || copyError}<p class="mt-4 rounded-lg border-2 border-red-600 bg-red-50 p-3 text-xs font-black text-red-700">{requestError || copyError}</p>{/if}
        </div>
    {/if}
</div>

{#if recoveryCodesVisible && localRecoveryCodes}
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-xs" role="presentation">
        <div class="w-full max-w-md rounded-xl border-[3px] border-black bg-white p-6 shadow-[6px_6px_0px_0px_#000]">
            <h3 class="text-lg font-black">Kode Pemulihan Cadangan</h3>
            <p class="mt-1 text-xs font-bold text-neutral-600">Simpan kode ini di tempat yang aman.</p>
            <div class="mt-4 grid grid-cols-2 gap-2 rounded-lg border-2 border-black bg-neutral-100 p-4 font-mono text-xs font-black">{#each localRecoveryCodes as recoveryCode}<div class="rounded border border-neutral-300 bg-white p-2 text-center select-all">{recoveryCode}</div>{/each}</div>
            <div class="mt-5 flex flex-wrap justify-between gap-2 border-t-2 border-black pt-4"><div class="flex gap-2"><button type="button" onclick={copyAllRecoveryCodes} disabled={copyProcessing} aria-label="Salin semua recovery codes" class="inline-flex items-center gap-1 rounded-lg border-2 border-black bg-white px-3 py-1.5 text-xs font-black disabled:cursor-not-allowed disabled:opacity-50">{#if copyCodesSuccess}<Check class="size-3.5 text-emerald-600" strokeWidth={3} aria-hidden="true" />{:else}<Copy class="size-3.5" aria-hidden="true" />{/if}{copyProcessing ? 'Menyalin...' : copyCodesSuccess ? 'Tersalin!' : 'Salin Semua'}</button><button type="button" onclick={downloadRecoveryCodes} class="rounded-lg border-2 border-black bg-yellow-400 px-3 py-1.5 text-xs font-black">Unduh .txt</button></div><Button onclick={() => (recoveryCodesVisible = false)} variant="yellow" size="sm">Selesai</Button></div>
        </div>
    </div>
{/if}
