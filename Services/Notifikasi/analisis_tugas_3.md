# Analisis Progres Individu - Tugas 3 IAE
**Nama:** Renaya Marvela Adoe
**NIM:** 102022400154 
**Username SSO:** warga33@ktp.iae.id  
**Mini-Service:** Notifikasi Delay  
**Resource:** delays  

---

## 1. Justifikasi Transaksi Kritis (State-Changing)
Dalam arsitektur *The Enterprise Digital City*, mini-service **Notifikasi Delay** yang saya kembangkan menangani proses transaksi penting yang bersifat **State-Changing** (mengubah kondisi basis data/stok/status entitas secara permanen).

Transaksi kritis yang dipilih adalah: `POST /api/v1/delays`
* **Justifikasi Alasan Kritis:** Transaksi ini digunakan untuk menginput data kendala operasional atau penundaan waktu keberangkatan pada armada tertentu ke dalam database lokal. Transaksi ini menjadi titik awal (trigger) utama bagi seluruh proses bisnis notifikasi keterlambatan. Jika data delay berhasil dicatat, sistem akan memicu pencocokan kode jadwal dengan tiket aktif milik penumpang untuk mengirimkan notifikasi secara *real-time*. Apabila transaksi ini gagal atau terjadi kecurangan (*fraud*), maka seluruh rantai informasi keterlambatan ke penumpang akan terputus. Oleh karena itu, setiap kali aksi ini sukses dieksekusi, sistem wajib mengirimkan log audit ke *Legacy SOAP Server* milik pusat untuk keperluan kepatuhan (*compliance*) dan akuntabilitas.
* **Mekanisme Distribusi Event:** Setelah log audit berhasil dicatat oleh server pusat dan mendapatkan nomor resi resmi, notifikasi kejadian ini harus disebarluaskan secara asinkron ke departemen lain di lingkungan enterprise (seperti dashboard monitoring atau customer service) melalui *Message Broker (RabbitMQ)* menggunakan pertukaran data berformat JSON.

---

## 2. Integrasi Federated SSO

Sebelum petugas operasional dapat mengakses endpoint dan menginput data keterlambatan armada melalui `POST /api/v1/delays`, sistem akan menangkap payload JWT yang dikirimkan. Mini-service secara asinkron akan melakukan *request* ke *endpoint* JWKS pusat (`GET /api/v1/auth/jwks` atau `/.well-known/jwks.json`) untuk mengambil Public Key dengan algoritma RS256 guna memverifikasi keabsahan *signature* dari token tersebut secara lokal.

Setelah token terverifikasi valid, data klaim user di dalam JWT akan dipetakan ke dalam tabel peran (*roles*) internal sistem lokal.
Contoh role hasil pemetaan:
- `operator`
- `admin_operasional`

---

## 3. Integrasi SOAP Audit

Every successful `POST /api/v1/delays` transaction must be replicated to the legacy audit system via `POST /soap/v1/audit` endpoint by attaching the Bearer JWT token in the header.

Data transaksi JSON lokal akan ditransformasikan ke dalam format XML Envelope yang kaku. Struktur tag wajib yang dikirimkan meliputi:
- `<TeamID>`: Identitas tim lab (diisi sesuai dengan nomor tim kelompok).
- `<ActivityName>`: `DelayCreated` (Nama aktivitas bisnis penanda delay).
- `<LogContent>`: Data detail delay yang dibungkus menggunakan format `<![CDATA[ ... ]]>` berisi objek JSON mentah agar tidak merusak struktur XML Parser.

Sistem akan menerima response XML sukses berupa `<iae:Status>SUCCESS</iae:Status>` dan wajib menangkap serta menyimpan teks di dalam tag `<iae:ReceiptNumber>` (contoh: `IAE-LOG-2026-8891A7BC`) ke database lokal sebagai bukti sah audit.

---

## 4. Integrasi RabbitMQ

Setelah log audit berhasil diverifikasi dan disimpan, koordinasi asinkron antar-layanan dilakukan dengan mempublikasikan (*publish*) pesan notifikasi event bertipe JSON ke Message Broker pusat. Aksi ini ditujukan ke *endpoint* relay pusat (`POST /api/v1/messages/publish`) dengan target distribusi otomatis menuju exchange bernama **`iae.central.exchange`**.

Contoh payload event JSON yang dikirimkan:
```json
{
    "event": "delay_created",
    "delay_id": 1,
    "schedule_code": "SCH001",
    "delay_minutes": 30
}

sequenceDiagram
    autonumber
    actor Operator as Petugas Operasional
    participant SRV as Delay Service (Lokal)
    participant SSO as Cloud SSO (Pusat)
    participant SOAP as Legacy SOAP Audit (Pusat)
    participant MQ as RabbitMQ Central (Pusat)

    %% 1. Federated SSO Verification
    Operator->>SRV: POST /api/v1/delays (Membawa JWT Token)
    SRV->>SSO: GET /api/v1/auth/jwks (Request Public Keys RS256)
    SSO-->>SRV: Return JWKS Public Keys
    Note over SRV: Memvalidasi JWT & memetakan user ke role lokal

    %% 2. Core Process & SOAP Legacy Audit
    Note over SRV: Validasi Sukses & Input Data Delay ke DB Lokal
    SRV->>SOAP: POST /soap/v1/audit (XML Envelope + Bearer JWT)
    Note over SOAP: Memvalidasi TeamID, ActivityName, & CDATA JSON
    SOAP-->>SRV: Response XML (<iae:Status>SUCCESS & <iae:ReceiptNumber>)
    Note over SRV: Menyimpan ReceiptNumber ke Database Lokal

    %% 3. AMQP Event Publishing via Gateway Relay
    SRV->>MQ: POST /api/v1/messages/publish (Payload JSON ke iae.central.exchange)
    MQ-->>SRV: Response 200 OK / Success
    
    SRV-->>Operator: 201 Created (Data Sukses Dicatat + Receipt Number)