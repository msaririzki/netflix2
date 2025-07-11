# Aplikasi Streaming Film Sederhana (Netflix Clone)

Aplikasi web streaming film sederhana yang dibangun menggunakan PHP Native dan MySQL. Proyek ini dibuat sebagai studi kasus untuk memahami alur kerja aplikasi web dinamis, lengkap dengan sistem otentikasi, manajemen konten, dan panel admin.

![Tampilan Halaman Detail](image_53e9f5.png)

---

## Fitur Utama

### Fitur Pengguna (Viewer)
- **Dashboard Dinamis**: Menampilkan film-film terbaru.
- **Pencarian & Filter**: Mencari film berdasarkan judul dan memfilter berdasarkan kategori/genre.
- **Halaman Detail**: Menampilkan informasi lengkap film (poster, sinopsis, sutradara, dll).
- **Gerbang Login**: Pengguna wajib login untuk menonton, dengan pop-up (modal) yang interaktif.
- **Halaman Nonton Imersif**: Pemutar video modern (Plyr.io) dengan latar belakang gelap.
- **Manajemen Profil**: Pengguna dapat mengubah nama, password, dan foto profil.

### Fitur Admin
- **Dashboard Admin**: Panel kontrol terpisah untuk mengelola seluruh konten situs.
- **Manajemen Film (CRUD)**: Admin dapat menambah, melihat, mengedit, dan menghapus film.
- **Manajemen Genre Dinamis**: Admin dapat menambah atau menghapus kategori genre secara mandiri.
- **Manajemen Pengguna**: Melihat daftar semua pengguna yang terdaftar beserta perannya.
- **Analitik Sederhana**: Melihat 10 film yang paling banyak ditonton.

---

## Teknologi yang Digunakan
- **Backend**: PHP 8.x (Native)
- **Database**: MySQL (MariaDB via XAMPP)
- **Frontend**:
  - HTML5 & CSS3
  - JavaScript (ES6)
  - Bootstrap 5
  - AOS (Animate on Scroll)
  - Plyr.io

---

## Panduan Instalasi

1.  **Prasyarat**: Pastikan Anda sudah menginstal **XAMPP**.
2.  **Clone Repositori**:
    ```bash
    git clone [URL_REPOSITORY_ANDA]
    ```
    Letakkan folder proyek di dalam direktori `C:\xampp\htdocs\`.

3.  **Setup Database**:
    - Buka **phpMyAdmin** (`http://localhost/phpmyadmin`).
    - Buat database baru dengan nama `netflix2`.
    - Pilih database `netflix2`, buka tab **"SQL"**, dan impor file `database_setup.sql` yang ada di repositori ini.

4.  **Konfigurasi Koneksi**:
    - Buka file `config/database.php`.
    - Pastikan detail koneksi sudah sesuai dengan pengaturan XAMPP Anda (umumnya sudah benar secara default).

5.  **Jalankan Aplikasi**:
    - Buka browser dan akses `http://localhost/[NAMA_FOLDER_PROYEK_ANDA]`.
    - Buat akun baru melalui halaman registrasi.
    - Ubah `role` akun Anda menjadi `admin` melalui phpMyAdmin untuk mengakses panel admin.

---

## Konfigurasi Server untuk Upload File Besar (Opsional)

Jika Anda berencana mengunggah file video yang sangat besar, Anda perlu mengubah beberapa pengaturan di file konfigurasi PHP (`php.ini`).

#### 1. Menemukan File `php.ini`
- Buka **XAMPP Control Panel**.
- Di baris **Apache**, klik tombol **"Config"**.
- Pilih **`PHP (php.ini)`**. File ini akan terbuka di editor teks Anda.

#### 2. Pengaturan yang Perlu Diubah
Gunakan `Ctrl + F` untuk mencari dan mengubah nilai-nilai berikut.

```ini
; Ubah ini untuk ukuran file upload maksimal (contoh: 500MB)
upload_max_filesize = 500M

; Ubah ini, nilainya HARUS lebih besar dari upload_max_filesize (contoh: 512MB)
post_max_size = 512M

; Ubah ini agar PHP punya cukup memori untuk memproses file
memory_limit = 512M

; Ubah ini agar skrip tidak berhenti di tengah jalan saat upload (timeout)
; Nilai dalam detik. 1800 = 30 menit
max_execution_time = 1800

; Ubah ini agar skrip tidak timeout saat menerima data
max_input_time = 1800