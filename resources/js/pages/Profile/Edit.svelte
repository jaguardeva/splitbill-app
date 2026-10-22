<script lang="ts">
import { Link, page, router } from '@inertiajs/svelte';
import { CircleAlert, LockKeyhole, ShieldCheck, UserRound } from '@lucide/svelte';
import AppHead from '@/components/AppHead.svelte';
import Navbar from '@/components/Navbar.svelte';
import Footer from '@/components/Footer.svelte';
import TwoFactorSetupWizard from '@/components/profile/TwoFactorSetupWizard.svelte';
import Button from '@/components/ui/Button.svelte';
import Input from '@/components/ui/Input.svelte';
import PasswordInput from '@/components/ui/PasswordInput.svelte';
import Toast from '@/components/ui/Toast.svelte';
import profile from '@/routes/profile';

let {
    mustVerifyEmail = false,
    isEmailVerified = false,
    twoFactorEnabled = false,
    qrCodeSvg = null,
    setupKey = null,
    status = null,
}: {
    mustVerifyEmail?: boolean;
    isEmailVerified?: boolean;
    twoFactorEnabled?: boolean;
    qrCodeSvg?: string | null;
    setupKey?: string | null;
    status?: string | null;
} = $props();

const user = $derived((page.props as any).auth?.user);
const successMessage = $derived((page.props as any).flash?.success || (status === 'two-factor-authentication-confirmed' ? 'Autentikasi dua faktor berhasil diaktifkan.' : status === 'two-factor-authentication-disabled' ? 'Autentikasi dua faktor telah dinonaktifkan.' : null));
const errorMessage = $derived((page.props as any).flash?.error);
let activeTab = $state<'profile' | 'password' | 'two-factor'>('profile');
let profileName = $state('');
let profileProcessing = $state(false);
let profileErrors = $state<Record<string, string>>({});
let currentPassword = $state('');
let newPassword = $state('');
let newPasswordConfirmation = $state('');
let passwordProcessing = $state(false);
let passwordErrors = $state<Record<string, string>>({});

$effect(() => {
    if (user && !profileName) profileName = user.name || '';

    if (typeof window !== 'undefined') {
        const hash = window.location.hash.replace('#', '');
        if (hash === 'profile' || hash === 'password' || hash === 'two-factor') {
            activeTab = hash;
        } else if (qrCodeSvg || (status && status.includes('two-factor'))) {
            activeTab = 'two-factor';
        }
    }
});

function setTab(tab: 'profile' | 'password' | 'two-factor'): void {
    activeTab = tab;
    if (typeof window !== 'undefined') {
        window.history.replaceState(null, '', `#${tab}`);
    }
}

function handleUpdateProfile(event: Event): void {
    event.preventDefault();
    profileProcessing = true;
    profileErrors = {};
    router.patch(profile.update.url(), { name: profileName }, { preserveScroll: true, onError: (errors) => (profileErrors = errors), onFinish: () => (profileProcessing = false) });
}

function handleUpdatePassword(event: Event): void {
    event.preventDefault();
    passwordProcessing = true;
    passwordErrors = {};
    router.put(profile.password.update.url(), { current_password: currentPassword, password: newPassword, password_confirmation: newPasswordConfirmation }, {
        preserveScroll: true,
        onSuccess: () => { currentPassword = ''; newPassword = ''; newPasswordConfirmation = ''; },
        onError: (errors) => (passwordErrors = errors),
        onFinish: () => (passwordProcessing = false),
    });
}
</script>

<AppHead title="Profil Saya &amp; Keamanan — SplitBill" />

<div class="flex min-h-screen flex-col bg-[#fffdf0] text-black antialiased selection:bg-yellow-400">
    <Navbar {user} />
    <main class="flex-1 px-4 py-8 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-4xl space-y-6">
            {#if successMessage}<Toast message={successMessage} variant="success" />{/if}
            {#if errorMessage}<Toast message={errorMessage} variant="error" />{/if}

            <div class="rounded-xl border-[3px] border-black bg-white p-5 shadow-[4px_4px_0px_0px_#000] sm:p-7">
                <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                    <div class="flex items-center gap-4">
                        <div class="flex size-14 shrink-0 items-center justify-center rounded-full border-2 border-black bg-yellow-400 text-xl font-black shadow-[3px_3px_0px_0px_#000]">{user?.name ? user.name.charAt(0).toUpperCase() : 'U'}</div>
                        <div><h1 class="text-xl font-black tracking-tight sm:text-2xl">{user?.name || 'Profil Pengguna'}</h1><p class="text-xs font-bold text-neutral-600 sm:text-sm">{user?.email || ''}</p></div>
                    </div>
                    {#if twoFactorEnabled}<span class="inline-flex items-center gap-1.5 rounded-lg border-2 border-black bg-emerald-100 px-3 py-1 text-xs font-black text-emerald-900 shadow-[2px_2px_0px_0px_#000]"><span class="size-2 rounded-full bg-emerald-600"></span>2FA Aktif</span>{:else}<span class="inline-flex items-center gap-1.5 rounded-lg border-2 border-black bg-neutral-100 px-3 py-1 text-xs font-black text-neutral-700 shadow-[2px_2px_0px_0px_#000]"><span class="size-2 rounded-full bg-neutral-400"></span>2FA Belum Aktif</span>{/if}
                </div>
                <div class="mt-6 flex flex-wrap gap-2 border-t-2 border-black pt-4">
                    <button type="button" onclick={() => setTab('profile')} class="inline-flex items-center gap-2 rounded-lg border-2 px-4 py-2 text-xs font-black sm:text-sm {activeTab === 'profile' ? 'border-black bg-yellow-400 shadow-[2px_2px_0px_0px_#000]' : 'border-transparent hover:border-black hover:bg-neutral-100'}"><UserRound class="size-4" aria-hidden="true" />Informasi Profil</button>
                    <button type="button" onclick={() => setTab('password')} class="inline-flex items-center gap-2 rounded-lg border-2 px-4 py-2 text-xs font-black sm:text-sm {activeTab === 'password' ? 'border-black bg-yellow-400 shadow-[2px_2px_0px_0px_#000]' : 'border-transparent hover:border-black hover:bg-neutral-100'}"><LockKeyhole class="size-4" aria-hidden="true" />Kata Sandi</button>
                    <button type="button" onclick={() => setTab('two-factor')} class="inline-flex items-center gap-2 rounded-lg border-2 px-4 py-2 text-xs font-black sm:text-sm {activeTab === 'two-factor' ? 'border-black bg-yellow-400 shadow-[2px_2px_0px_0px_#000]' : 'border-transparent hover:border-black hover:bg-neutral-100'}"><ShieldCheck class="size-4" aria-hidden="true" />Autentikasi Dua Faktor</button>
                </div>
            </div>

            {#if activeTab === 'profile'}
                <div class="rounded-xl border-[3px] border-black bg-white p-5 shadow-[4px_4px_0px_0px_#000] sm:p-7"><div class="border-b-2 border-black pb-4"><h2 class="text-lg font-black">Informasi Profil</h2><p class="text-xs font-bold text-neutral-600 sm:text-sm">Perbarui nama tampilan akun. Alamat email tidak dapat diubah.</p></div><form onsubmit={handleUpdateProfile} class="mt-6 space-y-5"><Input id="name" label="Nama Lengkap" required bind:value={profileName} error={profileErrors.name} disabled={profileProcessing} /><div><Input id="email" type="email" label="Alamat Email" value={user?.email || ''} readonly disabled class="cursor-not-allowed bg-neutral-100" />{#if isEmailVerified}<p class="mt-2 text-xs font-bold text-emerald-700">✓ Alamat email ini telah terverifikasi.</p>{:else if mustVerifyEmail}<div class="mt-2 flex items-center justify-between rounded-lg border border-amber-300 bg-amber-50 p-2.5 text-xs font-bold text-amber-900"><span><CircleAlert class="mr-1 inline-block size-4 align-text-bottom" aria-hidden="true" />Alamat email belum terverifikasi.</span><Link href="/email/verify" class="underline hover:text-black">Verifikasi Sekarang</Link></div>{/if}</div><Button type="submit" variant="yellow" size="md" disabled={profileProcessing}>{profileProcessing ? 'Menyimpan...' : 'Simpan Perubahan Profil'}</Button></form></div>
            {:else if activeTab === 'password'}
                <div class="rounded-xl border-[3px] border-black bg-white p-5 shadow-[4px_4px_0px_0px_#000] sm:p-7"><div class="border-b-2 border-black pb-4"><h2 class="text-lg font-black">Perbarui Kata Sandi</h2><p class="text-xs font-bold text-neutral-600 sm:text-sm">Gunakan kata sandi yang kuat dan unik.</p></div><form onsubmit={handleUpdatePassword} class="mt-6 space-y-5"><PasswordInput id="current_password" label="Kata Sandi Saat Ini" required bind:value={currentPassword} error={passwordErrors.current_password} disabled={passwordProcessing} /><PasswordInput id="password" label="Kata Sandi Baru" required showStrengthMeter bind:value={newPassword} error={passwordErrors.password} disabled={passwordProcessing} /><PasswordInput id="password_confirmation" label="Konfirmasi Kata Sandi Baru" required bind:value={newPasswordConfirmation} error={passwordErrors.password_confirmation} disabled={passwordProcessing} /><Button type="submit" variant="yellow" size="md" disabled={passwordProcessing}>{passwordProcessing ? 'Menyimpan Kata Sandi...' : 'Perbarui Kata Sandi'}</Button></form></div>
            {:else}
                <div class="rounded-xl border-[3px] border-black bg-white p-5 shadow-[4px_4px_0px_0px_#000] sm:p-7"><div class="border-b-2 border-black pb-4"><h2 class="text-lg font-black">Autentikasi Dua Faktor (2FA)</h2><p class="text-xs font-bold text-neutral-600 sm:text-sm">Gunakan aplikasi authenticator untuk menghasilkan kode 6 digit saat masuk.</p></div><TwoFactorSetupWizard enabled={twoFactorEnabled} {qrCodeSvg} {setupKey} /></div>
            {/if}
        </div>
    </main>
    <Footer />
</div>
