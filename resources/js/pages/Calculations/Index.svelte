<script lang="ts">
    import { onDestroy } from "svelte";
    import AppHead from "@/components/AppHead.svelte";
    import Navbar from "@/components/Navbar.svelte";
    import Footer from "@/components/Footer.svelte";
    import Button from "@/components/ui/Button.svelte";
    import Badge from "@/components/ui/Badge.svelte";
    import ConfirmModal from "@/components/ui/ConfirmModal.svelte";
    import Toast from "@/components/ui/Toast.svelte";
    import { formatDate, formatRupiah } from "@/lib/formatters";
    import { Link, page, router } from "@inertiajs/svelte";
    import { destroy as destroyCalculation } from "@/routes/calculations";

    interface CalculationItem {
        id: string;
        title: string;
        currency: string;
        split_mode: string;
        status: string;
        grand_total_minor: number;
        participants_count: number;
        items_count: number;
        created_at: string;
        updated_at: string;
    }

    let {
        calculations,
        filters = { search: "" },
    }: {
        calculations: {
            data: CalculationItem[];
            links: Array<{
                url: string | null;
                label: string;
                active: boolean;
            }>;
            total: number;
        };
        filters?: { search?: string | null };
    } = $props();

    const user = $derived((page.props as any).auth?.user);
    const successMessage = $derived((page.props as any).flash?.success);

    let searchQuery = $state((() => filters.search || "")());
    let selectedCalculation = $state<CalculationItem | null>(null);
    let deleteModalOpen = $state(false);
    let deleteProcessing = $state(false);
    let searchDebounceTimer: ReturnType<typeof setTimeout> | undefined;

    const hasActiveSearch = $derived(searchQuery.trim() !== "");

    function scheduleSearch(e: Event) {
        searchQuery = (e.currentTarget as HTMLInputElement).value;

        if (searchDebounceTimer) {
            clearTimeout(searchDebounceTimer);
        }

        searchDebounceTimer = setTimeout(() => {
            const normalizedSearch = searchQuery.trim();

            router.get(
                "/calculations",
                normalizedSearch ? { search: normalizedSearch } : {},
                { preserveState: true, preserveScroll: true },
            );
        }, 400);
    }

    function resetSearch() {
        if (searchDebounceTimer) {
            clearTimeout(searchDebounceTimer);
            searchDebounceTimer = undefined;
        }

        searchQuery = "";

        router.get(
            "/calculations",
            {},
            {
                preserveState: true,
                preserveScroll: true,
            },
        );
    }

    onDestroy(() => {
        if (searchDebounceTimer) {
            clearTimeout(searchDebounceTimer);
        }
    });

    function openDeleteModal(calculation: CalculationItem) {
        selectedCalculation = calculation;
        deleteModalOpen = true;
    }

    function closeDeleteModal() {
        if (deleteProcessing) {
            return;
        }

        deleteModalOpen = false;
        selectedCalculation = null;
    }

    function deleteSelectedCalculation() {
        if (!selectedCalculation || deleteProcessing) {
            return;
        }

        deleteProcessing = true;

        router.delete(destroyCalculation.url(selectedCalculation.id), {
            preserveScroll: true,
            onFinish: () => {
                deleteProcessing = false;
                deleteModalOpen = false;
                selectedCalculation = null;
            },
        });
    }
</script>

<AppHead title="Riwayat Tagihan Tersimpan" />

<div class="min-h-screen bg-neutral-50 text-black flex flex-col font-sans">
    <Navbar {user} />

    <main
        class="mx-auto w-full max-w-[1240px] px-4 py-8 sm:px-6 lg:px-8 flex-1"
    >
        <!-- Header -->
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between pb-6 border-b-[3px] border-black"
        >
            <div>
                <Badge variant="yellow" size="sm" class="mb-2">
                    Database Tagihan
                </Badge>
                <h1
                    class="text-2xl font-black tracking-tight text-black sm:text-4xl"
                >
                    Riwayat Tagihan
                </h1>
                <p class="mt-1 text-xs sm:text-sm font-bold text-neutral-600">
                    Kelola dan tinjau kembali rincian perhitungan tagihan yang
                    pernah Anda simpan.
                </p>
            </div>

            <Button href="/calculator" variant="blue" size="lg">
                <svg
                    class="size-4 mr-1.5 stroke-[2.5]"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M12 4v16m8-8H4"
                    />
                </svg>
                Buat Hitungan Baru
            </Button>
        </div>

        <!-- Search Bar -->
        <div class="mt-6 flex flex-col md:flex-row max-w-md gap-2">
            <input
                type="text"
                bind:value={searchQuery}
                oninput={scheduleSearch}
                placeholder="Cari berdasarkan judul..."
                class="min-w-full rounded-lg border-2 border-black bg-white px-3.5 py-2 text-sm font-bold text-black shadow-[2px_2px_0px_0px_#000] focus:border-blue-600 focus:ring-2 focus:ring-blue-600 focus:shadow-[2px_2px_0px_0px_#000] focus:outline-none sm:flex-1"
            />
            {#if hasActiveSearch}
                <Button
                    type="button"
                    onclick={resetSearch}
                    variant="outline"
                    size="sm"
                    class="flex-1 sm:flex-none"
                >
                    <svg
                        class="mr-1.5 size-4 stroke-[2.5]"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                    Reset
                </Button>
            {/if}
        </div>

        <!-- Calculations Grid -->
        {#if calculations.data.length === 0}
            <div
                class="mt-12 rounded-xl border-2 border-dashed border-black bg-yellow-50 py-16 text-center"
            >
                <div
                    class="mx-auto flex size-14 items-center justify-center rounded-xl border-2 border-black bg-white text-black shadow-[3px_3px_0px_0px_#000]"
                >
                    <svg
                        class="size-7 stroke-[2.5]"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                        />
                    </svg>
                </div>
                <h3 class="mt-4 text-base font-black text-black">
                    Belum ada perhitungan tersimpan
                </h3>
                <p class="mt-1 text-xs font-bold text-neutral-600">
                    Mulai buat dan simpan perhitungan pertama Anda.
                </p>
                <Button
                    href="/calculator"
                    variant="blue"
                    size="sm"
                    class="mt-4 inline-flex"
                >
                    + Hitung Sekarang
                </Button>
            </div>
        {:else}
            <div
                class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3"
            >
                {#each calculations.data as item (item.id)}
                    <div
                        class="flex flex-col justify-between rounded-xl border-2 border-black bg-white p-5 shadow-[4px_4px_0px_0px_#000] transition hover:-translate-x-0.5 hover:-translate-y-0.5 hover:shadow-[6px_6px_0px_0px_#000]"
                    >
                        <div>
                            <div class="flex items-center justify-between">
                                <Badge variant="yellow" size="sm">
                                    {item.split_mode === "itemized"
                                        ? "Per Item"
                                        : "Bagi Rata"}
                                </Badge>
                                <span
                                    class="text-[11px] font-bold text-neutral-600"
                                >
                                    {formatDate(item.updated_at)}
                                </span>
                            </div>

                            <Link
                                href={`/calculations/${item.id}`}
                                class="mt-3 block group"
                            >
                                <h3
                                    class="text-lg font-black text-black line-clamp-1 group-hover:text-blue-600"
                                >
                                    {item.title}
                                </h3>
                            </Link>

                            <div
                                class="mt-2 flex items-center gap-3 text-xs font-bold text-neutral-600"
                            >
                                <span
                                    >👥 {item.participants_count} Partisipan</span
                                >
                                {#if item.split_mode === "itemized"}
                                    <span>🍽️ {item.items_count} Menu</span>
                                {/if}
                            </div>
                        </div>

                        <div class="mt-6 border-t-2 border-black pt-3">
                            <div
                                class="flex items-baseline justify-between mb-3"
                            >
                                <span
                                    class="text-xs font-bold text-neutral-600 uppercase"
                                    >Total Tagihan</span
                                >
                                <span class="text-xl font-black text-blue-600">
                                    {formatRupiah(item.grand_total_minor)}
                                </span>
                            </div>

                            <!-- Action Row -->
                            <div
                                class="grid grid-cols-3 gap-2 text-xs font-black"
                            >
                                <Button
                                    href={`/calculations/${item.id}`}
                                    variant="white"
                                    size="sm"
                                    class="w-full text-center"
                                >
                                    Rincian
                                </Button>

                                <Button
                                    href={`/calculations/${item.id}/edit`}
                                    variant="yellow"
                                    size="sm"
                                    class="w-full text-center"
                                >
                                    Ubah
                                </Button>

                                <Button
                                    type="button"
                                    onclick={() => openDeleteModal(item)}
                                    variant="red"
                                    size="sm"
                                    class="w-full text-center"
                                >
                                    Hapus
                                </Button>
                            </div>
                        </div>
                    </div>
                {/each}
            </div>

            <!-- Pagination -->
            {#if calculations.links && calculations.links.length > 3}
                <div class="mt-8 flex justify-center gap-2">
                    {#each calculations.links as link}
                        {#if link.url}
                            <Link
                                href={link.url}
                                class="rounded-lg border-2 border-black px-3 py-1.5 text-xs font-black transition {link.active
                                    ? 'bg-blue-600 text-white shadow-[2px_2px_0px_0px_#000]'
                                    : 'bg-white text-black shadow-[2px_2px_0px_0px_#000] hover:bg-yellow-300'}"
                            >
                                {@html link.label}
                            </Link>
                        {/if}
                    {/each}
                </div>
            {/if}
        {/if}
    </main>

    <Footer />

    <Toast message={successMessage} variant="success" />

    <ConfirmModal
        isOpen={deleteModalOpen}
        title="Hapus Riwayat Tagihan?"
        message={selectedCalculation
            ? `Apakah Anda yakin ingin menghapus perhitungan "${selectedCalculation.title}"? Data yang dihapus tidak dapat dipulihkan.`
            : ""}
        confirmLabel="Hapus"
        cancelLabel="Batal"
        processing={deleteProcessing}
        onConfirm={deleteSelectedCalculation}
        onCancel={closeDeleteModal}
    />
</div>
