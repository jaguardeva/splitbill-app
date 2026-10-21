<script lang="ts">
import { onMount, type Snippet } from 'svelte';
import { Link, page, router } from '@inertiajs/svelte';
import AdminButton from '@/components/admin/AdminButton.svelte';
import AdminBadge from '@/components/admin/AdminBadge.svelte';
import { logout as logoutRoute } from '@/routes';

let {
    title = 'Admin Panel',
    subtitle,
    actions,
    children,
}: {
    title?: string;
    subtitle?: string;
    actions?: Snippet;
    children?: Snippet;
} = $props();

const user = $derived((page.props as any).auth?.user);
const currentUrl = $derived(page.url);
const flashSuccess = $derived((page.props as any).flash?.success);
const flashError = $derived((page.props as any).flash?.error);

let mobileSidebarOpen = $state(false);
let logoutModalOpen = $state(false);
let logoutProcessing = $state(false);
let profileMenuOpen = $state(false);
let profileMenuRef = $state<HTMLDivElement | undefined>(undefined);

const navigationItems = [
    {
        name: 'Dashboard',
        href: '/admin/dashboard',
        activeMatch: (url: string) => url === '/admin' || url.startsWith('/admin/dashboard'),
        icon: 'dashboard',
    },
    {
        name: 'Pengguna',
        href: '/admin/users',
        activeMatch: (url: string) => url.startsWith('/admin/users'),
        icon: 'users',
    },
    {
        name: 'Semua Tagihan',
        href: '/admin/calculations',
        activeMatch: (url: string) => url.startsWith('/admin/calculations'),
        icon: 'receipt',
    },
];

function handleLogout() {
    logoutProcessing = true;
    router.post(logoutRoute.url(), {}, {
        onFinish: () => {
            logoutProcessing = false;
            logoutModalOpen = false;
        },
    });
}

function handleProfileKeydown(e: KeyboardEvent) {
    if (e.key === 'Escape' && profileMenuOpen) {
        profileMenuOpen = false;
    }
}

onMount(() => {
    function handlePointerDown(e: PointerEvent) {
        if (profileMenuRef && !profileMenuRef.contains(e.target as Node)) {
            profileMenuOpen = false;
        }
    }

    document.addEventListener('pointerdown', handlePointerDown);
    return () => {
        document.removeEventListener('pointerdown', handlePointerDown);
    };
});
</script>

<svelte:head>
    <title>{title} — Admin SplitBill</title>
</svelte:head>

<div class="flex min-h-screen bg-slate-50 font-admin text-slate-900 antialiased">
    <!-- Desktop Sidebar -->
    <aside class="hidden w-64 shrink-0 flex-col border-r border-slate-200/90 bg-white md:flex">
        <!-- Brand / Header -->
        <div class="flex h-16 items-center justify-between border-b border-slate-100 px-5">
            <Link href="/admin/dashboard" class="flex items-center gap-2.5">
                <div class="flex size-9 items-center justify-center rounded-lg bg-blue-600 text-yellow-300 shadow-xs">
                    <svg class="size-5 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
                <div class="flex flex-col">
                    <span class="text-base font-bold tracking-tight text-slate-900 leading-none">SplitBill</span>
                    <span class="mt-0.5 text-[10px] font-semibold uppercase tracking-wider text-blue-600">Admin Console</span>
                </div>
            </Link>
            <AdminBadge variant="blue" size="sm">Admin</AdminBadge>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 space-y-1 px-3 py-4">
            <p class="px-3 pb-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">Navigasi Utama</p>
            {#each navigationItems as item}
                {@const isActive = item.activeMatch(currentUrl)}
                <Link
                    href={item.href}
                    class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors cursor-pointer {isActive
                        ? 'bg-blue-50 text-blue-700 font-semibold border border-blue-200/80 shadow-xs'
                        : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'}"
                >
                    {#if item.icon === 'dashboard'}
                        <svg class="size-4.5 shrink-0 transition-colors {isActive ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600'}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                    {:else if item.icon === 'users'}
                        <svg class="size-4.5 shrink-0 transition-colors {isActive ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600'}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    {:else if item.icon === 'receipt'}
                        <svg class="size-4.5 shrink-0 transition-colors {isActive ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600'}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                    {/if}
                    <span>{item.name}</span>
                </Link>
            {/each}

            <div class="my-4 border-t border-slate-100 pt-3">
                <p class="px-3 pb-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">Pintas</p>
                <div class="space-y-1">
                    <Link
                        href="/calculator"
                        class="group flex items-center justify-between rounded-lg px-3 py-2 text-sm font-medium text-slate-700 hover:bg-amber-50 hover:text-amber-800 transition-colors cursor-pointer"
                    >
                        <span class="flex items-center gap-3">
                            <svg class="size-4.5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            <span>Buka Kalkulator</span>
                        </span>
                        <span class="rounded bg-amber-100 px-1.5 py-0.5 text-[10px] font-bold text-amber-700">Pintas</span>
                    </Link>

                    <Link
                        href="/calculations"
                        class="group flex items-center justify-between rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-colors cursor-pointer"
                    >
                        <span class="flex items-center gap-3">
                            <svg class="size-4.5 text-slate-400 group-hover:text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                            <span>Mode Pengguna</span>
                        </span>
                        <span class="text-xs text-slate-400">↗</span>
                    </Link>
                </div>
            </div>
        </nav>

        <!-- Sidebar Footer / Current Admin -->
        <div class="border-t border-slate-100 p-4">
            <div class="flex items-center justify-between">
                <div class="min-w-0 pr-2">
                    <p class="truncate text-xs font-semibold text-slate-900">{user?.name || 'Administrator'}</p>
                    <p class="truncate text-[11px] text-slate-500">{user?.email || ''}</p>
                </div>
                <button
                    type="button"
                    onclick={() => (logoutModalOpen = true)}
                    title="Keluar"
                    class="rounded-md p-1.5 text-slate-400 hover:bg-slate-100 hover:text-rose-600 transition-colors cursor-pointer"
                >
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </button>
            </div>
        </div>
    </aside>

    <!-- Mobile Drawer Overlay -->
    {#if mobileSidebarOpen}
        <div
            role="presentation"
            onclick={() => (mobileSidebarOpen = false)}
            class="fixed inset-0 z-40 bg-slate-900/40 backdrop-blur-xs md:hidden"
        ></div>

        <aside class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col bg-white shadow-xl md:hidden">
            <div class="flex h-16 items-center justify-between border-b border-slate-100 px-5">
                <Link href="/admin/dashboard" class="flex items-center gap-2.5">
                    <div class="flex size-9 items-center justify-center rounded-lg bg-blue-600 text-yellow-300 shadow-xs">
                        <svg class="size-5 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-base font-bold tracking-tight text-slate-900 leading-none">SplitBill</span>
                        <span class="mt-0.5 text-[10px] font-semibold uppercase tracking-wider text-blue-600">Admin Console</span>
                    </div>
                </Link>
                <button
                    type="button"
                    onclick={() => (mobileSidebarOpen = false)}
                    aria-label="Tutup menu sidebar"
                    class="rounded-md p-1.5 text-slate-400 hover:bg-slate-100 text-slate-700 cursor-pointer"
                >
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <nav class="flex-1 space-y-1 px-3 py-4">
                {#each navigationItems as item}
                    {@const isActive = item.activeMatch(currentUrl)}
                    <Link
                        href={item.href}
                        onclick={() => (mobileSidebarOpen = false)}
                        class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {isActive
                            ? 'bg-blue-50 text-blue-700 font-semibold'
                            : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'}"
                    >
                        <span>{item.name}</span>
                    </Link>
                {/each}
                <div class="my-4 border-t border-slate-100 pt-3">
                    <p class="px-3 pb-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">Pintas</p>
                    <div class="space-y-1">
                        <Link
                            href="/calculator"
                            onclick={() => (mobileSidebarOpen = false)}
                            class="flex items-center justify-between rounded-lg px-3 py-2 text-sm font-medium text-slate-700 hover:bg-amber-50"
                        >
                            <span class="flex items-center gap-3">
                                <svg class="size-4.5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                <span>Buka Kalkulator</span>
                            </span>
                            <span class="rounded bg-amber-100 px-1.5 py-0.5 text-[10px] font-bold text-amber-700">Pintas</span>
                        </Link>
                        <Link
                            href="/calculations"
                            onclick={() => (mobileSidebarOpen = false)}
                            class="flex items-center justify-between rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100"
                        >
                            <span class="flex items-center gap-3">
                                <svg class="size-4.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                                <span>Mode Pengguna</span>
                            </span>
                            <span>↗</span>
                        </Link>
                    </div>
                </div>
            </nav>

            <div class="border-t border-slate-100 p-4">
                <p class="text-xs font-semibold text-slate-900">{user?.name}</p>
                <p class="text-[11px] text-slate-500">{user?.email}</p>
                <button
                    type="button"
                    onclick={() => (logoutModalOpen = true)}
                    class="mt-3 flex w-full items-center justify-center gap-2 rounded-lg border border-rose-200 bg-rose-50 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-100 cursor-pointer"
                >
                    Keluar Sesi
                </button>
            </div>
        </aside>
    {/if}

    <!-- Main Content Area -->
    <div class="flex flex-1 flex-col overflow-hidden">
        <!-- Topbar -->
        <header class="sticky top-0 z-30 flex min-h-14 sm:h-16 shrink-0 items-center justify-between border-b border-slate-200/90 bg-white/95 backdrop-blur-xs px-3 sm:px-6 lg:px-8">
            <div class="flex items-center gap-2 sm:gap-3 min-w-0 flex-1 pr-2">
                <button
                    type="button"
                    onclick={() => (mobileSidebarOpen = true)}
                    aria-label="Toggle menu mobile"
                    class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 md:hidden cursor-pointer shrink-0"
                >
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <div class="min-w-0 flex-1">
                    <h1 class="truncate text-sm sm:text-base md:text-lg font-bold tracking-tight text-slate-900 leading-snug" title={title}>{title}</h1>
                    {#if subtitle}
                        <p class="hidden sm:block truncate text-xs text-slate-500 leading-normal">{subtitle}</p>
                    {/if}
                </div>
            </div>

            <!-- Topbar Right Actions -->
            <div class="flex items-center gap-1.5 sm:gap-2.5 shrink-0">
                {#if actions}
                    <div class="hidden md:flex items-center gap-2">
                        {@render actions()}
                    </div>
                {/if}

                <div class="hidden md:block h-5 w-px bg-slate-200"></div>

                <div class="hidden md:block">
                    <AdminButton
                        href="/calculator"
                        variant="warning"
                        size="sm"
                    >
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        <span>Buka Kalkulator</span>
                    </AdminButton>
                </div>

                <!-- Interactive User Profile Dropdown -->
                <div
                    role="presentation"
                    bind:this={profileMenuRef}
                    onkeydown={handleProfileKeydown}
                    class="relative inline-block text-left"
                >
                    <button
                        type="button"
                        onclick={() => (profileMenuOpen = !profileMenuOpen)}
                        aria-expanded={profileMenuOpen}
                        aria-haspopup="menu"
                        class="flex items-center rounded-full p-0.5 text-slate-700 transition-colors hover:ring-2 hover:ring-blue-500/30 focus:outline-hidden focus:ring-2 focus:ring-blue-500/30 cursor-pointer"
                        title={user?.name || 'Profil Administrator'}
                    >
                        <div class="flex size-8 items-center justify-center rounded-full bg-blue-600 font-bold text-xs text-white shadow-xs hover:bg-blue-700 transition-colors">
                            {user?.name ? user.name.charAt(0).toUpperCase() : 'A'}
                        </div>
                    </button>

                    {#if profileMenuOpen}
                        <div
                            role="menu"
                            tabindex="-1"
                            class="absolute right-0 top-[calc(100%+0.5rem)] z-50 w-60 rounded-xl border border-slate-200/90 bg-white p-1.5 shadow-xl ring-1 ring-black/5 focus:outline-hidden"
                        >
                            <!-- User Info Header -->
                            <div class="border-b border-slate-100 px-3 py-2.5">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="truncate text-xs font-bold text-slate-900">{user?.name || 'Administrator'}</p>
                                    <AdminBadge variant="blue" size="sm">Admin</AdminBadge>
                                </div>
                                <p class="truncate text-[11px] text-slate-500 mt-0.5">{user?.email || ''}</p>
                            </div>

                            <!-- Menu Items -->
                            <div class="py-1">
                                <Link
                                    href="/calculations"
                                    onclick={() => (profileMenuOpen = false)}
                                    class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-colors"
                                >
                                    <span class="flex items-center gap-2.5">
                                        <svg class="size-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                        <span>Mode Pengguna</span>
                                    </span>
                                    <span class="text-[10px] text-slate-400">↗</span>
                                </Link>
                            </div>

                            <!-- Logout Item -->
                            <div class="border-t border-slate-100 pt-1">
                                <button
                                    type="button"
                                    onclick={() => {
                                        profileMenuOpen = false;
                                        logoutModalOpen = true;
                                    }}
                                    class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-left text-xs font-medium text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer"
                                >
                                    <svg class="size-4 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                    <span>Keluar Sesi</span>
                                </button>
                            </div>
                        </div>
                    {/if}
                </div>
            </div>
        </header>

        <!-- Main Body -->
        <main class="flex-1 overflow-y-auto p-3.5 sm:p-6 lg:p-8">

            <!-- Flash Notifications -->
            {#if flashSuccess}
                <div class="mb-6 flex items-start gap-3 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800 shadow-xs">
                    <svg class="mt-0.5 size-5 shrink-0 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="flex-1 font-medium">{flashSuccess}</div>
                </div>
            {/if}

            {#if flashError}
                <div class="mb-6 flex items-start gap-3 rounded-lg border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800 shadow-xs">
                    <svg class="mt-0.5 size-5 shrink-0 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="flex-1 font-medium">{flashError}</div>
                </div>
            {/if}

            <!-- Page Content Slot -->
            {@render children?.()}
        </main>
    </div>
</div>

<!-- Logout Confirmation Modal -->
{#if logoutModalOpen}
    <div
        role="presentation"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-xs"
    >
        <div class="w-full max-w-sm rounded-lg border border-slate-200 bg-white p-6 shadow-xl">
            <h3 class="text-base font-bold text-slate-900">Konfirmasi Keluar Sesi</h3>
            <p class="mt-2 text-sm text-slate-500">
                Apakah Anda yakin ingin mengakhiri sesi Administrator Anda saat ini?
            </p>
            <div class="mt-6 flex items-center justify-end gap-3">
                <AdminButton
                    variant="secondary"
                    size="sm"
                    disabled={logoutProcessing}
                    onclick={() => (logoutModalOpen = false)}
                >
                    Batal
                </AdminButton>
                <AdminButton
                    variant="danger"
                    size="sm"
                    disabled={logoutProcessing}
                    onclick={handleLogout}
                >
                    {logoutProcessing ? 'Memproses...' : 'Ya, Keluar'}
                </AdminButton>
            </div>
        </div>
    </div>
{/if}
