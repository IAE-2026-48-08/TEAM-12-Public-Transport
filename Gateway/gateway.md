# API Gateway — TEAM 12 (Tubes IAE)

Gateway kelompok diimplementasikan menggunakan **Nginx Reverse Proxy**. Gateway bertindak sebagai pintu masuk tunggal (*Single Entry Point*) untuk semua layanan eksternal.

## 🗂️ Konfigurasi Berkas
Konfigurasi gateway diletakkan di berkas [nginx.conf](file:///c:/PROJECT,%20PRAKTIKUM,%20TUBES/TEAM-12-Public-Transport/Gateway/nginx.conf).

## 🚀 Peta Perutean (Routing Hub)
Semua client luar mengakses sistem melalui port `80` pada host gateway, yang kemudian mendistribusikan request ke port internal masing-masing service dalam jaringan Docker `team-12-network`:

| Path Request Luar | Service Tujuan | Container Tujuan (Internal Port 80) |
| :--- | :--- | :--- |
| `GET/POST /api/v1/schedules` | Rute & Jadwal Service (Alvin) | `rute-jadwal-web` |
| `GET/POST /api/v1/tickets` | Ticket & Pembayaran Service (Bayu) | `ticket-pembayaran-web` |
| `/central` | Integration Dashboard Web UI | `ticket-pembayaran-web` |
| Lainnya | Gateway Default | Mengembalikan JSON `404 Endpoint not found` |

## 🛠️ Cara Menjalankan
Gunakan file `docker-compose.yml` utama di root repositori kelompok Anda:
```bash
# 1. Jalankan seluruh service gabungan
docker compose up -d

# 2. Periksa status container
docker compose ps
```
