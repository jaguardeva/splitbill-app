<script lang="ts">
import { Link } from '@inertiajs/svelte';
</script>

<div class="mb-6 flex justify-center">
    <Link href="/" class="flex items-center gap-2.5 transition active:translate-x-0.5 active:translate-y-0.5 cursor-pointer">
        <div class="flex size-10 items-center justify-center rounded-lg border-2 border-black bg-blue-600 text-yellow-300 shadow-[2px_2px_0px_0px_#000]">
            <svg class="size-6 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
        </div>
        <div class="flex flex-col text-left">
            <span class="text-xl font-black tracking-tight text-black">SplitBill</span>
            <span class="-mt-1 text-[10px] font-black uppercase tracking-wider text-blue-600">Hitung Patungan Adil</span>
        </div>
    </Link>
</div>
