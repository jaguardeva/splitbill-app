<script lang="ts">
import { Link, page, router } from '@inertiajs/svelte';
import Button from '@/components/ui/Button.svelte';
import ConfirmModal from '@/components/ui/ConfirmModal.svelte';
import UserAvatar from '@/components/UserAvatar.svelte';
import { logout as logoutRoute } from '@/routes';

let { user = null }: { user?: { name: string; email: string; role?: 'admin' | 'user' } | null } = $props();

let mobileMenuOpen = $state(false);
let logoutModalOpen = $state(false);
let logoutProcessing = $state(false);

const currentUrl = $derived(page.url);

function requestLogout() {
    logoutModalOpen = true;
}

function cancelLogout() {
    if (logoutProcessing) {
        return;
    }

    logoutModalOpen = false;
}

function confirmLogout() {
    if (logoutProcessing) {
        return;
    }

    logoutProcessing = true;

    router.post(logoutRoute.url(), {}, {
        onFinish: () => {
            logoutProcessing = false;
            logoutModalOpen = false;
        },
    });
}
</script>

<header class="sticky top-0 z-40 w-full border-b-[3px] border-black bg-white transition-all">
    <div class="mx-auto flex h-16 max-w-[1240px] items-center justify-between px-4 sm:px-6 lg:px-8">
        <!-- Logo -->
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

        <!-- Desktop Navigation Links -->
        <nav class="hidden md:flex items-center gap-2">
            <Link
                href="/"
                class="rounded-lg px-3.5 py-1.5 text-sm font-bold cursor-pointer transition {currentUrl === '/' || currentUrl === '/calculator' ? 'border-2 border-black bg-yellow-400 text-black shadow-[2px_2px_0px_0px_#000]' : 'border-2 border-transparent text-black hover:border-black hover:bg-neutral-100'}"
            >
                Kalkulator Cepat
            </Link>

            {#if user}
                <Link
                    href="/calculations"
                    class="rounded-lg px-3.5 py-1.5 text-sm font-bold cursor-pointer transition {currentUrl.startsWith('/calculations') ? 'border-2 border-black bg-yellow-400 text-black shadow-[2px_2px_0px_0px_#000]' : 'border-2 border-transparent text-black hover:border-black hover:bg-neutral-100'}"
                >
                    Riwayat Tagihan
                </Link>

                {#if user.role === 'admin'}
                    <Link
                        href="/admin/dashboard"
                        class="rounded-lg px-3.5 py-1.5 text-sm font-bold cursor-pointer transition {currentUrl.startsWith('/admin') ? 'border-2 border-black bg-blue-600 text-white shadow-[2px_2px_0px_0px_#000]' : 'border-2 border-black bg-blue-50 text-blue-700 hover:bg-blue-600 hover:text-white'}"
                    >
                        Panel Admin ⚡
                    </Link>
                {/if}
            {/if}
        </nav>

        <!-- User Actions / Auth -->
        <div class="hidden md:flex items-center gap-3">
            {#if user}
                <UserAvatar {user} onLogout={requestLogout} />
            {:else}
                <div class="flex items-center gap-3">
                    <Button href="/login" variant="white" size="md">
                        Masuk
                    </Button>
                    <Button href="/register" variant="blue" size="md">
                        Daftar Akun
                    </Button>
                </div>
            {/if}
        </div>

        <!-- Mobile Menu Toggle Button -->
        <button
            type="button"
            onclick={() => (mobileMenuOpen = !mobileMenuOpen)}
            aria-label="Toggle Menu"
            class="flex size-10 items-center justify-center rounded-lg border-2 border-black bg-yellow-400 text-black shadow-[2px_2px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 md:hidden cursor-pointer"
        >
            <svg class="size-6 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                {#if mobileMenuOpen}
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                {:else}
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                {/if}
            </svg>
        </button>
    </div>

    <!-- Mobile Menu Dropdown -->
    {#if mobileMenuOpen}
        <div class="border-t-2 border-black bg-white px-4 py-4 md:hidden shadow-[0_6px_0px_0px_#000]">
            <nav class="flex flex-col gap-2.5">
                <Link
                    href="/"
                    onclick={() => (mobileMenuOpen = false)}
                    class="rounded-lg border-2 border-black px-4 py-2 text-base font-bold cursor-pointer transition {currentUrl === '/' ? 'bg-yellow-400 text-black shadow-[2px_2px_0px_0px_#000]' : 'bg-white text-black'}"
                >
                    Kalkulator Cepat
                </Link>

                {#if user}
                    <Link
                        href="/calculations"
                        onclick={() => (mobileMenuOpen = false)}
                        class="rounded-lg border-2 border-black px-4 py-2 text-base font-bold cursor-pointer transition {currentUrl.startsWith('/calculations') ? 'bg-yellow-400 text-black shadow-[2px_2px_0px_0px_#000]' : 'bg-white text-black'}"
                    >
                        Riwayat Tagihan
                    </Link>

                    {#if user.role === 'admin'}
                        <Link
                            href="/admin/dashboard"
                            onclick={() => (mobileMenuOpen = false)}
                            class="rounded-lg border-2 border-black bg-blue-600 px-4 py-2 text-base font-bold text-white shadow-[2px_2px_0px_0px_#000] cursor-pointer"
                        >
                            Panel Admin ⚡
                        </Link>
                    {/if}


                    <div class="my-2 border-t-2 border-black pt-3">
                        <UserAvatar {user} onLogout={requestLogout} mobile />
                    </div>
                {:else}
                    <div class="mt-2 flex flex-col gap-2.5 border-t-2 border-black pt-3">
                        <Button
                            href="/login"
                            onclick={() => (mobileMenuOpen = false)}
                            variant="white"
                            size="lg"
                            class="w-full"
                        >
                            Masuk
                        </Button>
                        <Button
                            href="/register"
                            onclick={() => (mobileMenuOpen = false)}
                            variant="blue"
                            size="lg"
                            class="w-full"
                        >
                            Daftar Akun
                        </Button>
                    </div>
                {/if}
            </nav>
        </div>
    {/if}
</header>

<ConfirmModal
    isOpen={logoutModalOpen}
    title="Konfirmasi Keluar"
    message="Apakah Anda yakin ingin keluar dari akun?"
    confirmLabel="Keluar"
    cancelLabel="Batal"
    processingLabel="Keluar..."
    processing={logoutProcessing}
    onConfirm={confirmLogout}
    onCancel={cancelLogout}
/>
