<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappService
{
    protected string $baseUrl;

    public function __construct()
    {
        $port = env('WA_PORT', 3001);
        $this->baseUrl = "http://127.0.0.1:{$port}";
    }

    /**
     * Cek status koneksi bot WhatsApp lokal dan ambil QR Code terbaru.
     *
     * @return array
     */
    public function getStatus(): array
    {
        try {
            $response = Http::timeout(3)->get("{$this->baseUrl}/status");
            if ($response->successful()) {
                return $response->json();
            }
            return [
                'status' => 'disconnected',
                'phone'  => null,
                'qr'     => null,
                'qr_data_url' => null,
                'error'  => 'Service lokal mengembalikan status tidak valid.'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'offline',
                'phone'  => null,
                'qr'     => null,
                'qr_data_url' => null,
                'error'  => 'Service bot WhatsApp lokal di port 3001 belum dijalankan.'
            ];
        }
    }

    /**
     * Kirim pesan teks WhatsApp ke nomor tujuan.
     *
     * @param string $phone Nomor HP tujuan (08xxx / +628xxx)
     * @param string $message Isi pesan
     * @return array
     */
    public function send(string $phone, string $message): array
    {
        try {
            $response = Http::timeout(35)->post("{$this->baseUrl}/send", [
                'phone'   => $phone,
                'message' => $message
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            return [
                'status'  => 'error',
                'message' => $response->json()['message'] ?? 'Gagal mengirim pesan via bot WhatsApp.'
            ];
        } catch (\Exception $e) {
            Log::error('Error WhatsappService send: ' . $e->getMessage());
            return [
                'status'  => 'error',
                'message' => 'Service bot WhatsApp lokal tidak responsif (' . $e->getMessage() . '). Pastikan bot sudah jalan.'
            ];
        }
    }

    /**
     * Putuskan koneksi (Logout) dan reset sesi di bot lokal agar QR Code baru dibuat.
     *
     * @return array
     */
    public function logout(): array
    {
        try {
            $response = Http::timeout(5)->post("{$this->baseUrl}/logout");
            if ($response->successful()) {
                return $response->json();
            }
            return [
                'status'  => 'error',
                'message' => 'Gagal mereset sesi bot.'
            ];
        } catch (\Exception $e) {
            return [
                'status'  => 'error',
                'message' => 'Bot WhatsApp lokal tidak aktif.'
            ];
        }
    }

    /**
     * Kirim notifikasi resi otomatis ke pemohon paspor.
     *
     * @param \App\Models\TransaksiPaspor $transaksi
     * @return array
     */
    public function sendNotificationResi($transaksi): array
    {
        $phone = $transaksi->nomor_hp_pemohon;
        if (empty($phone)) {
            return [
                'status'  => 'error',
                'message' => 'Nomor HP pemohon kosong.'
            ];
        }

        $nama = $transaksi->nama_pemohon ?? 'Pemohon';
        $noResi = $transaksi->nomor_resi ?? '-';
        $kurir = strtoupper($transaksi->jenis_kurir ?? 'POS');
        $layanan = $transaksi->layanan_kurir ?? 'Reguler';
        $status = strtoupper($transaksi->status ?? 'DIPROSES');

        $urlLacak = url('/paspor/track/' . ($noResi !== '-' ? $noResi : $transaksi->kode_transaksi));

        $pesan  = "*KPRI KANTOR IMIGRASI KELAS I NON-TPI BOGOR*\n";
        $pesan .= "---------------------------------------------\n";
        $pesan .= "Halo *{$nama}*,\n\n";
        $pesan .= "Informasi terbaru mengenai pengiriman paspor Anda melalui layanan Koperasi:\n\n";
        $pesan .= "📋 *Status:* {$status}\n";
        $pesan .= "🚚 *Kurir:* {$kurir} - {$layanan}\n";
        $pesan .= "📦 *No. Resi:* *{$noResi}*\n\n";
        if ($noResi !== '-') {
            $pesan .= "🔗 *Lacak Pengiriman Secara Real-Time:* \n{$urlLacak}\n\n";
        }
        $pesan .= "_Terima kasih telah menggunakan layanan pengiriman Koperasi Kantor Imigrasi Bogor. Pesan ini dikirim secara otomatis oleh sistem._";

        return $this->send($phone, $pesan);
    }
}
