# Rekap Log Prompting AI Kelompok — TEAM-12

Berikut adalah rekap terperinci penggunaan AI untuk memandu pengerjaan, troubleshooting, dan integrasi microservices pada Tugas Besar:

---

## 1. Alvin Hibatullah (NIM: 102022430022) — Service Rute & Jadwal
*   **Docker & Database Setup**:
    *   *Prompt*: "Bagaimana cara mematikan service Laragon/Apache lokal di Windows agar port 80 tidak bentrok saat docker compose dijalankan?"
    *   *Prompt*: "Error 'incorrect datetime value' di MySQL saat running php artisan db:seed untuk tanggal perjalanan. Bagaimana memformat carbon date agar pas dengan format DATETIME MySQL?"
*   **SSO, SOAP, & RabbitMQ**:
    *   *Prompt*: "Bagaimana membuat parser XML manual di PHP menggunakan SimpleXMLElement untuk mengambil ReceiptNumber dari respon SOAP tanpa extension SOAP bawaan?"
    *   *Prompt*: "Cara decode token JWT RS256 menggunakan package firebase/php-jwt di Laravel 11 tanpa menggunakan library auth bawaan."
*   **API Gateway & Docker Network**:
    *   *Prompt*: "Bagaimana cara membuat custom network bridge bernama team-12-network di docker-compose.yml agar bisa dipakai bersama oleh service buatan teman sekelompok?"
*   **Integrasi Git Pull & Pemeliharaan Cache**:
    *   *Prompt*: "Bagaimana cara memverifikasi, melakukan sinkronisasi cache Laravel, dan me-restart container docker-compose setelah menarik update (git pull) yang memodifikasi middleware autentikasi dan orchestrator?"

---

## 2. Bayu (NIM: 102022400251) — Service Tiket & Pembayaran
*   **PHP-FPM & Nginx Dockerization**:
    *   *Prompt*: "Bagaimana menyusun Dockerfile berbasis php-fpm untuk Laravel agar dapat terhubung dengan Nginx di container terpisah menggunakan fastcgi_pass?"
    *   *Prompt*: "Mengapa Laravel mengembalikan error 2002 Connection Refused saat mencoba menghubungkan app container ke db container saat start up? Bagaimana membuat script wait-for-it di entrypoint?"
*   **RabbitMQ Consumer**:
    *   *Prompt*: "Bagaimana membuat file console command artisan di Laravel 11 untuk menjalankan consumer RabbitMQ infinite loop menggunakan package php-amqplib?"
    *   *Prompt*: "Bagaimana mendeklarasikan antrean (queue_declare) secara otomatis di sisi consumer jika broker RabbitMQ belum memilikinya?"
*   **REST Call Internal**:
    *   *Prompt*: "Bagaimana menggunakan Http Facade Laravel untuk memanggil REST endpoint internal di container lain via nama host docker?"

---

## 3. Renaya (NIM: 102022400154) — Service Notifikasi Delay
*   **Database & API Key**:
    *   *Prompt*: "Bagaimana cara membuat ApiKeyMiddleware di Laravel 11 untuk memvalidasi request header X-IAE-KEY menggunakan NIM mahasiswa?"
*   **SOAP Audit**:
    *   *Prompt*: "Bagaimana cara mengirim HTTP POST request berisi envelope XML SOAP secara asinkron menggunakan Laravel Http Client?"
*   **Dual-Authentication & Service Alignment**:
    *   *Prompt*: "Bagaimana cara memadukan middleware ApiKeyMiddleware dengan middleware verifikasi JWT SSO dalam satu route group di Laravel?"
    *   *Prompt*: "Bagaimana agar endpoint Notifikasi Delay memvalidasi keaktifan layanan Tiket via REST API internal sebelum memproses data? Gunakan Http Client dengan timeout dan tangani error agar jika Tiket mati, Notifikasi mengembalikan status 502."
*   **Swagger Annotation**:
    *   *Prompt*: "Bagaimana cara mengonfigurasi skema pengamanan bearerAuth dan ApiKeyAuth bersamaan di L5-Swagger/OpenAPI php agar memunculkan tombol Authorize ganda dengan posisi ApiKeyAuth di paling atas?"
