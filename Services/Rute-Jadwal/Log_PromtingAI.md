# Laporan Penggunaan AI — Integrasi Aplikasi Enterprise

Nama: Alvin Hibatullah
NIM: 102022430022
Service: Rute & Jadwal

Catatan ini saya buat untuk merekam bagaimana saya memakai AI selama mengerjakan service Rute & Jadwal, dari Tugas 2 sampai Tugas 3. Secara garis besar, AI saya pakai untuk dua hal: bertanya soal konsep yang belum saya pahami dan minta dibantu menyusun kerangka kode. Selebihnya, kodenya saya jalankan, uji, dan perbaiki sendiri sampai benar-benar berfungsi.

## Bagian 1 — Tugas 2: Migrasi ke Docker, Seeder, dan Debugging API

Target saya di Tugas 2 adalah memindahkan service dari Laragon ke Docker (Laravel Sail), menyiapkan data awal lewat seeder, dan merapikan respons REST API-nya.

Kurang lebih ini prompt yang saya pakai selama prosesnya:
1. Menanyakan apakah Laragon harus dimatikan dulu kalau mau memakai Docker.
2. Menanyakan di mana password database bawaan tersimpan pada Docker Compose.
3. Mencari solusi error "command not defined" saat menjalankan Laravel Sail.
4. Meminta perintah alternatif karena muncul error bin/bash saat Sail up di PowerShell.
5. Memastikan URL yang benar untuk membuka Swagger dan GraphiQL.
6. Berdiskusi soal perlu-tidaknya memakai seeder untuk data jadwal.
7. Meminta solusi error MySQL "incorrect datetime value".
8. Mengirim screenshot error failed to fetch dan CORS di Swagger.
9. Mengirim isi Controller.php untuk dicek karena ada bug pada bagian URL.
10. Mengirim screenshot error 500 saat POST untuk memahami Mass Assignment.
11. Meminta bantuan merapikan catatan ini menjadi laporan.

Bagian yang paling memakan waktu justru debugging-nya, dan di situlah saya merasa paling banyak belajar. Awalnya saya mematikan Laragon dan membereskan error Composer supaya Docker tidak bentrok port. Saat membuat data dummy lewat seeder, sempat tertahan karena format waktunya salah — selesai setelah saya ubah ke YYYY-MM-DD HH:MM:SS. Lanjut ke Swagger, datanya tidak mau muncul karena CORS; ternyata ada cache URL yang nyangkut ke port 8000, yang saya perbaiki di bagian anotasi server. Yang paling bikin penasaran muncul saat menguji POST: tiba-tiba error 500. Setelah ditelusuri, itu mekanisme keamanan Laravel yang memblokir input data massal, dan solusinya cukup menambahkan properti fillable pada model.

Pola kerja saya konsisten: setiap ketemu error, langsung saya kirim screenshot tampilan beserta log terminalnya, lalu solusinya saya coba di Swagger sambil memperhatikan perubahan kode HTTP-nya. Di akhir Tugas 2, service sudah berjalan stabil di Docker dengan data seeder dan endpoint yang merespons benar.

## Bagian 2 — Tugas 3: Integrasi SSO, SOAP, dan RabbitMQ

Di Tugas 3, service ini saya sambungkan ke tiga sistem pusat: SSO (login JWT), audit SOAP, dan RabbitMQ. AI saya pakai untuk memahami konsep JWT, format XML SOAP, dan cara kerja message broker. Polanya sama seperti sebelumnya — tiap modul saya uji sendiri dulu sebelum digabung.

Modul 1 (SSO/JWT). Saya minta dibantu membuat middleware untuk memverifikasi token JWT dari pusat lalu memetakannya ke role lokal. AI membantu menyusun bagian pengambilan JWKS dan verifikasi tanda tangannya. Kendalanya lagi-lagi soal environment: Sail error di PowerShell jadi saya kembali ke PHP native, dan host database harus saya sesuaikan. Saya juga menemukan sendiri dari hasil pengujian bahwa nama field token-nya "token" dan Team ID saya TEAM-12. Akhirnya token berhasil diverifikasi dan data user tersimpan di tabel sso_users.

Modul 2 (SOAP). Saya minta bantuan membuat client yang mengubah JSON menjadi XML Envelope untuk dikirim ke sistem audit pusat, beserta class IaeAuditClient untuk mengambil receipt number-nya. Sebelum digabung, saya menguji script ini dulu lewat Tinker sampai resinya benar-benar tersimpan di tabel audit_logs.

Modul 3 (RabbitMQ). Saya minta dibuatkan publisher untuk mengirim event JSON. Saat pertama dites malah error 400 karena format payload-nya kurang pas — setelah event-nya saya bungkus ulang, schedule.created berhasil terkirim dan muncul di board pusat bersama receipt number-nya.

Penggabungan dan penyesuaian. Terakhir, saya gabungkan audit SOAP dan RabbitMQ ke dalam satu proses POST saat jadwal baru dibuat. Sempat error 500 karena saya lupa meng-import class publisher, tapi setelah diperbaiki, sekali submit langsung berjalan semuanya: validasi JWT, simpan jadwal, kirim audit SOAP, lalu publish event. Belakangan ada dua tambahan: dosen mewajibkan token M2M menyertakan field nim, jadi saya perbarui agar mengirim api_key dan nim; serta event saya yang tadinya tidak berlabel di board saya beri routing key supaya muncul label schedule.created.
