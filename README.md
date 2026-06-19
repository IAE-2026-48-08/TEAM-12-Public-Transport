# TEAM-12 Public Transport — Integrasi Sistem Enterprise

Repositori ini menggabungkan seluruh layanan (microservices) untuk sistem transportasi umum Team-12, menggunakan arsitektur gabungan Docker dan API Gateway.

## 🚀 Anggota Kelompok
*   **Alvin Hibatullah** (NIM: 102022430022) — Service Rute & Jadwal
*   **Bayu** (NIM: 102022400251) — Service Tiket & Pembayaran

---

## 🏗️ Arsitektur & Komponen
Sistem ini terdiri dari beberapa komponen yang berjalan di dalam Docker container dan saling terhubung melalui jaringan `team-12-network`:

1.  **API Gateway (Nginx)**: Port eksternal `80`, merutekan request luar ke layanan internal yang sesuai.
2.  **Layanan Rute & Jadwal**: Mengelola rute dan waktu perjalanan (Laravel Sail).
3.  **Layanan Tiket & Pembayaran**: Mengelola pemesanan tiket dan transaksi pembayaran.

---

## 🛠️ Cara Menjalankan Layanan (Docker)

Pastikan Docker Desktop Anda sudah aktif, lalu jalankan perintah berikut di root folder proyek:

```bash
# 1. Nyalakan semua container gabungan
docker compose up -d

# 2. Lakukan migrasi database untuk Rute & Jadwal
docker exec -it rute-jadwal-app php artisan migrate --seed

# 3. Lakukan migrasi database untuk Ticket & Pembayaran
docker exec -it ticket-pembayaran-app php artisan migrate --seed
```

Setelah menyala, Anda dapat mengakses layanan melalui API Gateway di:
*   API Rute & Jadwal: `http://localhost/api/v1/schedules`
*   API Tiket & Pembayaran: `http://localhost/api/v1/tickets`

---

## 🤖 Rekap Log Prompting AI Kelompok

Berikut adalah catatan rekap penggunaan AI untuk membantu menyelesaikan kendala pemrograman selama pengerjaan proyek dari Tugas 2, Tugas 3, hingga Tugas Besar.

### 1. Bagian Layanan Rute & Jadwal (Alvin Hibatullah - 102022430022)

#### Tugas 2 (Docker & Database)
*   Cara mematikan Laragon agar tidak terjadi bentrok port dengan Docker di Windows.
*   Mencari letak konfigurasi password database bawaan pada file docker compose Laravel Sail.
*   Mengatasi kendala command docker sail not found di PowerShell Windows.
*   Solusi error format waktu MySQL "incorrect datetime value" saat mengisi seeder jadwal.
*   Mengatur cors.php agar tidak memicu CORS error saat diakses dari client luar.

#### Tugas 3 (SSO, SOAP, RabbitMQ)
*   Pembuatan middleware verifikasi tanda tangan JWT RS256 menggunakan library firebase-jwt.
*   Menyusun XML payload request secara manual untuk integrasi SOAP audit trail tanpa PHP-SOAP extension.
*   Cara membaca dan mengambil data receipt number dari XML response server SOAP menggunakan SimpleXMLElement.
*   Format payload JSON yang tepat untuk dipublikasikan ke broker RabbitMQ pusat.
*   Menyesuaikan parameter SSO token dengan menambahkan field nim mahasiswa sesuai aturan baru.

#### Tugas Besar (API Gateway & Jaringan Docker)
*   Menyatukan container Laravel Sail dan container PHP Nginx ke dalam satu network eksternal `team-12-network`.
*   Solusi agar autoload composer tidak mengalami hang saat container database belum sepenuhnya aktif.
*   Cara melakukan request API internal antar-container di Docker menggunakan hostname.

---

### 2. Bagian Layanan Tiket & Pembayaran (Bayu - 102022400251)
*(Dapat ditambahkan oleh Bayu di sini setelah pengerjaannya selesai)*
