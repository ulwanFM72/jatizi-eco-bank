# JATIZI ECO-BANK

Aplikasi web bank sampah **SMK Negeri 1 Cijati** untuk mencatat setoran sampah dari siswa, guru, dan tenaga administrasi, serta memantau jumlah sampah yang terkumpul dan dikelola oleh Bank Sampah SMKN 1 Cijati.

Aplikasi ini bertujuan mengubah pencatatan manual menjadi sistem digital yang lebih tertib, transparan, akurat, dan mudah dievaluasi.

## Fitur Utama

- **Dashboard**: ringkasan total sampah, total poin, jumlah transaksi, dan peringkat kelas.
- **Pencatatan setoran**: nama penyetor, kelas, jenis sampah, berat (gram atau kilogram), tanggal, dan petugas penerima.
- **Perhitungan poin otomatis**: setiap akumulasi 1.000 gram setara 5 poin. Sisa berat di bawah 1.000 gram tetap disimpan dan digabungkan dengan setoran berikutnya.
- **Riwayat transaksi**: pencarian dan penyaringan berdasarkan tanggal atau kelas, beserta detail dan status pencatatan.
- **Peringkat kelas**: diperbarui otomatis setelah setoran dicatat.
- **Laporan**: rekap berat dan poin berdasarkan rentang tanggal atau kelas, dapat dicetak atau diunduh dalam format PDF.
- **Pengelolaan admin**: kelas, jenis sampah, setoran, pengguna, dan aturan konversi poin.

Kategori sampah yang dikelola: **organik**, **an-organik**, dan **B3**.

## Hak Akses Pengguna

| Peran | Hak akses |
|---|---|
| Admin | Mengelola seluruh data master, pengguna, aturan poin, setoran (termasuk koreksi dan penghapusan), serta laporan |
| Petugas bank sampah | Mencatat setoran dan melihat riwayat |
| Siswa, guru, dan tenaga administrasi | Melihat riwayat setoran pribadi, akumulasi sampah, nilai setoran, dan peringkat kepedulian |
| Pengunjung umum | Melihat beranda, peringkat kelas, dan riwayat setoran dengan nama penyetor disamarkan |

## Teknologi

- Laravel (versi terbaru) dan Inertia.js
- Vue.js 3
- Tailwind CSS
- MySQL
- DomPDF untuk pembuatan laporan PDF

## Prasyarat

- PHP 8.2 atau lebih baru
- Composer
- Node.js 20 atau lebih baru
- MySQL

## Instalasi

```bash
git clone <URL-REPOSITORI>
cd <NAMA-FOLDER>

composer install
npm install
cp .env.example .env
php artisan key:generate
```

Buat basis data MySQL dengan nama `jatizi_eco_bank`, kemudian sesuaikan pengaturan berikut pada berkas `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=jatizi_eco_bank
DB_USERNAME=root
DB_PASSWORD=
APP_TIMEZONE=Asia/Jakarta
APP_LOCALE=id
```

Jalankan migrasi dan aplikasi:

```bash
php artisan migrate --seed
npm run build
php artisan serve
```

Aplikasi dapat diakses melalui `http://127.0.0.1:8000`.

## Akun Awal

Seeder membuat satu akun admin dengan nama pengguna `admin`. Kata sandi awal dapat ditentukan melalui variabel `SEED_ADMIN_PASSWORD` pada berkas `.env` sebelum menjalankan seeder. **Harap segera mengganti kata sandi bawaan setelah masuk untuk pertama kali.**

## Pengujian

```bash
php artisan test
```

## Aturan Perhitungan Poin

Perhitungan poin dilakukan sepenuhnya di sisi server (backend Laravel) sehingga hasilnya tidak dapat dimanipulasi melalui peramban. Setiap perubahan, koreksi, atau penghapusan setoran dihitung ulang secara konsisten agar poin tidak terhitung dua kali. Perubahan aturan konversi hanya berlaku untuk setoran yang dicatat setelah perubahan tersebut.

## Keamanan

- Jangan mengunggah berkas `.env` ke repositori.
- Gunakan kata sandi yang kuat untuk seluruh akun pengelola.
- Apabila menemukan celah keamanan, mohon laporkan langsung kepada pengelola aplikasi.

## Pengelola

Bank Sampah SMK Negeri 1 Cijati.
