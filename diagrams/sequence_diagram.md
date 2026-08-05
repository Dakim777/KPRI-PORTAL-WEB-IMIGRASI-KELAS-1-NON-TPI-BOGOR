# DOKUMENTASI SEQUENCE DIAGRAM
## SISTEM PORTAL KOPERASI KANTOR IMIGRASI KELAS I NON-TPI BOGOR

---

> **FILE GRAPHICAL DRAW.IO DIAGRAM:**  
> Diagram visual Sequence Diagram ini berada dalam file **[`diagrams/sequence_diagram.drawio`](file:///Users/dakim/Herd/portal-koperasi/diagrams/sequence_diagram.drawio)**.  
> Silakan buka file `sequence_diagram.drawio` menggunakan [Draw.io (https://app.diagrams.net/)](https://app.diagrams.net/) atau aplikasi Draw.io Desktop untuk melihat dan mengunduh diagram urutan pesan bernomor lengkap dengan *Activation Bars*, *Lifelines*, *Combined Fragment Box*, dan format formal UML.

---

## 1. SEQUENCE DIAGRAM: PROSES TRANSAKSI PASPOR BARU & NOTIFIKASI WHATSAPP

Menggambarkan urutan pesan kronologis (*chronological message sequence*) dan masa hidup objek (*lifeline activation*) saat Kasir Paspor menginput data permohonan paspor baru hingga pesan notifikasi otomatis terkirim melalui WhatsApp Gateway.

### 📋 Daftar Partisipan Objek & Kelas (UML Component Registry):

| Class / Participant Identifier | Peran & Deskripsi Komponen |
| :--- | :--- |
| **`kasir : KasirPaspor`** | Petugas kasir yang berinteraksi langsung dengan antarmuka web untuk memasukkan transaksi paspor. |
| **`view : PasporView`** | Antarmuka Blade Laravel (`create.blade.php` & `receipt.blade.php`) yang menampilkan form input dan kwitansi. |
| **`controller : PasporController`** | Pengendali logika bisnis yang memvalidasi input, meng-generate ID unik `KBG-YYMMDD-XXX`, dan mengarahkan respons. |
| **`model : TransaksiPaspor`** | Model ORM Eloquent yang menangani enkripsi data sensitif (PII AES-256) secara otomatis saat penyimpanan. |
| **`db : MySQLDatabase`** | Mesin database relational yang menyimpan record transaksi secara permanen ke tabel `transaksi_paspors`. |
| **`waService : WhatsappService`** | Service layer Laravel yang mengonstruksi payload notifikasi tanpa PII plaintext (Zero PII Risk). |
| **`daemon : NodeDaemon`** | Daemon REST API Node.js Baileys yang berjalan secara lokal pada port 3001 (`wa-service`). |
| **`waNet : WANetwork`** | Server infrastruktur WhatsApp global yang menyampaikan pesan E2EE ke perangkat ponsel pemohon. |

---

### 🔄 Urutan Pesan Berurutan (*Message Sequence Matrix*):

| No. Pesan | Objek Pengirim (*Source*) | Objek Penerima (*Target*) | Jenis Pesan (*Message Type*) | Nama Fungsi / Parameter / Deskripsi |
| :---: | :--- | :--- | :--- | :--- |
| **1** | `kasir : KasirPaspor` | `view : PasporView` | Synchronous Call | `bukaFormInput(/paspor/create)` |
| **2** | `view : PasporView` | `kasir : KasirPaspor` | Return Signal (Dashed) | `renderFormInputView` |
| **3** | `kasir : KasirPaspor` | `view : PasporView` | Synchronous Call | `submitFormTransaksiData(data)` |
| **4** | `view : PasporView` | `controller : PasporController` | Synchronous Call (HTTP POST) | `POST /paspor (request data)` |
| **5** | `controller : PasporController` | `controller : PasporController` | Self Call (Internal) | `validateInput() & generateID("KBG-YYMMDD-XXX")` |
| **6** | `controller : PasporController` | `model : TransaksiPaspor` | Synchronous Call | `TransaksiPaspor::create($data) [Auto AES-256 Encrypt PII]` |
| **7** | `model : TransaksiPaspor` | `db : MySQLDatabase` | Synchronous Call (SQL) | `INSERT INTO transaksi_paspors VALUES (...)` |
| **8** | `db : MySQLDatabase` | `model : TransaksiPaspor` | Return Signal (Dashed) | `recordCreated(id)` |
| **9** | `model : TransaksiPaspor` | `controller : PasporController` | Return Signal (Dashed) | `return $transaksiInstance` |
| **10** | `controller : PasporController` | `waService : WhatsappService` | Synchronous Call (Opt Frame) | `sendNotification($transaksi)` |
| **11** | `waService : WhatsappService` | `daemon : NodeDaemon` | Synchronous Call (REST API) | `POST http://127.0.0.1:3001/send (JSON Payload)` |
| **12** | `daemon : NodeDaemon` | `waNet : WANetwork` | Synchronous Call (WebSocket) | `dispatchSignalE2EE(message)` |
| **13** | `waNet : WANetwork` | `daemon : NodeDaemon` | Return Signal (Dashed) | `ACK 200 OK (Delivered)` |
| **14** | `daemon : NodeDaemon` | `waService : WhatsappService` | Return Signal (Dashed) | `HTTP 200 {status: true}` |
| **15** | `waService : WhatsappService` | `controller : PasporController` | Return Signal (Dashed) | `notificationSent(true)` |
| **16** | `controller : PasporController` | `view : PasporView` | Return Signal (Redirect) | `redirect("/paspor/" . $id)` |
| **17** | `view : PasporView` | `kasir : KasirPaspor` | Return Signal (UI Render) | `renderDetailStrukView (Tombol Cetak Struk)` |

---

## 2. PENJELASAN ALUR FRAGMENT COMBINED (`opt [wa_notification_active = true]`)

Pada langkah ke-10 hingga 15, proses dibungkus ke dalam **Combined Fragment Frame (`opt`)**. Hal ini menunjukkan bahwa pengiriman notifikasi WhatsApp bersifat kondisional opsional:
- Jika daemon WhatsApp aktif (`wa-service` running pada port 3001), maka controller mengeksekusi pesan 10 hingga 15 untuk mengirimkan notifikasi instan ke HP pemohon.
- Jika daemon tidak aktif atau sesi terputus, sistem akan mengembalikan *graceful fallback* tanpa menghentikan pembuatan transaksi paspor utama (transaksi di database tetap sukses disimpan).

---

## 3. SEQUENCE DIAGRAM 2: PROSES PELACAKAN PASPOR PUBLIK (TANPA LOGIN)

Menggambarkan alur interaksi saat masyarakat (pemohon paspor) mengakses gerbang utama publik (`https://kpriimigrasibogor.my.id`) untuk melacak status paspor dan resi pengiriman.

**Urutan Pesan Kronologis:**
1. Pemohon membuka URL utama web (`GET /`).
2. `PublicView` merender form pencarian pelacakan paspor publik (tanpa autentikasi).
3. Pemohon memasukkan **ID Transaksi (`KBG-YYMMDD-XXX`)** atau **Nomor Resi (misal `POS...`)** lalu menekan tombol **"Lacak Paspor"**.
4. Request `GET /?q=...` dikirim ke `PublicController`.
5. `PublicController` melakukan pencarian ke `TransaksiPaspor` Model di database lokal MySQL.
6. Jika data ditemukan, controller memanggil `LogistikService` yang mengintegrasikan **BinderByte API**.
7. API memindai status pengiriman live dari **9 kurir ekspedisi** (POS Indonesia, JNE, SiCepat, TIKI, J&T, AnterAja, Lion Parcel, Ninja, ID Express).
8. Data timeline pelacakan dikembalikan dan dirender oleh `PublicView` ke layar HP/laptop pemohon.

---

## 4. SEQUENCE DIAGRAM 3: PROSES SCAN BARCODE RESI VIA WEBCAM

Menggambarkan alur kerja pemindaian resi ekspedisi secara cepat menggunakan kamera webcam pada perangkat kasir.

**Urutan Pesan Kronologis:**
1. Kasir menekan ikon **"Scan Barcode"** pada modal edit transaksi paspor.
2. Form view menginisialisasi modul JavaScript `Html5QrcodeScanner`.
3. Browser meminta izin akses perangkat media kamera (`getUserMedia API`).
4. Setelah izin diberikan, video stream dari webcam aktif dan memindai frame gambar secara real-time.
5. Mesin pencari `ZXing` mendeteksi barcode 1D / QR Code resi.
6. Saat resi berhasil terdeteksi, sistem membunyikan suara **beep notifikasi sukses** dan secara otomatis mengisikan nomor resi ke kolom input form tanpa ketik manual.
7. Kasir menekan **"Simpan Resi"**, dan request dikirim ke `PasporController` untuk memperbarui status transaksi.
