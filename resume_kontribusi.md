# Resume Kontribusi Tim — TEAM-12 Public Transport

Dokumen ini merupakan rangkuman kontribusi pengerjaan coding, integrasi, dan pembagian tugas untuk setiap anggota kelompok TEAM-12 dalam penyusunan Tugas Besar Integrasi Sistem Enterprise.

---

## 1. Alvin Hibatullah (NIM: 102022430022)
*   **Peran Utama**: Penanggung Jawab Service Rute & Jadwal & Integrator Utama Notifikasi Delay
*   **Kontribusi Teknis**:
    *   Mengembangkan modul core **Rute & Jadwal** (Laravel).
    *   Mengonfigurasi dockerization dan database schema awal untuk scheduling transportasi.
    *   Mengintegrasikan seluruh Service **Notifikasi Delay** agar selaras dengan ketentuan Tugas Besar:
        *   Membuat dual middleware authentication (`VerifyIaeJwt.php`) di Service Notifikasi.
        *   Menghubungkan logika transaksi delay (`store` dan `sendNotification`) secara internal via REST API ke `rute-jadwal-app` dan `ticket-pembayaran-web`.
        *   Membuat skema dokumentasi Swagger ganda (JWT Bearer di bawah API Key) dan melakukan regenerasi dokumentasi L5-Swagger.
    *   Membuat mekanisme pembuatan jadwal perjalanan yang terintegrasi dengan SOAP Audit Cloud Pusat untuk mencatat log audit transaksi kritis.
    *   Menginisialisasi setup jaringan Docker (`team-12-network`) agar semua kontainer dapat terhubung secara internal.
    *   Mengatur routing dasar untuk service Rute di API Gateway Nginx.

---

## 2. Bayu (NIM: 102022400251)
*   **Peran Utama**: Penanggung Jawab Service Tiket & Pembayaran
*   **Kontribusi Teknis**:
    *   Mengembangkan modul **Tiket & Pembayaran** (Laravel + Nginx).
    *   Mengonfigurasi dual middleware authentication pada service tiket (JWT SSO Dosen + API Key Kredensial NIM Bayu).
    *   Melakukan refaktorisasi middleware autentikasi (`CheckIaeKey.php`) menggunakan helper `config()` untuk menggantikan `env()` agar tidak terpengaruh oleh isu cache konfigurasi Laravel.
    *   Menghubungkan alur transaksi pemesanan tiket (`POST /api/v1/tickets`) secara langsung ke Service Rute & Jadwal (`rute-jadwal-app`) secara internal via REST API untuk verifikasi jadwal.
    *   Mengembangkan consumer background process RabbitMQ menggunakan library `php-amqplib` untuk menangkap event-event broadcast dari Cloud Pusat.
    *   Mengintegrasikan endpoint GraphQL dan GraphQL Playground di belakang API Gateway.
    *   Menyempurnakan konfigurasi `docker-compose.yml` dengan menambahkan dependency `notifikasi-web` pada service `gateway` guna memastikan urutan start-up container berjalan dengan benar.

---

## 3. Renaya (NIM: 102022400154)
*   **Peran Utama**: Dokumentator & Pengunggah Kode Awal Service Notifikasi
*   **Kontribusi Teknis**:
    *   Mengunggah source code service Notifikasi Delay ke repositori bersama kelompok.
    *   Memperbarui dokumentasi README.md kelompok untuk menambahkan log pengerjaan service Notifikasi Delay.

