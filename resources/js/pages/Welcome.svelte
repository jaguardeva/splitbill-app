<script lang="ts">
import AppHead from '@/components/AppHead.svelte';
import Navbar from '@/components/Navbar.svelte';
import Footer from '@/components/Footer.svelte';
import CalculatorWorkspace from '@/components/calculator/CalculatorWorkspace.svelte';
import { page } from '@inertiajs/svelte';

import FaqSection from '@/components/FaqSection.svelte';
import Badge from '@/components/ui/Badge.svelte';
import Card from '@/components/ui/Card.svelte';

const user = $derived((page.props as any).auth?.user);
</script>

<AppHead title="Hitung & Bagi Tagihan Patungan Adil" />

<div class="min-h-screen bg-neutral-50 text-black flex flex-col font-sans">
    <Navbar {user} />

    <!-- Hero Section (Neo-Brutalist Dynamic Banner) -->
    <section class="border-b-[3px] border-black bg-yellow-400 overflow-hidden">
        <div class="mx-auto max-w-[1240px] px-4 py-10 sm:px-6 sm:py-16 lg:px-8 lg:py-20">
            <div class="flex flex-col gap-10 lg:flex-row lg:items-center lg:gap-12">
                <!-- Left: Text Content -->
                <div class="flex-1 text-center lg:text-left">
                    <div class="inline-flex items-center gap-2 rounded-lg border-2 border-black bg-white px-3.5 py-1.5 text-xs font-black uppercase tracking-wider text-black shadow-[2px_2px_0px_0px_#000]">
                        <span class="size-2 rounded-full border border-black bg-blue-600 animate-pulse"></span>
                        Gratis & Transparan
                    </div>

                    <h1 class="mx-auto mt-5 max-w-[22rem] text-3xl font-black leading-[1.2] tracking-tight text-black sm:max-w-none sm:text-4xl sm:leading-[1.1] lg:text-5xl xl:text-6xl">
                        Bagi Tagihan
                        <span class="relative inline-block">
                            <span class="relative z-10 rounded-xl border-2 border-black bg-blue-600 px-3 py-0.5 text-yellow-300 shadow-[4px_4px_0px_0px_#000]">
                                Patungan
                            </span>
                        </span>
                        <br class="hidden sm:block" />
                        Adil Hingga
                        <span class="inline-block border-b-4 border-black">Rupiah Terakhir!</span>
                    </h1>

                    <p class="mx-auto mt-5 max-w-xl text-sm font-bold leading-relaxed text-black/80 sm:text-base lg:mx-0">
                        Hitung struk restoran, kafe, atau liburan bersama teman tanpa drama. Pajak, diskon, dan service charge dialokasikan proporsional dengan presisi matematika.
                    </p>

                    <div class="mt-7 flex flex-col items-center justify-center gap-3 sm:flex-row lg:justify-start">
                        <a
                            href="#calculator-section"
                            class="neo-btn neo-btn-blue rounded-xl px-5 py-2.5 text-sm sm:text-base sm:px-6 sm:py-3 font-black min-h-[42px] sm:min-h-[48px] w-full sm:w-auto cursor-pointer"
                        >
                            Mulai Hitung Sekarang ↓
                        </a>
                        <a
                            href="#features-section"
                            class="neo-btn neo-btn-white rounded-xl px-5 py-2.5 text-sm sm:text-base sm:px-6 sm:py-3 font-black min-h-[42px] sm:min-h-[48px] w-full sm:w-auto cursor-pointer"
                        >
                            Lihat Fitur
                        </a>
                    </div>
                </div>

                <!-- Right: Decorative Floating Cards (Desktop only) -->
                <div class="hidden lg:flex flex-col items-center gap-4 relative w-[380px] shrink-0">
                    <!-- Card 1: Receipt mockup -->
                    <div class="relative -rotate-3 rounded-xl border-[3px] border-black bg-white p-5 shadow-[6px_6px_0px_0px_#000] w-72">
                        <div class="flex items-center gap-2 border-b-2 border-black pb-3">
                            <div class="size-8 rounded-lg border-2 border-black bg-blue-600 flex items-center justify-center">
                                <svg class="size-4 text-yellow-300 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                            </div>
                            <span class="font-black text-sm">Makan Siang Tim</span>
                        </div>
                        <div class="mt-3 space-y-2 text-xs font-bold text-neutral-700">
                            <div class="flex justify-between"><span>Nasi Goreng ×2</span><span class="text-black font-black">Rp 50.000</span></div>
                            <div class="flex justify-between"><span>Es Teh ×3</span><span class="text-black font-black">Rp 15.000</span></div>
                            <div class="flex justify-between"><span>Ayam Bakar ×1</span><span class="text-black font-black">Rp 35.000</span></div>
                            <div class="border-t-2 border-dashed border-black pt-2 flex justify-between text-sm">
                                <span class="font-black">Total</span>
                                <span class="font-black text-blue-600">Rp 100.000</span>
                            </div>
                        </div>
                    </div>

                    <!-- Card 2: Per-person result -->
                    <div class="relative rotate-2 -mt-4 ml-16 rounded-xl border-[3px] border-black bg-blue-600 p-4 shadow-[6px_6px_0px_0px_#000] w-56 text-white">
                        <span class="text-[10px] font-black uppercase tracking-wider text-yellow-300">Per Orang</span>
                        <div class="mt-2 text-2xl font-black">Rp 33.334</div>
                        <div class="mt-1 text-xs font-bold text-white/80">3 Partisipan · Bagi Rata</div>
                    </div>

                    <!-- Decorative geometric elements -->
                    <div class="absolute -top-4 -right-6 size-12 rounded-full border-[3px] border-black bg-white shadow-[3px_3px_0px_0px_#000]"></div>
                    <div class="absolute bottom-8 -left-8 size-8 border-[3px] border-black bg-yellow-300 shadow-[2px_2px_0px_0px_#000] rotate-12"></div>
                </div>
            </div>

            <!-- Mobile: Compact stat badges -->
            <div class="mt-8 flex flex-wrap items-center justify-center gap-2.5 sm:gap-3 lg:hidden">
                <div class="inline-flex items-center gap-1.5 rounded-lg border-2 border-black bg-white px-3 py-1.5 text-xs font-black shadow-[2px_2px_0px_0px_#000]">
                    ✓ Presisi Matematika
                </div>
                <div class="inline-flex items-center gap-1.5 rounded-lg border-2 border-black bg-white px-3 py-1.5 text-xs font-black shadow-[2px_2px_0px_0px_#000]">
                    ✓ Tanpa Daftar
                </div>
                <div class="inline-flex items-center gap-1.5 rounded-lg border-2 border-black bg-white px-3 py-1.5 text-xs font-black shadow-[2px_2px_0px_0px_#000]">
                    ✓ Kirim ke WhatsApp
                </div>
            </div>
        </div>
    </section>

    <!-- Main Live Interactive Calculator Section -->
    <main id="calculator-section" class="mx-auto w-full max-w-[1240px] px-4 py-8 sm:px-6 lg:px-8 flex-1">
        <CalculatorWorkspace />
    </main>

    <!-- Feature Highlights (Color Blocking: Blue, Yellow, White) -->
    <section id="features-section" class="border-t-[3px] border-black bg-white py-14">
        <div class="mx-auto max-w-[1240px] px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10">
                <Badge variant="yellow" size="md">Fitur Unggulan</Badge>
                <h2 class="mt-3 text-2xl font-black tracking-tight text-black sm:text-4xl">
                    Kenapa Memakai SplitBill?
                </h2>
                <p class="mt-2 text-sm font-bold text-neutral-600">
                    Solusi praktis untuk menghindari drama salah hitung saat makan bareng.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Feature 1: White Box with Blue Icon -->
                <Card variant="white" shadow="md">
                    <div class="flex size-12 items-center justify-center rounded-lg border-2 border-black bg-blue-600 text-yellow-300 shadow-[2px_2px_0px_0px_#000] mb-4">
                        <svg class="size-6 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-black text-black">Langsung Hitung</h3>
                    <p class="mt-2 text-xs font-bold text-neutral-600 leading-relaxed">
                        Gunakan kalkulator secara instan tanpa harus mendaftar atau login terlebih dahulu.
                    </p>
                </Card>

                <!-- Feature 2: Yellow Box -->
                <Card variant="yellow" shadow="md">
                    <div class="flex size-12 items-center justify-center rounded-lg border-2 border-black bg-white text-black shadow-[2px_2px_0px_0px_#000] mb-4">
                        <svg class="size-6 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-black text-black">Presisi Matematika</h3>
                    <p class="mt-2 text-xs font-bold text-black leading-relaxed">
                        Total setiap orang dijamin pas dengan grand total menggunakan algoritma Largest Remainder.
                    </p>
                </Card>

                <!-- Feature 3: Blue Box -->
                <Card variant="blue" shadow="md">
                    <div class="flex size-12 items-center justify-center rounded-lg border-2 border-black bg-yellow-400 text-black shadow-[2px_2px_0px_0px_#000] mb-4">
                        <svg class="size-6 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 1 1 0 5.292M15 21H3v-1a6 6 0 0 1 12 0v1zm0 0h6v-1a6 6 0 0 0-9-5.197M13 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-black text-yellow-300">Bagi Rata / Item</h3>
                    <p class="mt-2 text-xs font-bold text-white leading-relaxed">
                        Pilih menu yang dimakan bersama atau ditugaskan khusus untuk orang tertentu.
                    </p>
                </Card>

                <!-- Feature 4: White Box with Yellow Icon -->
                <Card variant="white" shadow="md">
                    <div class="flex size-12 items-center justify-center rounded-lg border-2 border-black bg-yellow-400 text-black shadow-[2px_2px_0px_0px_#000] mb-4">
                        <svg class="size-6 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 0 1-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-black text-black">Format WhatsApp</h3>
                    <p class="mt-2 text-xs font-bold text-neutral-600 leading-relaxed">
                        Salin teks rincian rapi atau kirim langsung ke grup chat dengan satu sentuhan.
                    </p>
                </Card>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <FaqSection />

    <Footer />
</div>
