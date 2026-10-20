# SplitBill

SplitBill adalah aplikasi web untuk menghitung dan membagi tagihan secara adil, transparan, dan responsif. Pengguna dapat menghitung tagihan tanpa login, lalu menyimpan dan mengelola riwayat perhitungan setelah memiliki akun terverifikasi.

## Fitur Utama

- Membagi tagihan secara rata atau berdasarkan item/menu.
- Menambahkan partisipan dan menetapkan item kepada partisipan.
- Menghitung pajak, biaya layanan, tip, dan diskon.
- Menampilkan rincian subtotal, penyesuaian, dan total setiap partisipan.
- Menyimpan, melihat, mengubah, menduplikasi, dan menghapus riwayat tagihan.
- Registrasi, login, logout, verifikasi email dengan OTP, dan reset password.
- Menyalin rincian tagihan dan membagikannya melalui WhatsApp.
- Mencetak atau menyimpan invoice sebagai PDF.
- Tampilan responsif untuk mobile, tablet, dan desktop.

## Tech Stack

- PHP 8.3+ dan Laravel 13.
- Inertia.js 3 dan Svelte 5.
- TypeScript.
- Tailwind CSS 4.
- Laravel Wayfinder untuk route helper frontend.
- SQLite sebagai konfigurasi database lokal default.
- Pest/PHPUnit untuk testing dan Larastan untuk analisis PHP.

## Prasyarat

Pastikan perangkat sudah memiliki:

- Git.
- PHP 8.3 atau lebih baru.
- Composer.
- Node.js dan npm.
- SQLite, atau database lain yang didukung Laravel jika ingin mengganti konfigurasi default.

Versi yang digunakan pada saat dokumentasi ini dibuat:

```text
PHP      8.4+
Composer 2.10+
Node.js  26+
npm      11+
```

## Instalasi Setelah Clone

Clone repository dan masuk ke direktori proyek:

```bash
git clone <repository-url> splitbill-app
cd splitbill-app
```

### Setup cepat

Proyek menyediakan script setup yang menjalankan instalasi dependency, pembuatan file environment, generate application key, migrasi database, instalasi dependency frontend, dan build frontend:

```bash
composer run setup
```

Setelah selesai, periksa file `.env` dan sesuaikan konfigurasi yang diperlukan.

### Setup manual

Jika ingin menjalankan setiap langkah secara terpisah:

```bash
composer install
```

Buat file environment:

```bash
copy .env.example .env
```

Untuk macOS/Linux, gunakan:

```bash
cp .env.example .env
```

Generate application key dan jalankan migrasi:

```bash
php artisan key:generate
php artisan migrate
```

Install dependency frontend dan buat asset production:

```bash
npm install
npm run build
```

## Konfigurasi Environment

Konfigurasi utama berada di file `.env`. Jangan commit file `.env` atau nilai rahasia ke repository.

Contoh konfigurasi penting:

```dotenv
APP_NAME=SplitBill
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

APP_TIMEZONE=Asia/Jakarta
APP_LOCALE=id
APP_FALLBACK_LOCALE=id

DB_CONNECTION=sqlite
MAIL_MAILER=log
VITE_APP_NAME="${APP_NAME}"
```

Catatan:

- `APP_URL` harus diisi dengan domain aplikasi yang sedang digunakan. Domain ini juga digunakan pada pesan berbagi dan invoice.
- `APP_TIMEZONE` menggunakan `Asia/Jakarta`.
- Konfigurasi SQLite pada `.env.example` digunakan untuk development lokal.
- Konfigurasi mail default menggunakan log, sehingga email tidak dikirim ke inbox sungguhan. Untuk staging atau production, isi konfigurasi `MAIL_*` sesuai mail provider.
- Jika menggunakan database selain SQLite, ubah `DB_CONNECTION` dan variable `DB_*` yang relevan.

## Database

Jalankan migrasi database dengan:

```bash
php artisan migrate
```

Untuk menghapus seluruh tabel dan menjalankan ulang migrasi beserta seeder pada database lokal:

```bash
php artisan migrate:fresh --seed
```

Jangan menjalankan perintah `migrate:fresh` pada database production karena perintah tersebut menghapus seluruh tabel.

## Menjalankan Aplikasi

### Menggunakan satu perintah

```bash
composer run dev
```

### Menjalankan proses secara terpisah

Terminal pertama:

```bash
php artisan serve
```

Terminal kedua:

```bash
npm run dev
```

Buka URL yang ditampilkan oleh Laravel development server. Pastikan `APP_URL` sesuai dengan URL yang digunakan.

## Testing dan Quality Check

Menjalankan test Laravel:

```bash
php artisan test
```

Menjalankan test lengkap yang juga melakukan pemeriksaan lint dan static analysis:

```bash
composer run test
```

Pemeriksaan frontend:

```bash
npm run check
npm run types:check
```

Build frontend production:

```bash
npm run build
```

Format dan pemeriksaan kode PHP:

```bash
composer run lint
composer run lint:check
```

## Struktur Folder Penting

```text
app/
├── Http/Controllers/       Controller Laravel dan endpoint aplikasi
├── Http/Middleware/        Shared props Inertia dan middleware
├── Models/                 Model Eloquent
├── Notifications/          Notifikasi email
├── Policies/               Authorization riwayat tagihan
└── Services/               Logic kalkulasi dan persistence

database/
├── factories/              Factory untuk testing
├── migrations/             Struktur database
└── seeders/                Data awal database

resources/
├── css/                    Stylesheet Tailwind
└── js/
    ├── components/         Komponen reusable Svelte
    ├── lib/                Helper dan logic frontend
    ├── pages/              Halaman Inertia/Svelte
    └── types/              Deklarasi tipe TypeScript

routes/web.php              Route aplikasi web
tests/                      Unit test dan feature test
vite.config.ts              Konfigurasi Vite, Svelte, Tailwind, dan Wayfinder
```

## Route Utama

Route publik:

- `/` — landing page dan akses kalkulator.
- `/calculator` — kalkulator SplitBill.
- `/login` — login.
- `/register` — registrasi akun.
- `/forgot-password` — permintaan reset password.

Route pengguna terautentikasi dan terverifikasi:

- `/calculations` — daftar riwayat tagihan.
- `/calculations/create` — membuat perhitungan tersimpan.
- `/calculations/{id}` — melihat detail perhitungan.
- `/calculations/{id}/edit` — mengubah perhitungan.

## Catatan untuk Kontributor

- Gunakan Bahasa Indonesia untuk teks antarmuka dan pesan error baru.
- Pertahankan timezone `Asia/Jakarta` dan gunakan konfigurasi aplikasi untuk domain, jangan menulis domain production secara hardcoded.
- Logic perhitungan utama berada di `app/Services/Calculation/` dan harus tetap konsisten dengan perhitungan preview frontend.
- Perubahan frontend perlu divalidasi dengan `npm run types:check` dan `npm run build`.
- Perubahan PHP perlu diformat menggunakan Laravel Pint.
- Jangan menambahkan atau mengubah dependency tanpa kebutuhan yang jelas.
- Jangan commit `.env`, application key, credential database, credential mail, atau secret lainnya.

## Dokumentasi Tambahan

- [PRD.md](PRD.md) — kebutuhan produk dan aturan bisnis.
- [.env.example](.env.example) — template konfigurasi environment.
- [composer.json](composer.json) — dependency dan script backend.
- [package.json](package.json) — dependency dan script frontend.
