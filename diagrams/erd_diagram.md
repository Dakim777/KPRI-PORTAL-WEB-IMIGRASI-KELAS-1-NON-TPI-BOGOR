# 4.5.5. Perancangan Basis Data (Database)

Rancangan tabel-tabel utama yang diterapkan pada basis data MySQL 8.0 Sistem Portal KPRI Kantor Imigrasi Kelas I Non-TPI Bogor adalah sebagai berikut:

---

#### Tabel 4.1. Struktur Tabel `users`

| Nama Field | Tipe Data | Keterangan |
| :--- | :--- | :--- |
| **id** | BigInt (PK) | Auto increment, identifier utama pengguna |
| **name** | VarChar(255) | Nama lengkap pengguna / petugas kasir |
| **email** | VarChar(255) | Email unik pengguna (kredensial login) |
| **password** | VarChar(255) | Kata sandi terenkripsi (Bcrypt) |
| **role** | Enum | Hak akses akun (`super_admin`, `kasir_paspor`) |
| **remember_token** | VarChar(100) | Token penjelajah untuk fitur *Remember Me* |
| **created_at** | Timestamp | Waktu pembuatan akun pengguna |
| **updated_at** | Timestamp | Waktu pembaruan data akun |
| **deleted_at** | Timestamp | Waktu penghapusan akun (*Soft Delete*) |

---

#### Tabel 4.2. Struktur Tabel `transaksi_paspors`

| Nama Field | Tipe Data | Keterangan |
| :--- | :--- | :--- |
| **id** | BigInt (PK) | Auto increment, primary key |
| **kasir_id** | BigInt (FK) | Relasi ke tabel `users.id` (Kasir pemproses) |
| **id_transaksi** | VarChar(50) | ID Unik Transaksi (format `KBG-YYMMDD-XXX`) |
| **no_struk** | Int | Nomor urut kwitansi transaksi |
| **nama_penerima** | VarChar(255) | Nama pemohon paspor (Terenkripsi AES-256) |
| **jumlah_paspor** | Int | Jumlah fisik buku paspor yang dikirim |
| **nama_paspor_list** | Text | Rincian nama pemilik paspor (Terenkripsi AES-256) |
| **alamat_tujuan_pengiriman** | Text | Alamat pengiriman paspor (Terenkripsi AES-256) |
| **no_hp** | Text | Nomor WhatsApp pemohon (Terenkripsi AES-256) |
| **no_resi** | VarChar(100) | Nomor resi pengiriman dari kurir (Nullable) |
| **jenis_kurir** | VarChar(50) | Ekspedisi kurir (`pos`, `jne`, `sicepat`, `tiki`, dll) |
| **layanan_kurir** | VarChar(100) | Jenis paket layanan ekspedisi kurir |
| **metode_pembayaran** | VarChar(50) | Metode pembayaran transaksi (`Tunai` / `QRIS`) |
| **total_pembayaran_pemohon** | Decimal(12,2) | Total tagihan pembayaran yang dibayar pemohon |
| **biaya_kurir** | Decimal(12,2) | Biaya ongkir asli dari pihak ekspedisi |
| **handling_fee** | Decimal(12,2) | Biaya jasa penanganan Koperasi (*Handling Fee*) |
| **tanggal_transaksi** | Date | Tanggal pencatatan transaksi paspor |
| **status_pengiriman** | Enum | Status alur (`transaksi`, `diproses`, `selesai`, `batal`) |
| **created_at** | Timestamp | Waktu pembuatan record transaksi |
| **updated_at** | Timestamp | Waktu pembaruan record transaksi |
| **deleted_at** | Timestamp | Waktu transaksi dihapus (*Soft Delete*) |

---

#### Tabel 4.3. Struktur Tabel `activity_log`

| Nama Field | Tipe Data | Keterangan |
| :--- | :--- | :--- |
| **id** | BigInt (PK) | Auto increment, primary key |
| **log_name** | VarChar(255) | Nama kategori log audit (`default`, `paspor`) |
| **description** | Text | Deskripsi rincian aktivitas operasional |
| **subject_type** | VarChar(255) | Nama model target yang diubah |
| **subject_id** | BigInt | ID record target yang diubah |
| **causer_type** | VarChar(255) | Nama model pengguna pelaksana aktivitas |
| **causer_id** | BigInt (FK) | ID user pelaksana (Relasi ke `users.id`) |
| **properties** | Json | Rekam nilai sebelum dan sesudah (*Old & New Values*) |
| **event** | VarChar(255) | Jenis kejadian (`created`, `updated`, `deleted`) |
| **created_at** | Timestamp | Waktu pencatatan aktivitas operasional |

---

#### Tabel 4.4. Struktur Tabel `notifications`

| Nama Field | Tipe Data | Keterangan |
| :--- | :--- | :--- |
| **id** | Char(36) (PK) | Primary key unik berformat UUID |
| **type** | VarChar(255) | Nama kelas notifikasi sistem Laravel |
| **notifiable_type** | VarChar(255) | Kelas model target penerima (`App\Models\User`) |
| **notifiable_id** | BigInt (FK) | ID user penerima notifikasi (Relasi ke `users.id`) |
| **data** | Text | Data payload notifikasi JSON (Zero PII Plaintext) |
| **read_at** | Timestamp | Waktu notifikasi dibaca pengguna (Nullable) |
| **created_at** | Timestamp | Waktu notifikasi dibuat |

---

#### Tabel 4.5. Struktur Tabel `sessions`

| Nama Field | Tipe Data | Keterangan |
| :--- | :--- | :--- |
| **id** | VarChar(255) (PK) | Primary key unik hash sesi browser |
| **user_id** | BigInt (FK) | ID user terautentikasi (Relasi ke `users.id`, Nullable) |
| **ip_address** | VarChar(45) | Alamat IP perangkat pengguna |
| **user_agent** | Text | Informasi browser & OS pengguna (*User Agent*) |
| **payload** | LongText | Data sesi login terenkripsi Laravel |
| **last_activity** | Int | Timestamp UNIX waktu aktivitas terakhir |

---

> 📌 **CATATAN ATURAN PENULISAN CAPTION KAMPUS:**  
> - **Caption Tabel:** Berada di **ATAS TABEL** dengan format `<bab>.<urutan_tabel>` (contoh: *Tabel 4.1. Struktur Tabel users*).  
> - **Caption Gambar:** Berada di **BAWAH GAMBAR** dengan format `<bab>.<urutan_gambar>` (contoh: *Gambar 4.1. Entity Relationship Diagram (ERD)*).
