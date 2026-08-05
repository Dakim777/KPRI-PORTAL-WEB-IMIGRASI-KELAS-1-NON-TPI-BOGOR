# ACTIVITY DIAGRAM (DIAGRAM AKTIVITAS)
## KOPERASI KANTOR IMIGRASI KELAS I NON-TPI BOGOR

---

> **FILE DRAW.IO DIAGRAM:**
> Seluruh 3 diagram aktivitas ini berada dalam folder yang sama: `activity_diagram.drawio`.
> Silakan buka file `activity_diagram.drawio` menggunakan [Draw.io (https://app.diagrams.net/)](https://app.diagrams.net/) atau aplikasi Draw.io Desktop untuk melihat dan mengunduh diagram aktivitas UML dengan decision diamond dan alur panah yang sangat rapi.

---

## 1. ACTIVITY DIAGRAM 1: INPUT TRANSAKSI PASPOR DAN SCAN BARCODE (OPERASIONAL KASIR)

Diagram ini memetakan alur kerja petugas kasir mulai dari pengisian form data pemohon, pemindaian barcode resi via webcam, penyimpanan berenkripsi, hingga pencetakan struk termal.

**Alur Aktivitas:**
1. Kasir melakukan login ke sistem dashboard.
2. Kasir membuka menu "Tambah Paspor".
3. Kasir memilih jenis kurir dan layanan, kemudian sistem melakukan kalkulasi ongkir via API BinderByte.
4. Jika ongkir berhasil dihitung, kasir mengisi data pemohon (nama, alamat, no HP, rincian biaya).
5. Kasir dapat memindai barcode resi fisik via kamera webcam atau memasukkan nomor resi secara manual.
6. Kasir menekan tombol "Simpan Transaksi".
7. Server Laravel memvalidasi data. Jika valid, sistem meng-generate ID Transaksi unik (`KBG-YYMMDD-XXX`) dan mengenkripsi data sensitif (PII) menggunakan algoritma AES-256.
8. Data disimpan ke database MySQL.
9. Sistem memicu pengiriman notifikasi WhatsApp ke HP pemohon.
10. Kasir dapat mencetak struk bukti transaksi.

---

## 2. ACTIVITY DIAGRAM 2: PELACAKAN RESI PUBLIK (MASYARAKAT / TANPA LOGIN)

Diagram ini memetakan alur pencarian posisi paket paspor oleh pemohon melalui portal pelacakan publik tanpa perlu otentikasi.

**Alur Aktivitas:**
1. Pemohon membuka website utama (`https://kpriimigrasibogor.my.id`).
2. Pemohon memasukkan nomor resi atau ID Transaksi (`KBG-YYMMDD-XXX`) di kolom pencarian publik.
3. Pemohon menekan tombol "Lacak Paspor".
4. Sistem melakukan normalisasi input keyword dan mencari di database lokal (`transaksi_paspors`).
5. Jika data ditemukan, sistem secara otomatis menghubungi API BinderByte untuk melakukan scanning ke 9 kurir ekspedisi.
6. Jika data timeline resi berhasil didapatkan dari API, sistem menampilkan timeline tracking live.
7. Jika resi belum terindeks oleh pihak ekspedisi, sistem menampilkan pesan *Graceful Fallback Box* ("Belum Terindeks di POS / Ekspedisi").
8. Jika ID Transaksi tidak terdaftar di database, sistem menampilkan pesan "Data Tidak Ditemukan".

---

## 3. ACTIVITY DIAGRAM 3: PENGIRIMAN NOTIFIKASI WHATSAPP OTOMATIS (MICROSERVICE)

Diagram ini memetakan alur pemicuan notifikasi otomatis dari aplikasi Laravel menuju daemon Node.js Baileys hingga terkirim ke HP pemohon.

**Alur Aktivitas:**
1. Event pemicu notifikasi terjadi di backend Laravel (saat transaksi baru dibuat atau status diubah).
2. Laravel menyusun payload JSON yang aman (tanpa PII plaintext, hanya memuat ID Transaksi, No HP, dan direct track link).
3. Laravel mengeksekusi request HTTP POST via Guzzle Client ke daemon Node.js local (`http://127.0.0.1:3001/send`).
4. Server Express.js di Node.js menerima request dan memeriksa status koneksi WhatsApp (WebSocket Baileys Auth State).
5. Jika koneksi aktif, daemon mendispatch pesan E2EE melalui Protokol Signal WhatsApp ke nomor HP target.
6. WhatsApp mengembalikan konfirmasi pengiriman (Delivery ACK).
7. Node.js mengembalikan respon HTTP 200 OK ke Laravel.
8. Laravel mencatat aktivitas sukses pada Audit Log (`activity_log`).
