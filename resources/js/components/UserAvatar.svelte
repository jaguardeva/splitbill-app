<script lang="ts">
import { onMount } from 'svelte';
import { Link } from '@inertiajs/svelte';
import Button from '@/components/ui/Button.svelte';

let {
    user,
    onLogout,
    mobile = false,
}: {
    user: { name: string; email: string; role?: 'admin' | 'user' };
    onLogout: () => void;
    mobile?: boolean;
} = $props();

let menuOpen = $state(false);
let avatarRoot = $state<HTMLDivElement | undefined>(undefined);

const initials = $derived(
    user.name
        .trim()
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((name) => name.charAt(0).toUpperCase())
        .join('') || '?'
);

function handleLogout() {
    menuOpen = false;
    onLogout();
}

onMount(() => {
    if (mobile) {
        return;
    }

    function handleOutsidePointerDown(event: PointerEvent) {
        if (avatarRoot && !avatarRoot.contains(event.target as Node)) {
            menuOpen = false;
        }
    }

    document.addEventListener('pointerdown', handleOutsidePointerDown);

    return () => {
        document.removeEventListener('pointerdown', handleOutsidePointerDown);
    };
});
</script>

{#if mobile}
    <div class="flex w-full items-center justify-between gap-3">
        <div class="min-w-0">
            <p class="truncate text-sm font-black text-black">{user.name}</p>
            <p class="truncate text-xs font-bold text-neutral-600">{user.email}</p>
        </div>

        <Button
            onclick={handleLogout}
            variant="red"
            size="sm"
            class="shrink-0"
        >
            Keluar
        </Button>
    </div>
{:else}
    <div bind:this={avatarRoot} class="relative">
        <button
            type="button"
            aria-label="Buka menu akun"
            aria-expanded={menuOpen}
            onclick={() => (menuOpen = !menuOpen)}
            class="neo-btn flex size-11 items-center justify-center rounded-full border-2 border-black bg-yellow-400 text-sm font-black text-black shadow-[3px_3px_0px_0px_#000] transition hover:bg-blue-600 hover:text-white active:translate-x-0.5 active:translate-y-0.5"
        >
            {initials}
        </button>

        {#if menuOpen}
            <div
                class="absolute right-0 top-[calc(100%+0.75rem)] z-50 w-64 rounded-lg border-2 border-black bg-white p-3 text-black shadow-[4px_4px_0px_0px_#000]"
            >
                <div class="mb-3 border-b-2 border-black pb-3">
                    <p class="truncate text-sm font-black">{user.name}</p>
                    <p class="truncate text-xs font-bold text-neutral-600">{user.email}</p>
                    {#if user.role === 'admin'}
                        <span class="mt-1 inline-block rounded border border-black bg-blue-100 px-2 py-0.5 text-[10px] font-black uppercase text-blue-800">
                            Administrator
                        </span>
                    {/if}
                </div>

                <div class="space-y-2">
                    {#if user.role === 'admin'}
                        <Link
                            href="/admin/dashboard"
                            onclick={() => (menuOpen = false)}
                            class="flex w-full items-center justify-center rounded-lg border-2 border-black bg-blue-600 py-2 text-xs font-black text-white shadow-[2px_2px_0px_0px_#000] hover:bg-blue-700 cursor-pointer"
                        >
                            Panel Admin ⚡
                        </Link>
                    {/if}

                    <Button
                        onclick={handleLogout}
                        variant="red"
                        size="sm"
                        class="w-full"
                    >
                        Keluar
                    </Button>
                </div>
            </div>
        {/if}
    </div>
{/if}

