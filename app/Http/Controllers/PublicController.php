<?php

namespace App\Http\Controllers;

use App\Models\TransaksiPaspor;
use App\Services\LogistikService;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    /**
     * Tampilkan halaman depan portal publik untuk Lacak Resi / Permohonan Paspor.
     */
    public function index()
    {
        return view('public.index');
    }

    /**
     * API publik untuk melacak status paspor dan resi ekspedisi oleh masyarakat (tanpa login).
     */
    public function track(Request $request, LogistikService $logistikService)
    {
        $q = trim($request->input('q'));
        if (empty($q)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Silakan masukkan Nomor Resi atau ID Transaksi / Struk Anda.'
            ], 400);
        }

        // Normalisasi query input (bersihkan spasi & ubah ke huruf besar)
        $qClean = strtoupper(trim($q));

        // Cari transaksi berdasarkan no_resi, id_transaksi persis, atau no_struk
        $paspor = TransaksiPaspor::where('no_resi', $q)
            ->orWhere('id_transaksi', $q)
            ->orWhere('id_transaksi', $qClean)
            ->orWhere('no_struk', $q)
            ->first();

        // Jika tidak ketemu langsung, coba cocokkan id_transaksi tanpa tanda hubung (-) atau case-insensitive
        if (!$paspor) {
            $paspor = TransaksiPaspor::all()->first(function ($t) use ($qClean) {
                $idClean = str_replace('-', '', strtoupper((string) $t->id_transaksi));
                $queryClean = str_replace('-', '', $qClean);
                return $idClean !== '' && $idClean === $queryClean;
            });
        }

        $forceRefresh = $request->boolean('refresh');
        if (!$paspor) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Data pengiriman dengan ID Transaksi atau Nomor Resi "' . htmlspecialchars($q) . '" tidak ditemukan dalam sistem paspor kami. Silakan periksa nomor pada Struk atau WhatsApp Anda.'
            ], 404);
        }

        // Masking nama penerima demi perlindungan data pribadi (GDPR / UU PDP)
        // Contoh: "Budi Santoso" -> "Bud*** ***oso"
        $namaPenerima = $paspor->nama_penerima;
        $words = explode(' ', $namaPenerima);
        $maskedWords = array_map(function ($word) {
            $len = strlen($word);
            if ($len <= 2) return $word;
            return substr($word, 0, 2) . str_repeat('*', max($len - 3, 1)) . substr($word, -1);
        }, $words);
        $maskedNama = implode(' ', $maskedWords);

        $statusInternal = $paspor->status_pengiriman; // 'transaksi', 'diproses', 'selesai', 'batal'

        $kpriStatus = [
            'id_transaksi'             => $paspor->id_transaksi,
            'no_struk'                 => $paspor->no_struk,
            'nama_penerima_masked'     => $maskedNama,
            'jumlah_paspor'            => $paspor->jumlah_paspor,
            'tanggal_transaksi'        => $paspor->tanggal_transaksi ? $paspor->tanggal_transaksi->format('d/m/Y') : '-',
            'estimasi_tanggal_selesai' => $paspor->estimasi_tanggal_selesai ? $paspor->estimasi_tanggal_selesai->format('d/m/Y') : '-',
            'status_internal'          => $statusInternal,
            'no_resi'                  => $paspor->no_resi ?? null,
        ];

        $manifestEkspedisi = null;
        $isMock = false;
        $messageStatus = $statusInternal;
        $alertMessage = null;

        if ($statusInternal === 'transaksi') {
            // Status transaksi: paspor sedang dalam masa pembuatan/pencetakan (pelacakan resi BELUM boleh dijalankan)
            $alertMessage = 'Paspor Anda masih dalam status TRANSAKSI. Artinya paspor sedang dalam masa pembuatan atau pencetakan di Kantor Imigrasi. Silahkan periksa kembali nanti setelah nomor resi pengiriman resmi dirilis dan dikirimkan ke WhatsApp Anda.';
        } elseif ($statusInternal === 'batal') {
            // Status pembatalan: tampilkan alert pembatalan dan tidak memuat riwayat pengiriman
            $noResiText = !empty($paspor->no_resi) && $paspor->no_resi !== '-' ? $paspor->no_resi : 'yang didaftarkan';
            $alertMessage = "Maaf, status pengiriman paspor Anda telah DIBATALKAN. Pengiriman atau pemrosesan paspor dengan nomor resi ({$noResiText}) tidak dilanjutkan dan riwayat pelacakan tidak dapat diakses.";
        }

        // Khusus untuk status PROSES dan SELESAI: lakukan fetching real-time ke BinderByte
        if (in_array($statusInternal, ['diproses', 'selesai'])) {
            if (!empty($paspor->no_resi) && $paspor->no_resi !== '-') {
                $track = $logistikService->lacakResi($paspor->no_resi, strtolower($paspor->jenis_kurir ?? 'pos'), $forceRefresh);
                if (($track['status'] ?? 0) == 200 && !empty($track['data']['summary']) && empty($track['is_fallback'])) {
                    $manifestEkspedisi = [
                        'summary' => $track['data']['summary'],
                        'history' => $track['data']['history'] ?? [],
                    ];
                    $isMock = $track['is_mock'] ?? false;
                } else {
                    // Fallback jujur tanpa asumsi (sesuai arahan: minta refresh/periksa kembali tanpa membuat asumsi palsu status kurir)
                    $manifestEkspedisi = [
                        'summary' => [
                            'awb'     => $paspor->no_resi,
                            'courier' => strtoupper($paspor->jenis_kurir ?? 'POS'),
                            'status'  => 'GAGAL MEMUAT / BELUM TERINDEKS',
                            'date'    => now()->format('Y-m-d H:i:s'),
                            'desc'    => "Gagal memuat data pengiriman secara real-time dari server ekspedisi (" . strtoupper($paspor->jenis_kurir ?? 'POS') . ") atau nomor resi belum terindeks di BinderByte. Silahkan refresh kembali halaman ini atau periksa beberapa saat lagi."
                        ],
                        'history' => [
                            [
                                'date'     => now()->format('Y-m-d H:i:s'),
                                'desc'     => "Gagal memuat riwayat pergerakan paket dari server ekspedisi (" . strtoupper($paspor->jenis_kurir ?? 'POS') . "). Silahkan refresh kembali halaman atau coba beberapa saat lagi.",
                                'location' => 'Server Ekspedisi / Belum Terindeks'
                            ]
                        ]
                    ];
                }
            }
        }

        return response()->json([
            'status'  => 'success',
            'is_mock' => $isMock,
            'source'  => 'kpri_system',
            'data'    => [
                'kpri'           => $kpriStatus,
                'message_status' => $messageStatus,
                'alert_message'  => $alertMessage,
                'ekspedisi'      => $manifestEkspedisi
            ]
        ]);
    }
}
