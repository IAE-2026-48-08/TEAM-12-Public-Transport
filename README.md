# TEAM-12 Public Transport — Integrasi Sistem Enterprise

Repositori ini menggabungkan seluruh layanan (microservices) untuk sistem transportasi umum Team-12, menggunakan arsitektur gabungan Docker dan API Gateway.

## Anggota Kelompok
*   **Alvin Hibatullah** (NIM: 102022430022) — Service Rute & Jadwal
*   **Bayu** (NIM: 102022400251) — Service Tiket & Pembayaran
*   **Renaya** (NIM:102022400154) - Service Notifikasi Delay

---

## Arsitektur & Komponen
Sistem ini terdiri dari beberapa komponen yang berjalan di dalam Docker container dan saling terhubung melalui jaringan `team-12-network`:

1.  **API Gateway (Nginx)**: Port eksternal `80`, merutekan request luar ke layanan internal yang sesuai.
2.  **Layanan Rute & Jadwal**: Mengelola rute dan waktu perjalanan (Laravel Sail).
3.  **Layanan Tiket & Pembayaran**: Mengelola pemesanan tiket dan transaksi pembayaran.

---

## Cara Menjalankan Layanan (Docker)

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

## Rekap Log Prompting AI Kelompok

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

#### Tugas 2 (Docker & Database)
*   Membuat Dockerfile berbasis php:8.2-fpm dan memasang ekstensi pdo_mysql untuk koneksi database.
*   Mengatur konfigurasi Nginx default agar request PHP-FPM diteruskan ke port 9000 container aplikasi.
*   Menyusun seeder data tiket dan transaksi pembayaran untuk inisialisasi awal database.
*   Mengatasi error database connection refused pada container Laravel saat pertama kali dinyalakan bersama MySQL.

#### Tugas 3 (SSO, SOAP, RabbitMQ)
*   Membuat logika penanganan dan penyimpanan log audit XML untuk dikirimkan via client SOAP.
*   Membuat consumer/subscriber RabbitMQ di Laravel menggunakan package php-amqplib untuk menangkap event schedule.created dari service Rute-Jadwal.
*   Mengimplementasikan middleware validasi token M2M dari SSO dengan mencocokkan nim dan api_key.
*   Mengatasi masalah antrean RabbitMQ yang tidak otomatis terbuat (declare queue) saat container consumer dijalankan.

#### Tugas Besar (API Gateway & Jaringan Docker)
*   Menhubungkan container Nginx ticket-pembayaran-web ke jaringan bersama kelompok.
*   Menguji koneksi pemanggilan API internal ke container rute-jadwal-app untuk mencocokkan ketersediaan jadwal sebelum tiket dibuat.
**

---

### 3. Bagian Notifikasi Delay (Renaya - 102022400154)

#### Tugas 2 (Docker & Database)
* Menginisialisasi arsitektur database notification_delay_db untuk menyimpan data log notifikasi dan antrean delay.
* Menyusun skema migrasi tabel delays yang mencakup field schedule_code, reason, dan delay_minutes untuk kebutuhan tracking transaksi kritis.
* Mengonfigurasi koneksi database di dalam container Docker agar terhubung dengan layanan mikro lainnya dalam satu network yang sama.



#### Tugas 3 (SSO, SOAP, RabbitMQ)
* Pengembangan Service untuk menangani notification delay secara asinkronus menggunakan RabbitMQ.
* Implementasi RabbitMqService menggunakan pendekatan HTTP API (RabbitMQ Management Plugin) untuk pengiriman pesan event notification.
* Pembuatan route API POST /api/test-rabbit untuk pengujian integrasi publisher ke antrean pesan.
* Implementasi ApiKeyMiddleware untuk mengamankan akses API dengan verifikasi header X-IAE-KEY (NIM: 102022400154).
* Integrasi AuditService untuk mengirimkan data transaksi ke sistem SOAP Legacy sebagai syarat validasi transaksi kritis.



#### Tugas Besar (API Gateway & Jaringan Docker)
*Penyusunan dokumen analisis_tugas_3.md yang merinci justifikasi transaksi kritis (data delay jadwal) yang memerlukan audit SOAP dan notifikasi RabbitMQ.
* Penyusunan Sequence Diagram untuk interaksi layanan notification-delay-service dengan SSO Dosen dan sistem Cloud Pusat.
* Finalisasi pengujian end-to-end menggunakan Postman untuk memastikan sinkronisasi data antar-service melalui API Gateway.
* Pemetaan role pengguna ke tabel lokal setelah berhasil menangkap payload JWT dari SSO Dosen.
