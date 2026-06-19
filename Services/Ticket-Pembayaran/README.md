# Identitas Mahasiswa
- **Nama:** Bayu Samudera
- **NIM:** 102022400251
- **Kelas:** SI4808
- **Team:** 12

---

# Ticket & Payment Service (Layanan Tiket & Pembayaran)

Projek ini merupakan microservice **Layanan Tiket & Pembayaran (Ticket & Payment Service)** yang dibangun menggunakan **Laravel 11.x**. Microservice ini dirancang untuk berintegrasi dengan sistem pusat IAE untuk mengelola otentikasi pengguna, audit transaksi keuangan/tiket, serta melakukan penyiaran event ke broker pesan secara asinkron.

## 🚀 Deskripsi Tugas & Modul Integrasi

Layanan ini mengimplementasikan 3 modul integrasi utama yang menghubungkan sistem lokal dengan Server Pusat IAE:

### 1. Modul 1: Federated SSO (Single Sign-On)
*   **Tujuan:** Mengamankan aplikasi lokal dan melakukan otentikasi pengguna secara terpusat.
*   **Cara Kerja:**
    *   Pengguna melakukan login menggunakan API Key Mahasiswa (`KEY-MHS-264`).
    *   Aplikasi mengirimkan permintaan token JWT ke SSO Server Pusat (`POST /api/v1/auth/token`).
    *   Token JWT yang dikembalikan didekode secara internal (mengambil data *claims* seperti `email`, `name`, dan `role`).
    *   Informasi tersebut disinkronkan ke tabel database lokal (`users`) menggunakan fitur *SSO User Mapping*, memetakan peran secara dinamis.
    *   Token JWT disimpan di session untuk otorisasi request transaksi berikutnya.

### 2. Modul 2: SOAP XML Client (Audit Trail Transaksi Kritis)
*   **Tujuan:** Melaporkan transaksi pembelian tiket yang kritis ke server audit eksternal legacy demi akuntabilitas keuangan.
*   **Cara Kerja:**
    *   Data transaksi tiket berformat JSON dikonversi menjadi dokumen XML dengan skema kaku SOAP Envelope (`<soap:Envelope>`).
    *   Permintaan dikirim ke server audit legacy pusat (`POST /soap/v1/audit`) dengan menyertakan JWT Bearer token di header.
    *   Respon dari server berupa XML dibaca dan diekstrak nilainya (menggunakan Regex) untuk mendapatkan `ReceiptNumber` unik.
    *   Status tiket lokal kemudian diperbarui menjadi `AUDITED` dan menyimpan `receipt_number` sebagai bukti audit transaksi sah.

### 3. Modul 3: AMQP Event Publisher (RabbitMQ Broadcast)
*   **Tujuan:** Mendistribusikan notifikasi event transaksi tiket sukses ke departemen lain secara asinkron.
*   **Cara Kerja:**
    *   Data event berformat JSON dikirimkan ke REST API proxy RabbitMQ pusat.
    *   Permintaan dikirimkan ke `/api/v1/messages/publish` dengan membawa Bearer JWT.
    *   Payload ditujukan ke exchange `iae.central.exchange` dengan routing key `ticket.purchased`.

---

## 🛠️ Arsitektur & Teknologi

*   **Framework Utama:** PHP 8.x / Laravel 11.x
*   **Database & Cache:** MySQL 8.0, Redis (sebagai penyimpan session / cache driver)
*   **REST API Engine:** L5-Swagger / OpenAPI Annotation
*   **GraphQL Engine:** Nuwave Lighthouse (menyediakan schema query dan mutasi GraphQL)
*   **Containerization:** Docker & Docker Compose

---

## 🔌 API Endpoints & Interfaces

### 1. REST API (Memerlukan Header `X-IAE-KEY` berisi NIM)
Semua endpoint REST API di bawah prefix `/api/v1` dilindungi oleh middleware keamanan yang memvalidasi header kunci mahasiswa (`X-IAE-KEY` harus bernilai `102022400251`):
*   `GET /api/v1/tickets` - Mendapatkan daftar seluruh tiket (Collection)
*   `GET /api/v1/tickets/{id}` - Mendapatkan detail tiket tertentu berdasarkan UUID (Resource)
*   `POST /api/v1/tickets` - Membuat tiket baru (Action)

### 2. GraphQL API
Mengakses data model `Ticket` menggunakan query GraphQL:
*   Endpoint GraphQL: `/graphql`
*   GraphQL Playground (Interactive UI): `/graphql-playground`

### 3. Web Dashboard Integrasi Pusat
*   `GET /central` - Halaman monitoring status koneksi, log audit, dan publisher event
*   `POST /central/login` - Melakukan M2M authentication ke Server SSO Pusat
*   `POST /central/audit` - Mengirim audit transaksi dalam format SOAP XML ke pusat
*   `POST /central/publish` - Mempublikasikan JSON event ke RabbitMQ

---

## ⚙️ Panduan Instalasi & Menjalankan Projek

Ikuti langkah-langkah di bawah untuk menjalankan projek di lingkungan lokal Anda menggunakan Docker:

### 1. Prasyarat
Pastikan Anda sudah menginstal:
*   [Docker Desktop](https://www.docker.com/products/docker-desktop/)

### 2. Konfigurasi Environment File
Salin file `.env.example` ke `.env` (atau edit file `.env` yang sudah ada) dan sesuaikan variabel integrasi berikut:
```env
IAE_SSO_URL=https://iae-sso.virtualfri.id
MY_TEAM_ID=TEAM-12
MY_NIM_KEY=102022400251
```

### 3. Menjalankan Docker Containers
Jalankan perintah berikut pada terminal di folder root projek untuk menyalakan semua container (app, web nginx, database mysql, redis, dan compiler node):
```bash
docker-compose up -d --build
```

### 4. Setup Database & Generate Key
Jalankan migrasi database lokal di dalam container app:
```bash
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate
```

### 5. Generate Dokumentasi Swagger (Optional)
Jika Anda ingin memperbarui dokumentasi API Swagger UI secara manual:
```bash
docker-compose exec app php artisan l5-swagger:generate
```

### 6. Akses Layanan
Buka browser Anda dan akses tautan berikut:
*   **Web Dashboard Integrasi:** [http://localhost:8000/central](http://localhost:8000/central)
*   **REST API Swagger Documentation:** [http://localhost:8000/api/documentation](http://localhost:8000/api/documentation)
*   **GraphQL Playground:** [http://localhost:8000/graphql-playground](http://localhost:8000/graphql-playground)
