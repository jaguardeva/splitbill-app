<script lang="ts">
import Accordion from '@/components/ui/Accordion.svelte';
import AccordionItem from '@/components/ui/AccordionItem.svelte';
import Badge from '@/components/ui/Badge.svelte';

let activeIndex = $state(0);

const faqs = [
    {
        q: 'Bagaimana cara kerja pembagian Bagi Rata vs Per Item?',
        a: 'Mode Bagi Rata (Equal Split) membagi seluruh subtotal tagihan secara merata kepada semua orang. Sedangkan mode Per Item (Itemized) memungkinkan Anda menentukan makanan/minuman yang dikonsumsi oleh orang tertentu, atau membagi satu menu untuk beberapa orang sekaligus. Pajak, diskon, dan layanan akan dihitung secara proporsional sesuai porsi masing-masing.',
    },
    {
        q: 'Apakah saya harus membuat akun untuk menggunakan SplitBill?',
        a: 'Sama sekali tidak! Anda bisa langsung memakai kalkulator ini tanpa login atau registrasi. Anda baru memerlukan akun jika ingin menyimpan riwayat perhitungan ke cloud untuk ditinjau kembali nanti. Draf yang Anda buat sebagai tamu juga akan otomatis tersimpan jika Anda memutuskan untuk mendaftar.',
    },
    {
        q: 'Bagaimana sistem menjamin tidak ada selisih uang atau pembulatan rupiah?',
        a: 'SplitBill menggunakan algoritma matematika deterministik Largest Remainder (Metode Hamilton-Hare). Algoritma ini memastikan bahwa jumlah total yang dibayar oleh seluruh partisipan sama persis dengan Grand Total struk hingga 1 Rupiah terakhir tanpa ada uang kembalian atau sisa yang hilang.',
    },
    {
        q: 'Apakah rincian perhitungan bisa dibagikan langsung ke WhatsApp?',
        a: 'Tentu saja! Setelah perhitungan selesai, klik tombol "Bagi Rincian". Anda bisa menyalin teks ringkasan rapi atau langsung menekan tombol WhatsApp untuk mengirim rincian ke grup obrolan teman-teman Anda.',
    },
    {
        q: 'Bagaimana cara menambahkan diskon, pajak PB1, dan biaya layanan?',
        a: 'Pada bagian "Biaya Tambahan & Diskon", kami telah menyediakan tombol preset cepat untuk Pajak Restoran (PB1 10%), Biaya Layanan (5%), Diskon Promo (persentase atau nominal rupiah tetap), serta Tip sukarela. Anda bebas mengaktifkan atau menonaktifkan penyesuaian sesuai struk kasir.',
    },
];
</script>

<section class="border-t-[3px] border-black bg-neutral-100 py-16">
    <div class="mx-auto max-w-[840px] px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <Badge variant="yellow" size="md">Tanya Jawab</Badge>
            <h2 class="mt-3 text-2xl font-black tracking-tight text-black sm:text-4xl">
                Pertanyaan yang Sering Diajukan (FAQ)
            </h2>
            <p class="mt-2 text-sm font-bold text-neutral-600">
                Punya pertanyaan seputar cara menghitung dan membagikan tagihan? Temukan jawabannya di sini.
            </p>
        </div>

        <Accordion>
            {#each faqs as item, index}
                <AccordionItem
                    title={item.q}
                    isOpen={activeIndex === index}
                    ontoggle={() => { activeIndex = activeIndex === index ? -1 : index; }}
                >
                    <p>{item.a}</p>
                </AccordionItem>
            {/each}
        </Accordion>
    </div>
</section>
