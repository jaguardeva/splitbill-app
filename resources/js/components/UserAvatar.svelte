<script lang="ts">
import { onMount } from 'svelte';
import { Link, page } from '@inertiajs/svelte';
import { Building2, FileText, LogOut, UserRound } from '@lucide/svelte';
import Button from '@/components/ui/Button.svelte';

type User = { name: string; email: string; role?: 'admin' | 'user' };

let { user, onLogout, mobile = false }: { user: User; onLogout: () => void; mobile?: boolean } = $props();

let menuOpen = $state(false);
let avatarRoot = $state<HTMLDivElement | undefined>();
const currentUrl = $derived(page.url);
const initials = $derived(user.name.trim().split(/\s+/).filter(Boolean).slice(0, 2).map((part) => part.charAt(0).toUpperCase()).join('') || '?');
const profileIsActive = $derived(currentUrl.startsWith('/profile'));
const calculationsAreActive = $derived(currentUrl.startsWith('/calculations'));
const adminIsActive = $derived(currentUrl.startsWith('/admin'));

function closeMenu(): void {
    menuOpen = false;
}

function handleKeydown(event: KeyboardEvent): void {
    if (event.key === 'Escape') closeMenu();
}

function handleLogout(): void {
    closeMenu();
    onLogout();
}

onMount(() => {
    if (mobile) return;

    const handleOutsidePointerDown = (event: PointerEvent): void => {
        if (avatarRoot && !avatarRoot.contains(event.target as Node)) closeMenu();
    };

    document.addEventListener('pointerdown', handleOutsidePointerDown);
    return () => document.removeEventListener('pointerdown', handleOutsidePointerDown);
});
</script>

{#if mobile}
    <div class="flex w-full flex-col gap-2.5">
        <div class="flex w-full items-center justify-between gap-3">
            <div class="min-w-0">
                <p class="truncate text-sm font-black text-black">{user.name}</p>
                <p class="truncate text-xs font-bold text-neutral-600">{user.email}</p>
                {#if user.role === 'admin'}
                    <span class="mt-0.5 inline-block rounded border border-black bg-blue-100 px-1.5 py-0.2 text-[9px] font-black uppercase text-blue-800">Administrator</span>
                {/if}
            </div>
            <Button onclick={onLogout} variant="red" size="sm" class="shrink-0 gap-1.5">
                <LogOut class="size-4" aria-hidden="true" />
                <span>Keluar</span>
            </Button>
        </div>
    </div>
{:else}
    <div bind:this={avatarRoot} role="presentation" class="relative" onkeydown={handleKeydown}>
        <button type="button" aria-label="Buka menu akun" aria-expanded={menuOpen} aria-haspopup="menu" onclick={() => (menuOpen = !menuOpen)} class="neo-btn flex size-11 items-center justify-center rounded-full border-2 border-black bg-yellow-400 text-sm font-black text-black shadow-[3px_3px_0px_0px_#000] transition hover:bg-blue-600 hover:text-white active:translate-x-0.5 active:translate-y-0.5">{initials}</button>

        {#if menuOpen}
            <div role="menu" aria-label="Menu akun" class="absolute right-0 top-[calc(100%+0.75rem)] z-50 w-64 rounded-lg border-2 border-black bg-white p-3 text-black shadow-[4px_4px_0px_0px_#000]">
                <div class="mb-3 border-b-2 border-black pb-3"><p class="truncate text-sm font-black">{user.name}</p><p class="truncate text-xs font-bold text-neutral-600">{user.email}</p>{#if user.role === 'admin'}<span class="mt-1 inline-block rounded border border-black bg-blue-100 px-2 py-0.5 text-[10px] font-black uppercase text-blue-800">Administrator</span>{/if}</div>

                <div class="space-y-1.5">
                    <Link href="/profile#profile" onclick={closeMenu} aria-current={profileIsActive ? 'page' : undefined} role="menuitem" class="flex w-full items-center gap-2.5 rounded-lg border-2 px-3 py-2 text-xs font-black transition {profileIsActive ? 'border-black bg-yellow-400 shadow-[2px_2px_0px_0px_#000]' : 'border-transparent hover:border-black hover:bg-neutral-100'}"><UserRound class="size-4" aria-hidden="true" /><span>Profil & Keamanan</span></Link>
                    <Link href="/calculations" onclick={closeMenu} aria-current={calculationsAreActive ? 'page' : undefined} role="menuitem" class="flex w-full items-center gap-2.5 rounded-lg border-2 px-3 py-2 text-xs font-black transition {calculationsAreActive ? 'border-black bg-yellow-400 shadow-[2px_2px_0px_0px_#000]' : 'border-transparent hover:border-black hover:bg-neutral-100'}"><FileText class="size-4" aria-hidden="true" /><span>Riwayat Tagihan</span></Link>
                    {#if user.role === 'admin'}
                        <Link href="/admin/dashboard" onclick={closeMenu} aria-current={adminIsActive ? 'page' : undefined} role="menuitem" aria-label="Buka Panel Admin" class="flex w-full items-center gap-2.5 rounded-lg border-2 px-3 py-2 text-xs font-black transition {adminIsActive ? 'border-black bg-blue-700 text-white shadow-[3px_3px_0px_0px_#000]' : 'border-blue-600 bg-blue-600 text-white shadow-[2px_2px_0px_0px_#000] hover:bg-blue-700 hover:shadow-[3px_3px_0px_0px_#000]'}">
                            <Building2 class="size-4 shrink-0" aria-hidden="true" />
                            <span class="flex-1 text-left">Panel Admin</span>
                        </Link>
                    {/if}
                </div>

                <div class="mt-2 border-t-2 border-black pt-2">
                    <Button variant="red" size="sm" role="menuitem" onclick={handleLogout} class="w-full justify-start gap-2.5 text-left">
                        <LogOut class="size-4" aria-hidden="true" />
                        <span>Keluar</span>
                    </Button>
                </div>
            </div>
        {/if}
    </div>
{/if}
