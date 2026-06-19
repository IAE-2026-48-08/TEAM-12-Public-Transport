# Identitas Mahasiswa
- **Nama:** Renaya Marvela Adoe
- **NIM:** 102022400154
- **Kelas:** SI4808
- **Team:** 12

---

# Notification Delay Service (Layanan Notifikasi Delay)

Projek ini merupakan microservice **Layanan Notifikasi Delay (Notification Delay Service)** yang dibangun menggunakan **Laravel 11.x**. Microservice ini dirancang untuk berintegrasi dengan sistem pusat IAE untuk mengelola antrean notifikasi keterlambatan jadwal secara asinkron, serta melakukan audit transaksi kritis keterlambatan ke sistem pusat.

## 🚀 Deskripsi Tugas & Modul Integrasi

Layanan ini mengimplementasikan 3 modul integrasi utama yang menghubungkan sistem lokal dengan Server Pusat IAE:

### 1. Modul 1: Federated SSO (Single Sign-On)
* **Tujuan:** Mengamankan akses API dan melakukan validasi identitas pengguna secara terpusat.
* **Cara Kerja:**
    * Setiap request ke layanan divalidasi menggunakan `ApiKeyMiddleware` dengan validasi NIM lokal (`102022400154`).
    * Menerima dan memproses *payload* dari sistem keamanan terpusat untuk otentikasi internal.

### 2. Modul 2: SOAP XML Client (Audit Trail Keterlambatan)
* **Tujuan:** Melaporkan transaksi kritis perubahan jadwal/keterlambatan ke server audit eksternal legacy.
* **Cara Kerja:**
    * Data keterlambatan (delay) yang kritis dikonversi menjadi format XML yang kaku.
    * Mengirimkan log perubahan status jadwal ke sistem audit pusat untuk memastikan setiap perubahan waktu perjalanan tercatat dengan sah secara administratif.

### 3. Modul 3: AMQP Publisher (RabbitMQ Broadcast)
* **Tujuan:** Menyebarkan informasi keterlambatan ke seluruh departemen perusahaan secara asinkron (mengurangi beban sistem pusat).
* **Cara Kerja:**
    * Menggunakan *RabbitMQ Management Plugin* (HTTP API) untuk mempublikasikan event `delay.notified`.
    * Data notifikasi dikirimkan ke antrean pusat agar *service* lain (seperti Tiket/Rute) dapat menerima *update* perubahan jadwal tanpa harus melakukan *polling* manual.

---

## 🛠️ Arsitektur & Teknologi

* **Framework Utama:** PHP 8.x / Laravel 11.x
* **Database:** MySQL 8.0
* **Message Broker:** RabbitMQ
* **Communication Protocol:** REST API, SOAP, HTTP API
* **Containerization:** Docker & Docker Compose

---

## 🔌 API Endpoints & Interfaces

### 1. REST API (Memerlukan Header `X-IAE-KEY`)
Semua endpoint REST API di bawah prefix `/api/v1` dilindungi oleh middleware yang memvalidasi header kunci (`X-IAE-KEY` harus bernilai `102022400154`):
* `GET /api/v1/delays` - Mendapatkan daftar seluruh laporan keterlambatan.
* `GET /api/v1/delays/{id}` - Mendapatkan detail laporan keterlambatan spesifik.
* `POST /api/v1/delays` - Membuat laporan keterlambatan baru (akan memicu Audit SOAP & RabbitMQ broadcast).
* `POST /api/test-rabbit` - Endpoint pengujian khusus untuk memverifikasi koneksi RabbitMQ.

---

## ⚙️ Panduan Instalasi & Menjalankan Projek

### 1. Konfigurasi Environment File
Sesuaikan file `.env` untuk integrasi:
```env
IAE_SSO_URL=[https://iae-sso.virtualfri.id](https://iae-sso.virtualfri.id)
MY_TEAM_ID=TEAM-12
MY_NIM_KEY=102022400154


### 2. Menjalankan Docker Containers
Jalankan perintah berikut di folder root proyek untuk menyalakan semua container yang dibutuhkan:

Bash
docker-compose up -d --build

### 3. Setup Database
Lakukan migrasi database untuk menginisialisasi skema tabel yang diperlukan:

Bash
docker-compose exec app php artisan migrate

### 4. Pengujian RabbitMQ
Untuk melakukan pengujian integrasi asinkron, gunakan Postman dengan metode POST ke endpoint http://localhost:8000/api/test-rabbit dengan melampirkan body JSON berikut:

JSON
{
    "message": "Update keterlambatan jadwal rute X"
}
