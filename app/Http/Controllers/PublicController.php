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

        // Cari transaksi berdasarkan no_resi, id_transaksi, atau no_struk
        $paspor = TransaksiPaspor::where('no_resi', $q)
            ->orWhere('id_transaksi', $q)
            ->orWhere('no_struk', $q)
            ->first();

        if (!$paspor) {
            // Karena nama_penerima terenkripsi di database, kita cari mencocokkan hasil dekripsi di PHP
            $paspor = TransaksiPaspor::all()->first(function ($t) use ($q) {
                try {
                    return strcasecmp(trim((string) $t->nama_penerima), trim($q)) === 0
                        || stripos((string) $t->nama_penerima, trim($q)) !== false;
                } catch (\Exception $e) {
                    return false;
                }
            });
        }

        if (!$paspor) {
            // Coba cek langsung ke BinderByte jika format terlihat seperti resi ekspedisi murni walau tidak ada di DB lokal
            if (strlen($q) >= 8) {
                $trackEkspedisi = $logistikService->lacakResi($q, 'pos');
                if (($trackEkspedisi['status'] ?? 0) == 200 && !empty($trackEkspedisi['data']['summary']) && empty($trackEkspedisi['is_fallback'])) {
                    return response()->json([
                        'status'  => 'success',
                        'is_mock' => $trackEkspedisi['is_mock'] ?? false,
                        'source'  => 'ekspedisi_direct',
                        'data'    => [
                            'summary' => $trackEkspedisi['data']['summary'],
                            'history' => $trackEkspedisi['data']['history'] ?? [],
                        ]
                    ]);
                }
            }

            return response()->json([
                'status'  => 'error',
                'message' => 'Data pengiriman paspor dengan nomor atau nama "' . htmlspecialchars($q) . '" tidak ditemukan.'
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
            // Status pembatalan: tampilkan alert pembatalan di atas, namun jika ada resi tetap coba lacak riwayat terakhirnya
            $noResiText = !empty($paspor->no_resi) && $paspor->no_resi !== '-' ? $paspor->no_resi : 'yang didaftarkan';
            $alertMessage = "Maaf, status pengiriman Anda telah dibatalkan dengan nomor resi yang diinputkan ({$noResiText}).";
            
            if (!empty($paspor->no_resi) && $paspor->no_resi !== '-') {
                $track = $logistikService->lacakResi($paspor->no_resi, strtolower($paspor->jenis_kurir ?? 'pos'));
                if (($track['status'] ?? 0) == 200 && !empty($track['data']['summary']) && empty($track['is_fallback'])) {
                    $manifestEkspedisi = [
                        'summary' => $track['data']['summary'],
                        'history' => $track['data']['history'] ?? [],
                    ];
                    $isMock = $track['is_mock'] ?? false;
                } else {
                    $manifestEkspedisi = [
                        'summary' => [
                            'awb'     => $paspor->no_resi,
                            'courier' => strtoupper($paspor->jenis_kurir ?? 'POS'),
                            'status'  => 'DIBATALKAN',
                            'date'    => $paspor->updated_at ? $paspor->updated_at->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s'),
                            'desc'    => "Pengiriman paspor atas nama penerima telah dibatalkan."
                        ],
                        'history' => [
                            [
                                'date'     => $paspor->updated_at ? $paspor->updated_at->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s'),
                                'desc'     => "Pengiriman paspor ini telah dibatalkan oleh petugas.",
                                'location' => 'Dibatalkan'
                            ]
                        ]
                    ];
                }
            }
        } elseif (in_array($statusInternal, ['diproses', 'selesai'])) {
            // Status diproses & selesai: pelacakan resi live ke ekspedisi / BinderByte dijalankan
            if (!empty($paspor->no_resi) && $paspor->no_resi !== '-') {
                $track = $logistikService->lacakResi($paspor->no_resi, strtolower($paspor->jenis_kurir ?? 'pos'));
                if (($track['status'] ?? 0) == 200 && !empty($track['data']['summary']) && empty($track['is_fallback'])) {
                    $manifestEkspedisi = [
                        'summary' => $track['data']['summary'],
                        'history' => $track['data']['history'] ?? [],
                    ];
                    $isMock = $track['is_mock'] ?? false;
                } else {
                    $statusSummary = $statusInternal === 'selesai' ? 'SELESAI / DELIVERED' : 'MANIFESTED / ON PROCESS';
                    $descSummary = $statusInternal === 'selesai' 
                        ? "Paspor atas nama penerima telah selesai dikirim dan diterima (Terverifikasi Sistem Koperasi)."
                        : "Nomor resi {$paspor->no_resi} (" . strtoupper($paspor->jenis_kurir ?? 'POS') . ") tercatat di loket Koperasi dan sedang dalam proses pengiriman.";
                    $locationSummary = $statusInternal === 'selesai' ? 'Alamat Tujuan (Selesai)' : 'Bogor / Dalam Pengiriman';

                    $manifestEkspedisi = [
                        'summary' => [
                            'awb'     => $paspor->no_resi,
                            'courier' => strtoupper($paspor->jenis_kurir ?? 'POS'),
                            'status'  => $statusSummary,
                            'date'    => $paspor->updated_at ? $paspor->updated_at->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s'),
                            'desc'    => $descSummary
                        ],
                        'history' => [
                            [
                                'date'     => $paspor->updated_at ? $paspor->updated_at->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s'),
                                'desc'     => $descSummary,
                                'location' => $locationSummary
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
