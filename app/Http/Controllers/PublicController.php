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
            // Coba cek langsung ke BinderByte jika format terlihat seperti resi ekspedisi murni walau tidak ada di DB lokal
            if (strlen($q) >= 8) {
                $trackEkspedisi = $logistikService->lacakResi($q, 'pos');
                if (($trackEkspedisi['status'] ?? 0) == 200 && !empty($trackEkspedisi['data']['summary'])) {
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
                'message' => 'Data pengiriman paspor dengan nomor "' . htmlspecialchars($q) . '" tidak ditemukan.'
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

        $kpriStatus = [
            'id_transaksi'             => $paspor->id_transaksi,
            'no_struk'                 => $paspor->no_struk,
            'nama_penerima_masked'     => $maskedNama,
            'jumlah_paspor'            => $paspor->jumlah_paspor,
            'tanggal_transaksi'        => $paspor->tanggal_transaksi ? $paspor->tanggal_transaksi->format('d/m/Y') : '-',
            'estimasi_tanggal_selesai' => $paspor->estimasi_tanggal_selesai ? $paspor->estimasi_tanggal_selesai->format('d/m/Y') : '-',
            'status_internal'          => $paspor->status_pengiriman, // transaksi, diproses, selesai, batal
            'no_resi'                  => $paspor->no_resi ?? null,
        ];

        $manifestEkspedisi = null;
        $isMock = false;

        if (!empty($paspor->no_resi)) {
            $track = $logistikService->lacakResi($paspor->no_resi, strtolower($paspor->jenis_kurir ?? 'pos'));
            if (($track['status'] ?? 0) == 200 && !empty($track['data']['summary'])) {
                $manifestEkspedisi = [
                    'summary' => $track['data']['summary'],
                    'history' => $track['data']['history'] ?? [],
                ];
                $isMock = $track['is_mock'] ?? false;
            } else {
                // Jika resi sudah ada tapi belum terindeks/tersinkron di server POS Indonesia
                $manifestEkspedisi = [
                    'summary' => [
                        'awb'     => $paspor->no_resi,
                        'courier' => strtoupper($paspor->jenis_kurir ?? 'POS'),
                        'status'  => 'BELUM TERINDEKS DI POS',
                        'date'    => $paspor->updated_at ? $paspor->updated_at->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s'),
                        'desc'    => 'Nomor resi tercatat pada sistem Koperasi Kantor Imigrasi Bogor, namun saat ini belum ditemukan pada pemindaian live POS Indonesia.'
                    ],
                    'history' => [
                        [
                            'date'     => $paspor->updated_at ? $paspor->updated_at->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s'),
                            'desc'     => 'Data transaksi pengiriman paspor dicatat di Koperasi (Menunggu sinkronisasi atau pemindaian fisik oleh kurir POS Indonesia)',
                            'location' => 'Bogor'
                        ]
                    ]
                ];
            }
        }

        return response()->json([
            'status'  => 'success',
            'is_mock' => $isMock,
            'source'  => 'kpri_system',
            'data'    => [
                'kpri'      => $kpriStatus,
                'ekspedisi' => $manifestEkspedisi
            ]
        ]);
    }
}
