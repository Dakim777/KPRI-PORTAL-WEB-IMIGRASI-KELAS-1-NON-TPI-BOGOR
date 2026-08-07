<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class LogistikService
{
    /**
     * Cek Tarif Ongkos Kirim via RajaOngkir Starter API (Gratis 100 cek/hari, reset 00:00).
     *
     * @param int|string $destination ID Kota/Kabupaten tujuan pemohon paspor
     * @param int $weight Berat paspor dalam gram (default +/- 200g)
     * @param string $courier Kode kurir: 'pos', 'jne', atau 'tiki'
     * @param int|string $origin ID Kota/Kabupaten asal (default 149 = Kota Bogor)
     * @return array
     */
    public function cekOngkir($destination, int $weight = 200, string $courier = 'pos', $origin = 'dist_32.71.01'): array
    {
        $apiKey = config('services.binderbyte.key');
        $baseUrl = config('services.binderbyte.base_url', 'https://api.binderbyte.com/v1');

        if (empty($apiKey) || $apiKey === 'isi_api_key_binderbyte_anda_disini') {
            // Mode simulasi/mock jika API Key belum dipasang di .env
            return [
                'status'  => 'success',
                'is_mock' => true,
                'message' => 'API Key BinderByte belum dipasang di .env (Mode Simulasi Tarif)',
                'data'    => [
                    'courier'      => strtoupper($courier),
                    'service'      => 'Kilat Khusus / Reguler',
                    'biaya_ongkir' => 15000,
                    'etd'          => '1-2 Hari',
                ]
            ];
        }

        // Peta konversi otomatis dari ID lama/teks ke ID dist_ resmi BinderByte
        $destMap = [
            'bogor'        => 'dist_32.71.01',
            '149'          => 'dist_32.71.01',
            '80'           => 'dist_32.01.01',
            'depok'        => 'dist_32.76.01',
            '114'          => 'dist_32.76.01',
            'jakarta'      => 'dist_31.71.01',
            '153'          => 'dist_31.71.01',
            '152'          => 'dist_31.73.01',
            '150'          => 'dist_31.72.01',
            '151'          => 'dist_31.74.01',
            '154'          => 'dist_31.75.01',
            'bekasi'       => 'dist_32.75.01',
            '40'           => 'dist_32.75.01',
            '39'           => 'dist_32.75.01',
            'tangerang'    => 'dist_36.71.01',
            '455'          => 'dist_36.71.01',
            '456'          => 'dist_36.74.01',
            'sukabumi'     => 'dist_32.72.01',
            '421'          => 'dist_32.72.01',
            '420'          => 'dist_32.02.01',
            'bandung'      => 'dist_32.73.01',
            '23'           => 'dist_32.73.01',
            'semarang'     => 'dist_33.74.01',
            '399'          => 'dist_33.74.01',
            'yogyakarta'   => 'dist_34.71.01',
            '501'          => 'dist_34.71.01',
            'solo'         => 'dist_33.72.01',
            '419'          => 'dist_33.72.01',
            'surabaya'     => 'dist_35.78.01',
            '444'          => 'dist_35.78.01',
            'malang'       => 'dist_35.73.01',
            '256'          => 'dist_35.73.01',
            'denpasar'     => 'dist_51.71.01',
            'medan'        => 'dist_12.71.01',
            'palembang'    => 'dist_16.71.01',
            'pekanbaru'    => 'dist_14.71.01',
            'batam'        => 'dist_21.71.01',
            'padang'       => 'dist_13.71.01',
            'lampung'      => 'dist_18.71.01',
            'makassar'     => 'dist_73.71.01',
            'manado'       => 'dist_71.71.01',
            'balikpapan'   => 'dist_64.71.01',
            'samarinda'    => 'dist_64.72.01',
            'pontianak'    => 'dist_61.71.01',
            'banjarmasin'  => 'dist_63.71.01',
        ];

        if (strpos((string) $destination, 'dist_') !== 0) {
            $destination = $destMap[(string) $destination] ?? 'dist_32.71.01';
        }
        if (strpos((string) $origin, 'dist_') !== 0) {
            $origin = $destMap[(string) $origin] ?? 'dist_32.71.01';
        }

        try {
            // Konversi weight ke kg (minimum 1 kg standar ekspedisi via BinderByte)
            $weightKg = max(1, (int) ceil($weight / 1000));

            // Penambahan waktu buffering dan retry untuk ketahanan koneksi cek tarif
            $response = Http::asForm()
                ->timeout(45)
                ->connectTimeout(15)
                ->retry(3, 1500, function ($exception, $request) {
                    return $exception instanceof \Illuminate\Http\Client\ConnectionException || $exception instanceof \Illuminate\Http\Client\RequestException;
                })
                ->post("{$baseUrl}/cost", [
                    'api_key'     => $apiKey,
                    'origin'      => $origin,
                    'destination' => $destination,
                    'weight'      => $weightKg,
                    'courier'     => strtolower($courier),
                ]);

            if ($response->successful()) {
                $result = $response->json();
                if (($result['status'] ?? 0) == 200 || ($result['code'] ?? '') == '200' || isset($result['data']['results'])) {
                    $results = $result['data']['results'] ?? [];
                    if (!empty($results)) {
                        $costs = $results[0]['costs'] ?? [];
                        if (!empty($costs)) {
                            $firstService = $costs[0];
                            return [
                                'status'  => 'success',
                                'is_mock' => false,
                                'data'    => [
                                    'courier'      => strtoupper($courier),
                                    'service'      => $firstService['service'] ?? ($firstService['description'] ?? 'REG'),
                                    'biaya_ongkir' => (int) ($firstService['cost'] ?? ($firstService['price'] ?? 0)),
                                    'etd'          => $firstService['etd'] ?? '1-2 Hari',
                                ]
                            ];
                        }
                    }
                }

                return [
                    'status'  => 'error',
                    'message' => 'Layanan kurir ' . strtoupper($courier) . ' saat ini tidak menyediakan pengantaran atau belum memiliki data tarif live ke daerah tersebut di pangkalan data resmi BinderByte. Silakan pilih ekspedisi atau daerah lain.'
                ];
            }

            $errorDesc = $response->json()['message'] ?? $response->body();
            if ($errorDesc === 'Successfully calculated cost') {
                $errorDesc = 'Rute pengantaran ke wilayah tersebut tidak tersedia untuk kurir ' . strtoupper($courier);
            }
            return [
                'status'  => 'error',
                'message' => 'Layanan kurir ' . strtoupper($courier) . ' tidak menyediakan rute pengantaran ke daerah tersebut: ' . $errorDesc
            ];
        } catch (\Exception $e) {
            Log::error('Error BinderByte cekOngkir live: ' . $e->getMessage());
            return [
                'status'  => 'error',
                'message' => 'Gagal terhubung ke server resmi BinderByte (' . $e->getMessage() . '). Periksa koneksi internet atau API Key Anda.'
            ];
        }
    }

    /**
     * Lacak Resi Pengiriman Paspor via BinderByte API (+ Cache 2 Jam agar sangat hemat credit).
     *
     * @param string $nomorResi Nomor resi / AWB ekspedisi
     * @param string $courier Kode kurir (pos, jne, jnt, sicepat, dll.)
     * @return array
     */
    public function lacakResi(string $nomorResi, string $courier = 'pos', bool $forceRefresh = false): array
    {
        $nomorResi = trim($nomorResi);
        if (empty($nomorResi)) {
            return [
                'status'  => 'error',
                'message' => 'Nomor resi tidak boleh kosong.'
            ];
        }

        $courier = strtolower(trim($courier));
        if (empty($courier)) {
            $courier = 'pos';
        }

        $cacheKey = "tracking_resi_{$courier}_{$nomorResi}";
        if (!$forceRefresh) {
            $cached = Cache::get($cacheKey);
            if ($cached && !empty($cached['data']['summary']) && empty($cached['is_fallback']) && empty($cached['is_error'])) {
                return $this->enrichTrackingResult($cached);
            }
        }

        $apiKey = config('services.binderbyte.key');
        $baseUrl = config('services.binderbyte.base_url', 'https://api.binderbyte.com/v1');

        if (empty($apiKey)) {
            // Mode simulasi/mock jika BINDERBYTE_API_KEY belum dipasang di .env
            return $this->enrichTrackingResult([
                'status'  => 200,
                'message' => 'API Key BinderByte belum dipasang di .env (Mode Simulasi Lacak Resi)',
                'is_mock' => true,
                'data'    => [
                    'summary' => [
                        'awb'     => $nomorResi,
                        'courier' => strtoupper($courier),
                        'status'  => 'ON PROCESS',
                        'date'    => now()->format('Y-m-d H:i:s'),
                        'desc'    => 'Paket sedang dalam proses pengantaran ke alamat pemohon paspor'
                    ],
                    'history' => [
                        [
                            'date' => now()->format('Y-m-d H:i:s'),
                            'desc' => 'Paket dibawa oleh kurir ' . strtoupper($courier) . ' menuju alamat pemohon (Kota Bogor / Sekitar)',
                            'location' => 'Bogor'
                        ],
                        [
                            'date' => now()->subHours(3)->format('Y-m-d H:i:s'),
                            'desc' => 'Paket telah diterima di Kantor Cabang Pengirim ' . strtoupper($courier),
                            'location' => 'Kantor POS Bogor'
                        ]
                    ]
                ]
            ]);
        }

        try {
            // Penambahan waktu buffering (timeout 45 detik + connectTimeout 15 detik + auto-retry 3 kali)
            // agar saat jaringan atau server ekspedisi sedang lambat, sistem tidak cepat menyerah (fallback) dan mencegah user bolak-balik refresh.
            $response = Http::timeout(45)
                ->connectTimeout(15)
                ->retry(3, 1500, function ($exception, $request) {
                    return $exception instanceof \Illuminate\Http\Client\ConnectionException || $exception instanceof \Illuminate\Http\Client\RequestException;
                })
                ->get("{$baseUrl}/track", [
                    'api_key' => $apiKey,
                    'courier' => $courier,
                    'awb'     => $nomorResi,
                ]);

            if ($response->successful() && !empty($response->json()['data']['summary'])) {
                $json = $response->json();
                $json['is_mock'] = false;
                $json['is_fallback'] = false;
                $json = $this->enrichTrackingResult($json);
                // Cache hasil sukses: jika sudah DELIVERED cache 2 jam, jika masih proses cache 2 menit agar real-time dan akurat
                $statusUpper = strtoupper(trim((string) ($json['data']['summary']['status'] ?? '')));
                $ttlMinutes = ($statusUpper === 'DELIVERED' || strpos($statusUpper, 'SELESAI') !== false) ? 120 : 2;
                Cache::put($cacheKey, $json, $ttlMinutes * 60);
                return $json;
            }
        } catch (\Exception $e) {
            Log::warning("Gangguan/Timeout API BinderByte lacakResi ({$nomorResi} via {$courier}): " . $e->getMessage());
        }

        // Fallback jujur tanpa asumsi status kurir (sesuai arahan: tidak ada asumsi, minta refresh/periksa lagi)
        return $this->enrichTrackingResult([
            'status'      => 200,
            'message'     => 'Gagal memuat data pengiriman dari server ekspedisi atau resi belum terindeks',
            'is_fallback' => true,
            'is_mock'     => false,
            'data'        => [
                'summary' => [
                    'awb'     => $nomorResi,
                    'courier' => strtoupper($courier),
                    'status'  => 'GAGAL MEMUAT / BELUM TERINDEKS',
                    'date'    => now()->format('Y-m-d H:i:s'),
                    'desc'    => "Gagal memuat data pengiriman secara real-time dari server ekspedisi (" . strtoupper($courier) . ") atau nomor resi belum terindeks di BinderByte. Silahkan refresh kembali halaman ini atau periksa beberapa saat lagi."
                ],
                'history' => [
                    [
                        'date'     => now()->format('Y-m-d H:i:s'),
                        'desc'     => "Gagal memuat riwayat pergerakan paket dari server ekspedisi (" . strtoupper($courier) . "). Silahkan refresh kembali halaman atau coba beberapa saat lagi.",
                        'location' => 'Server Ekspedisi / Belum Terindeks'
                    ]
                ]
            ]
        ]);
    }

    /**
     * Normalisasi deskripsi dan terjemahan status agar riwayat pengiriman detail, real, dan mudah dipahami dalam bahasa Indonesia.
     */
    protected function enrichTrackingResult(array $result): array
    {
        if (empty($result['data']['summary'])) {
            return $result;
        }

        $summary = $result['data']['summary'];
        $history = $result['data']['history'] ?? [];

        // Terjemahan/pemetaan status utama dari bahasa Inggris ke Bahasa Indonesia yang rapi (tetap mempertahankan kode aslinya)
        $rawStatus = strtoupper(trim((string) ($summary['status'] ?? 'PROSES')));
        $statusMap = [
            'DELIVERED'               => 'SELESAI / TERKIRIM (DELIVERED)',
            'ON PROCESS'              => 'SEDANG DIPROSES / ON PROCESS',
            'ONPROCESS'               => 'SEDANG DIPROSES / ON PROCESS',
            'TRANSIT'                 => 'DALAM PERJALANAN / TRANSIT',
            'MANIFESTED'              => 'TERCATAT DI LOKET / MANIFESTED',
            'OUT FOR DELIVERY'        => 'SEDANG DIANTAR KURIR / OUT FOR DELIVERY',
            'RECEIVED ON DESTINATION' => 'TIBA DI KOTA TUJUAN / RECEIVED',
            'RETURNED'                => 'DIKEMBALIKAN KE PENGIRIM (RETURNED)',
            'FAILED'                  => 'GAGAL DIANTAR / FAILED',
        ];
        $summary['status_indonesia'] = $statusMap[$rawStatus] ?? $rawStatus;
        
        // Pastikan deskripsi real terkini dari ekspedisi selalu terisi dan akurat serta diterjemahkan ke gaya rinci Shopee/Tokopedia
        $rawSummaryDesc = $summary['desc'] ?? ($summary['description'] ?? ($summary['message'] ?? ($summary['note'] ?? $rawStatus)));
        $summary['desc'] = $this->translateToShopeeStyleIndonesian((string) $rawSummaryDesc, (string) ($summary['location'] ?? ''), $rawStatus);

        // Normalisasi seluruh riwayat (history) agar deskripsi real dari ekspedisi bergaya Shopee/Tokopedia
        if (is_array($history)) {
            foreach ($history as $idx => $h) {
                $rawDesc = $h['desc'] ?? ($h['description'] ?? ($h['note'] ?? ($h['message'] ?? ($h['status'] ?? '-'))));
                $loc = (string) ($h['location'] ?? ($h['city'] ?? ''));
                
                // Jika deskripsi hanya berisi satu kata status Inggris standar (seperti DELIVERED / ON PROCESS), berikan penjelasan terjemahannya
                $upperDesc = strtoupper(trim((string) $rawDesc));
                if (isset($statusMap[$upperDesc])) {
                    $rawDesc = $statusMap[$upperDesc];
                }
                
                $history[$idx]['desc'] = $this->translateToShopeeStyleIndonesian((string) $rawDesc, $loc, (string) ($h['status'] ?? ''));
                $history[$idx]['date'] = $h['date'] ?? ($h['time'] ?? ($h['timestamp'] ?? '-'));
                $history[$idx]['location'] = $loc;
            }
        }

        $result['data']['summary'] = $summary;
        $result['data']['history'] = $history;

        return $result;
    }

    /**
     * Menerjemahkan deskripsi ekspedisi ke dalam bahasa Indonesia bergaya rinci (Shopee/Tokopedia style)
     * agar pemohon/kasir langsung paham posisi dan status detail paket secara akurat.
     */
    protected function translateToShopeeStyleIndonesian(string $rawDesc, string $location = '', string $status = ''): string
    {
        $desc = trim($rawDesc);
        if (empty($desc) || $desc === '-') {
            return '-';
        }

        $upper = strtoupper($desc);
        $locText = !empty($location) ? " ({$location})" : "";

        // 1. Cek pola penerimaan / delivered
        if (preg_match('/(?:DELIVERED TO|RECEIVED BY|DITERIMA OLEH|ITEM HAS BEEN DELIVERED(?: TO)?)\s*[:\-]?\s*(.*)/i', $desc, $matches)) {
            $penerima = trim($matches[1] ?? '');
            if (!empty($penerima) && !in_array(strtoupper($penerima), ['CUSTOMER', 'RECIPIENT', 'PENERIMA', 'YANG BERSANGKUTAN'])) {
                return "Paket telah sampai dan berhasil diterima oleh [{$penerima}]{$locText}.";
            }
            return "Paket telah sampai dan berhasil diterima oleh penerima di alamat tujuan{$locText}.";
        }
        if (in_array($upper, ['DELIVERED', 'ITEM HAS BEEN DELIVERED', 'PAKET TELAH DITERIMA', 'SELESAI', 'ITEM HAS BEEN DELIVERED TO CUSTOMER'])) {
            return "Paket telah sampai dan berhasil diterima oleh penerima di alamat tujuan{$locText}.";
        }

        // 2. Cek pola pengantaran kurir ke alamat (Out for Delivery)
        if (preg_match('/(?:OUT FOR DELIVERY|WITH DELIVERY COURIER|ITEM IS ON THE WAY TO CUSTOMER|COURIER IS DELIVERING|PENGANTARAN KIRIMAN OLEH KURIR|DIANTAR OLEH KURIR)/i', $desc)) {
            return "Kurir ekspedisi sedang dalam perjalanan mengantar paket menuju alamat tujuan penerima{$locText}.";
        }

        // 3. Cek pola penjemputan (Pick up / Counter pick up)
        if (preg_match('/(?:COUNTER IS ON THE WAY TO PICK UP ITEM|PICK UP|PICKED UP|KURIR SEDANG MENUJU LOKASI PENJEMPUTAN|PENJEMPUTAN PAKET)/i', $desc)) {
            return "Kurir/Petugas ekspedisi sedang menuju lokasi kantor pengirim untuk melakukan penjemputan paket.";
        }

        // 4. Cek pola pendaftaran / manifest di loket kantor pengirim
        if (preg_match('/(?:SHIPMENT HAS BEEN MANIFESTED|MANIFESTED|PENERIMAAN KIRIMAN DI LOKET|ITEM ACCEPTED BY COURIER|PACKAGE RECEIVED AT WAREHOUSE)/i', $desc)) {
            return "Paket telah diserahkan dan terdaftar di loket/gudang ekspedisi pengirim{$locText}.";
        }

        // 5. Cek pola keberangkatan dari hub/transit (Departed from / Leaving hub / Scan out)
        if (preg_match('/(?:DEPARTED FROM|LEAVING|BERANGKAT DARI|KELUAR DARI|PROSES SCAN OUT|DISPATCHED TO)/i', $desc)) {
            $cleanDetail = preg_replace('/^(?:DEPARTED FROM|LEAVING|BERANGKAT DARI|KELUAR DARI|DISPATCHED TO)\s*[:\-]?\s*/i', '', $desc);
            if (!empty($cleanDetail) && strcasecmp($cleanDetail, $desc) !== 0 && strlen($cleanDetail) > 2) {
                return "Paket telah berangkat dari pusat transit/sortir ({$cleanDetail}) menuju kota berikutnya.";
            }
            return "Paket telah berangkat dari pusat transit/sortir ekspedisi{$locText} menuju tahap pengiriman berikutnya.";
        }

        // 6. Cek pola ketibaan di hub/transit (Arrived at / Received at sorting center / Scan in)
        if (preg_match('/(?:ARRIVED AT|ARRIVING|TIBA DI|RECEIVED AT SORTING CENTER|PROSES SCAN IN|MASUK KE)/i', $desc)) {
            $cleanDetail = preg_replace('/^(?:ARRIVED AT|ARRIVING|TIBA DI|RECEIVED AT SORTING CENTER|PROSES SCAN IN)\s*[:\-]?\s*/i', '', $desc);
            if (!empty($cleanDetail) && strcasecmp($cleanDetail, $desc) !== 0 && strlen($cleanDetail) > 2) {
                return "Paket telah tiba di pusat transit/sortir ekspedisi ({$cleanDetail}).";
            }
            return "Paket telah tiba di pusat transit/sortir ekspedisi{$locText}.";
        }

        // 7. Cek pola On Process / In Transit umum
        if (in_array($upper, ['ON PROCESS', 'ONPROCESS', 'IN TRANSIT', 'SEDANG DIPROSES', 'DALAM PERJALANAN'])) {
            return "Paket sedang dalam proses pemrosesan dan pengiriman di jaringan ekspedisi{$locText}.";
        }

        // 8. Cek pola kegagalan / penundaan / retur
        if (preg_match('/(?:FAILED TO DELIVER|RECIPIENT NOT AT HOME|ALAMAT TIDAK DITEMUKAN|RETURNED|GAGAL PENGANTARAN)/i', $desc)) {
            return "Pengantaran paket tertunda atau gagal (Alamat tidak ditemukan / Penerima tidak di tempat). Akan dilakukan penjadwalan ulang oleh kurir.";
        }

        // 9. Jika deskripsi asli (baik dalam bahasa Indonesia maupun kalimat khusus ekspedisi domestik) tidak masuk ke pola di atas,
        // kita bersihkan dan kembalikan seutuhnya agar informasi penting atau catatan kurir tidak hilang.
        return $desc;
    }

    /**
     * Daftar lengkap kota/kabupaten tujuan pengiriman seluruh Indonesia (400+ entri)
     * dengan ID dist_ resmi BinderByte - mencakup semua provinsi Indonesia.
     */
    public function getDaftarKota(): array
    {
        return Cache::remember('binderbyte_daftar_kota_v5', 30 * 24 * 60 * 60, function () {
            return [
                // ===== JAWA BARAT =====
                ['city_id' => 'dist_32.71.01', 'city_name' => 'Kota Bogor (Bogor Tengah)'],
                ['city_id' => 'dist_32.71.02', 'city_name' => 'Kota Bogor (Bogor Utara)'],
                ['city_id' => 'dist_32.71.03', 'city_name' => 'Kota Bogor (Bogor Selatan)'],
                ['city_id' => 'dist_32.71.04', 'city_name' => 'Kota Bogor (Bogor Timur)'],
                ['city_id' => 'dist_32.71.05', 'city_name' => 'Kota Bogor (Bogor Barat)'],
                ['city_id' => 'dist_32.71.06', 'city_name' => 'Kota Bogor (Tanah Sareal)'],
                ['city_id' => 'dist_32.01.01', 'city_name' => 'Kab. Bogor (Cibinong)'],
                ['city_id' => 'dist_32.01.02', 'city_name' => 'Kab. Bogor (Citeureup)'],
                ['city_id' => 'dist_32.01.03', 'city_name' => 'Kab. Bogor (Gunung Putri)'],
                ['city_id' => 'dist_32.01.04', 'city_name' => 'Kab. Bogor (Jonggol)'],
                ['city_id' => 'dist_32.01.05', 'city_name' => 'Kab. Bogor (Cariu)'],
                ['city_id' => 'dist_32.01.06', 'city_name' => 'Kab. Bogor (Cigombong)'],
                ['city_id' => 'dist_32.01.07', 'city_name' => 'Kab. Bogor (Ciampea)'],
                ['city_id' => 'dist_32.01.08', 'city_name' => 'Kab. Bogor (Parung)'],
                ['city_id' => 'dist_32.01.09', 'city_name' => 'Kab. Bogor (Jasinga)'],
                ['city_id' => 'dist_32.01.10', 'city_name' => 'Kab. Bogor (Leuwiliang)'],
                ['city_id' => 'dist_32.76.01', 'city_name' => 'Kota Depok (Pancoran Mas)'],
                ['city_id' => 'dist_32.76.02', 'city_name' => 'Kota Depok (Beji)'],
                ['city_id' => 'dist_32.76.03', 'city_name' => 'Kota Depok (Sukmajaya)'],
                ['city_id' => 'dist_32.76.04', 'city_name' => 'Kota Depok (Cimanggis)'],
                ['city_id' => 'dist_32.76.05', 'city_name' => 'Kota Depok (Sawangan)'],
                ['city_id' => 'dist_32.76.06', 'city_name' => 'Kota Depok (Limo)'],
                ['city_id' => 'dist_32.75.01', 'city_name' => 'Kota Bekasi (Bekasi Barat)'],
                ['city_id' => 'dist_32.75.02', 'city_name' => 'Kota Bekasi (Bekasi Timur)'],
                ['city_id' => 'dist_32.75.03', 'city_name' => 'Kota Bekasi (Bekasi Utara)'],
                ['city_id' => 'dist_32.75.04', 'city_name' => 'Kota Bekasi (Bekasi Selatan)'],
                ['city_id' => 'dist_32.75.05', 'city_name' => 'Kota Bekasi (Pondok Gede)'],
                ['city_id' => 'dist_32.75.06', 'city_name' => 'Kota Bekasi (Jatiasih)'],
                ['city_id' => 'dist_32.75.07', 'city_name' => 'Kota Bekasi (Bantargebang)'],
                ['city_id' => 'dist_32.16.01', 'city_name' => 'Kab. Bekasi (Cikarang Pusat)'],
                ['city_id' => 'dist_32.16.02', 'city_name' => 'Kab. Bekasi (Cikarang Barat)'],
                ['city_id' => 'dist_32.16.03', 'city_name' => 'Kab. Bekasi (Cikarang Utara)'],
                ['city_id' => 'dist_32.16.04', 'city_name' => 'Kab. Bekasi (Cikarang Selatan)'],
                ['city_id' => 'dist_32.16.05', 'city_name' => 'Kab. Bekasi (Tambun Selatan)'],
                ['city_id' => 'dist_32.16.06', 'city_name' => 'Kab. Bekasi (Tambun Utara)'],
                ['city_id' => 'dist_32.16.07', 'city_name' => 'Kab. Bekasi (Cibitung)'],
                ['city_id' => 'dist_32.73.01', 'city_name' => 'Kota Bandung (Bandung Wetan)'],
                ['city_id' => 'dist_32.73.02', 'city_name' => 'Kota Bandung (Coblong)'],
                ['city_id' => 'dist_32.73.03', 'city_name' => 'Kota Bandung (Cicendo)'],
                ['city_id' => 'dist_32.73.04', 'city_name' => 'Kota Bandung (Sukasari)'],
                ['city_id' => 'dist_32.73.05', 'city_name' => 'Kota Bandung (Cibeunying Kidul)'],
                ['city_id' => 'dist_32.73.06', 'city_name' => 'Kota Bandung (Kiaracondong)'],
                ['city_id' => 'dist_32.73.07', 'city_name' => 'Kota Bandung (Antapani)'],
                ['city_id' => 'dist_32.73.08', 'city_name' => 'Kota Bandung (Arcamanik)'],
                ['city_id' => 'dist_32.73.09', 'city_name' => 'Kota Bandung (Cibiru)'],
                ['city_id' => 'dist_32.73.10', 'city_name' => 'Kota Bandung (Ujungberung)'],
                ['city_id' => 'dist_32.73.11', 'city_name' => 'Kota Bandung (Gedebage)'],
                ['city_id' => 'dist_32.73.12', 'city_name' => 'Kota Bandung (Buahbatu)'],
                ['city_id' => 'dist_32.73.13', 'city_name' => 'Kota Bandung (Rancasari)'],
                ['city_id' => 'dist_32.73.14', 'city_name' => 'Kota Bandung (Bandung Kidul)'],
                ['city_id' => 'dist_32.73.15', 'city_name' => 'Kota Bandung (Regol)'],
                ['city_id' => 'dist_32.73.16', 'city_name' => 'Kota Bandung (Lengkong)'],
                ['city_id' => 'dist_32.73.17', 'city_name' => 'Kota Bandung (Sumur Bandung)'],
                ['city_id' => 'dist_32.73.18', 'city_name' => 'Kota Bandung (Andir)'],
                ['city_id' => 'dist_32.73.19', 'city_name' => 'Kota Bandung (Astanaanyar)'],
                ['city_id' => 'dist_32.73.20', 'city_name' => 'Kota Bandung (Bojongloa Kaler)'],
                ['city_id' => 'dist_32.73.21', 'city_name' => 'Kota Bandung (Babakan Ciparay)'],
                ['city_id' => 'dist_32.73.22', 'city_name' => 'Kota Bandung (Bandung Kulon)'],
                ['city_id' => 'dist_32.73.23', 'city_name' => 'Kota Bandung (Sukajadi)'],
                ['city_id' => 'dist_32.73.24', 'city_name' => 'Kota Bandung (Cidadap)'],
                ['city_id' => 'dist_32.74.01', 'city_name' => 'Kota Cimahi (Cimahi Utara)'],
                ['city_id' => 'dist_32.74.02', 'city_name' => 'Kota Cimahi (Cimahi Tengah)'],
                ['city_id' => 'dist_32.74.03', 'city_name' => 'Kota Cimahi (Cimahi Selatan)'],
                ['city_id' => 'dist_32.04.01', 'city_name' => 'Kab. Bandung (Baleendah)'],
                ['city_id' => 'dist_32.04.02', 'city_name' => 'Kab. Bandung (Majalaya)'],
                ['city_id' => 'dist_32.04.03', 'city_name' => 'Kab. Bandung (Soreang)'],
                ['city_id' => 'dist_32.04.04', 'city_name' => 'Kab. Bandung (Cicalengka)'],
                ['city_id' => 'dist_32.04.05', 'city_name' => 'Kab. Bandung (Ciwidey)'],
                ['city_id' => 'dist_32.04.06', 'city_name' => 'Kab. Bandung (Dayeuhkolot)'],
                ['city_id' => 'dist_32.17.01', 'city_name' => 'Kab. Bandung Barat (Ngamprah)'],
                ['city_id' => 'dist_32.17.02', 'city_name' => 'Kab. Bandung Barat (Padalarang)'],
                ['city_id' => 'dist_32.17.03', 'city_name' => 'Kab. Bandung Barat (Batujajar)'],
                ['city_id' => 'dist_32.17.04', 'city_name' => 'Kab. Bandung Barat (Lembang)'],
                ['city_id' => 'dist_32.72.01', 'city_name' => 'Kota Sukabumi (Cikole)'],
                ['city_id' => 'dist_32.02.01', 'city_name' => 'Kab. Sukabumi (Palabuhanratu)'],
                ['city_id' => 'dist_32.02.02', 'city_name' => 'Kab. Sukabumi (Cicurug)'],
                ['city_id' => 'dist_32.02.03', 'city_name' => 'Kab. Sukabumi (Cibadak)'],
                ['city_id' => 'dist_32.03.01', 'city_name' => 'Kab. Cianjur (Cianjur Kota)'],
                ['city_id' => 'dist_32.03.02', 'city_name' => 'Kab. Cianjur (Cipanas)'],
                ['city_id' => 'dist_32.77.01', 'city_name' => 'Kota Tasikmalaya (Cipedes)'],
                ['city_id' => 'dist_32.06.01', 'city_name' => 'Kab. Tasikmalaya (Singaparna)'],
                ['city_id' => 'dist_32.78.01', 'city_name' => 'Kota Banjar (Banjar)'],
                ['city_id' => 'dist_32.05.01', 'city_name' => 'Kab. Garut (Garut Kota)'],
                ['city_id' => 'dist_32.05.02', 'city_name' => 'Kab. Garut (Tarogong Kidul)'],
                ['city_id' => 'dist_32.07.01', 'city_name' => 'Kab. Ciamis (Ciamis Kota)'],
                ['city_id' => 'dist_32.08.01', 'city_name' => 'Kab. Kuningan (Kuningan Kota)'],
                ['city_id' => 'dist_32.09.01', 'city_name' => 'Kab. Cirebon (Sumber)'],
                ['city_id' => 'dist_32.75.08', 'city_name' => 'Kota Cirebon (Kejaksan)'],
                ['city_id' => 'dist_32.10.01', 'city_name' => 'Kab. Majalengka (Majalengka Kota)'],
                ['city_id' => 'dist_32.11.01', 'city_name' => 'Kab. Sumedang (Sumedang Utara)'],
                ['city_id' => 'dist_32.12.01', 'city_name' => 'Kab. Indramayu (Indramayu Kota)'],
                ['city_id' => 'dist_32.13.01', 'city_name' => 'Kab. Subang (Subang Kota)'],
                ['city_id' => 'dist_32.14.01', 'city_name' => 'Kab. Purwakarta (Purwakarta Kota)'],
                ['city_id' => 'dist_32.15.01', 'city_name' => 'Kab. Karawang (Karawang Barat)'],
                ['city_id' => 'dist_32.15.02', 'city_name' => 'Kab. Karawang (Karawang Timur)'],
                ['city_id' => 'dist_32.15.03', 'city_name' => 'Kab. Karawang (Cikampek)'],
                // ===== DKI JAKARTA =====
                ['city_id' => 'dist_31.71.01', 'city_name' => 'Jakarta Selatan (Kebayoran Baru)'],
                ['city_id' => 'dist_31.71.02', 'city_name' => 'Jakarta Selatan (Pesanggrahan)'],
                ['city_id' => 'dist_31.71.03', 'city_name' => 'Jakarta Selatan (Cilandak)'],
                ['city_id' => 'dist_31.71.04', 'city_name' => 'Jakarta Selatan (Pasar Minggu)'],
                ['city_id' => 'dist_31.71.05', 'city_name' => 'Jakarta Selatan (Jagakarsa)'],
                ['city_id' => 'dist_31.71.06', 'city_name' => 'Jakarta Selatan (Tebet)'],
                ['city_id' => 'dist_31.71.07', 'city_name' => 'Jakarta Selatan (Setiabudi)'],
                ['city_id' => 'dist_31.71.08', 'city_name' => 'Jakarta Selatan (Mampang Prapatan)'],
                ['city_id' => 'dist_31.71.09', 'city_name' => 'Jakarta Selatan (Pancoran)'],
                ['city_id' => 'dist_31.71.10', 'city_name' => 'Jakarta Selatan (Kebayoran Lama)'],
                ['city_id' => 'dist_31.73.01', 'city_name' => 'Jakarta Pusat (Gambir)'],
                ['city_id' => 'dist_31.73.02', 'city_name' => 'Jakarta Pusat (Sawah Besar)'],
                ['city_id' => 'dist_31.73.03', 'city_name' => 'Jakarta Pusat (Kemayoran)'],
                ['city_id' => 'dist_31.73.04', 'city_name' => 'Jakarta Pusat (Senen)'],
                ['city_id' => 'dist_31.73.05', 'city_name' => 'Jakarta Pusat (Cempaka Putih)'],
                ['city_id' => 'dist_31.73.06', 'city_name' => 'Jakarta Pusat (Menteng)'],
                ['city_id' => 'dist_31.73.07', 'city_name' => 'Jakarta Pusat (Tanah Abang)'],
                ['city_id' => 'dist_31.73.08', 'city_name' => 'Jakarta Pusat (Johar Baru)'],
                ['city_id' => 'dist_31.72.01', 'city_name' => 'Jakarta Timur (Jatinegara)'],
                ['city_id' => 'dist_31.72.02', 'city_name' => 'Jakarta Timur (Cakung)'],
                ['city_id' => 'dist_31.72.03', 'city_name' => 'Jakarta Timur (Ciracas)'],
                ['city_id' => 'dist_31.72.04', 'city_name' => 'Jakarta Timur (Cipayung)'],
                ['city_id' => 'dist_31.72.05', 'city_name' => 'Jakarta Timur (Makasar)'],
                ['city_id' => 'dist_31.72.06', 'city_name' => 'Jakarta Timur (Pasar Rebo)'],
                ['city_id' => 'dist_31.72.07', 'city_name' => 'Jakarta Timur (Kramat Jati)'],
                ['city_id' => 'dist_31.72.08', 'city_name' => 'Jakarta Timur (Duren Sawit)'],
                ['city_id' => 'dist_31.74.01', 'city_name' => 'Jakarta Barat (Grogol Petamburan)'],
                ['city_id' => 'dist_31.74.02', 'city_name' => 'Jakarta Barat (Tambora)'],
                ['city_id' => 'dist_31.74.03', 'city_name' => 'Jakarta Barat (Taman Sari)'],
                ['city_id' => 'dist_31.74.04', 'city_name' => 'Jakarta Barat (Palmerah)'],
                ['city_id' => 'dist_31.74.05', 'city_name' => 'Jakarta Barat (Kebon Jeruk)'],
                ['city_id' => 'dist_31.74.06', 'city_name' => 'Jakarta Barat (Kalideres)'],
                ['city_id' => 'dist_31.74.07', 'city_name' => 'Jakarta Barat (Cengkareng)'],
                ['city_id' => 'dist_31.74.08', 'city_name' => 'Jakarta Barat (Kembangan)'],
                ['city_id' => 'dist_31.75.01', 'city_name' => 'Jakarta Utara (Kelapa Gading)'],
                ['city_id' => 'dist_31.75.02', 'city_name' => 'Jakarta Utara (Penjaringan)'],
                ['city_id' => 'dist_31.75.03', 'city_name' => 'Jakarta Utara (Pademangan)'],
                ['city_id' => 'dist_31.75.04', 'city_name' => 'Jakarta Utara (Tanjung Priok)'],
                ['city_id' => 'dist_31.75.05', 'city_name' => 'Jakarta Utara (Koja)'],
                ['city_id' => 'dist_31.75.06', 'city_name' => 'Jakarta Utara (Cilincing)'],
                // ===== BANTEN =====
                ['city_id' => 'dist_36.71.01', 'city_name' => 'Kota Tangerang (Tangerang)'],
                ['city_id' => 'dist_36.71.02', 'city_name' => 'Kota Tangerang (Batuceper)'],
                ['city_id' => 'dist_36.71.03', 'city_name' => 'Kota Tangerang (Ciledug)'],
                ['city_id' => 'dist_36.71.04', 'city_name' => 'Kota Tangerang (Larangan)'],
                ['city_id' => 'dist_36.71.05', 'city_name' => 'Kota Tangerang (Karang Tengah)'],
                ['city_id' => 'dist_36.71.06', 'city_name' => 'Kota Tangerang (Cipondoh)'],
                ['city_id' => 'dist_36.71.07', 'city_name' => 'Kota Tangerang (Pinang)'],
                ['city_id' => 'dist_36.71.08', 'city_name' => 'Kota Tangerang (Periuk)'],
                ['city_id' => 'dist_36.71.09', 'city_name' => 'Kota Tangerang (Cibodas)'],
                ['city_id' => 'dist_36.71.10', 'city_name' => 'Kota Tangerang (Neglasari)'],
                ['city_id' => 'dist_36.71.11', 'city_name' => 'Kota Tangerang (Benda)'],
                ['city_id' => 'dist_36.74.01', 'city_name' => 'Kota Tangerang Selatan (Serpong)'],
                ['city_id' => 'dist_36.74.02', 'city_name' => 'Kota Tangerang Selatan (Ciputat)'],
                ['city_id' => 'dist_36.74.03', 'city_name' => 'Kota Tangerang Selatan (Pamulang)'],
                ['city_id' => 'dist_36.74.04', 'city_name' => 'Kota Tangerang Selatan (Pondok Aren)'],
                ['city_id' => 'dist_36.74.05', 'city_name' => 'Kota Tangerang Selatan (Ciputat Timur)'],
                ['city_id' => 'dist_36.74.06', 'city_name' => 'Kota Tangerang Selatan (Serpong Utara)'],
                ['city_id' => 'dist_36.74.07', 'city_name' => 'Kota Tangerang Selatan (Setu)'],
                ['city_id' => 'dist_36.03.01', 'city_name' => 'Kab. Tangerang (Tigaraksa)'],
                ['city_id' => 'dist_36.03.02', 'city_name' => 'Kab. Tangerang (Balaraja)'],
                ['city_id' => 'dist_36.03.03', 'city_name' => 'Kab. Tangerang (Cikupa)'],
                ['city_id' => 'dist_36.03.04', 'city_name' => 'Kab. Tangerang (Pasar Kemis)'],
                ['city_id' => 'dist_36.72.01', 'city_name' => 'Kota Serang (Serang)'],
                ['city_id' => 'dist_36.01.01', 'city_name' => 'Kab. Serang (Ciruas)'],
                ['city_id' => 'dist_36.02.01', 'city_name' => 'Kab. Lebak (Rangkasbitung)'],
                ['city_id' => 'dist_36.04.01', 'city_name' => 'Kab. Pandeglang (Pandeglang Kota)'],
                ['city_id' => 'dist_36.73.01', 'city_name' => 'Kota Cilegon (Cilegon)'],
                // ===== JAWA TENGAH =====
                ['city_id' => 'dist_33.74.01', 'city_name' => 'Kota Semarang (Semarang Tengah)'],
                ['city_id' => 'dist_33.74.02', 'city_name' => 'Kota Semarang (Candisari)'],
                ['city_id' => 'dist_33.74.03', 'city_name' => 'Kota Semarang (Banyumanik)'],
                ['city_id' => 'dist_33.74.04', 'city_name' => 'Kota Semarang (Gajahmungkur)'],
                ['city_id' => 'dist_33.74.05', 'city_name' => 'Kota Semarang (Tembalang)'],
                ['city_id' => 'dist_33.74.06', 'city_name' => 'Kota Semarang (Pedurungan)'],
                ['city_id' => 'dist_33.74.07', 'city_name' => 'Kota Semarang (Genuk)'],
                ['city_id' => 'dist_33.74.08', 'city_name' => 'Kota Semarang (Gayamsari)'],
                ['city_id' => 'dist_33.74.09', 'city_name' => 'Kota Semarang (Semarang Timur)'],
                ['city_id' => 'dist_33.74.10', 'city_name' => 'Kota Semarang (Semarang Selatan)'],
                ['city_id' => 'dist_33.74.11', 'city_name' => 'Kota Semarang (Semarang Barat)'],
                ['city_id' => 'dist_33.74.12', 'city_name' => 'Kota Semarang (Semarang Utara)'],
                ['city_id' => 'dist_33.74.13', 'city_name' => 'Kota Semarang (Tugu)'],
                ['city_id' => 'dist_33.74.14', 'city_name' => 'Kota Semarang (Mijen)'],
                ['city_id' => 'dist_33.74.15', 'city_name' => 'Kota Semarang (Gunung Pati)'],
                ['city_id' => 'dist_33.74.16', 'city_name' => 'Kota Semarang (Ngaliyan)'],
                ['city_id' => 'dist_33.72.01', 'city_name' => 'Kota Surakarta / Solo (Banjarsari)'],
                ['city_id' => 'dist_33.72.02', 'city_name' => 'Kota Surakarta / Solo (Jebres)'],
                ['city_id' => 'dist_33.72.03', 'city_name' => 'Kota Surakarta / Solo (Laweyan)'],
                ['city_id' => 'dist_33.72.04', 'city_name' => 'Kota Surakarta / Solo (Pasar Kliwon)'],
                ['city_id' => 'dist_33.72.05', 'city_name' => 'Kota Surakarta / Solo (Serengan)'],
                ['city_id' => 'dist_33.75.01', 'city_name' => 'Kota Pekalongan (Pekalongan Barat)'],
                ['city_id' => 'dist_33.75.02', 'city_name' => 'Kota Pekalongan (Pekalongan Timur)'],
                ['city_id' => 'dist_33.76.01', 'city_name' => 'Kota Tegal (Tegal Barat)'],
                ['city_id' => 'dist_33.76.02', 'city_name' => 'Kota Tegal (Tegal Selatan)'],
                ['city_id' => 'dist_33.73.01', 'city_name' => 'Kota Salatiga (Sidorejo)'],
                ['city_id' => 'dist_33.77.01', 'city_name' => 'Kota Magelang (Magelang Utara)'],
                ['city_id' => 'dist_33.01.01', 'city_name' => 'Kab. Cilacap (Cilacap Kota)'],
                ['city_id' => 'dist_33.02.01', 'city_name' => 'Kab. Banyumas (Purwokerto Timur)'],
                ['city_id' => 'dist_33.02.02', 'city_name' => 'Kab. Banyumas (Purwokerto Utara)'],
                ['city_id' => 'dist_33.03.01', 'city_name' => 'Kab. Purbalingga (Purbalingga Kota)'],
                ['city_id' => 'dist_33.04.01', 'city_name' => 'Kab. Banjarnegara (Banjarnegara Kota)'],
                ['city_id' => 'dist_33.05.01', 'city_name' => 'Kab. Kebumen (Kebumen Kota)'],
                ['city_id' => 'dist_33.06.01', 'city_name' => 'Kab. Purworejo (Purworejo Kota)'],
                ['city_id' => 'dist_33.07.01', 'city_name' => 'Kab. Wonosobo (Wonosobo Kota)'],
                ['city_id' => 'dist_33.08.01', 'city_name' => 'Kab. Magelang (Mungkid)'],
                ['city_id' => 'dist_33.09.01', 'city_name' => 'Kab. Boyolali (Boyolali Kota)'],
                ['city_id' => 'dist_33.10.01', 'city_name' => 'Kab. Klaten (Klaten Tengah)'],
                ['city_id' => 'dist_33.11.01', 'city_name' => 'Kab. Sukoharjo (Sukoharjo Kota)'],
                ['city_id' => 'dist_33.12.01', 'city_name' => 'Kab. Wonogiri (Wonogiri Kota)'],
                ['city_id' => 'dist_33.13.01', 'city_name' => 'Kab. Karanganyar (Karanganyar Kota)'],
                ['city_id' => 'dist_33.14.01', 'city_name' => 'Kab. Sragen (Sragen Kota)'],
                ['city_id' => 'dist_33.15.01', 'city_name' => 'Kab. Grobogan (Purwodadi)'],
                ['city_id' => 'dist_33.16.01', 'city_name' => 'Kab. Blora (Blora Kota)'],
                ['city_id' => 'dist_33.17.01', 'city_name' => 'Kab. Rembang (Rembang Kota)'],
                ['city_id' => 'dist_33.18.01', 'city_name' => 'Kab. Pati (Pati Kota)'],
                ['city_id' => 'dist_33.19.01', 'city_name' => 'Kab. Kudus (Kudus Kota)'],
                ['city_id' => 'dist_33.20.01', 'city_name' => 'Kab. Jepara (Jepara Kota)'],
                ['city_id' => 'dist_33.21.01', 'city_name' => 'Kab. Demak (Demak Kota)'],
                ['city_id' => 'dist_33.22.01', 'city_name' => 'Kab. Semarang (Ungaran Barat)'],
                ['city_id' => 'dist_33.23.01', 'city_name' => 'Kab. Temanggung (Temanggung Kota)'],
                ['city_id' => 'dist_33.24.01', 'city_name' => 'Kab. Kendal (Kendal Kota)'],
                ['city_id' => 'dist_33.25.01', 'city_name' => 'Kab. Batang (Batang Kota)'],
                ['city_id' => 'dist_33.26.01', 'city_name' => 'Kab. Pekalongan (Kajen)'],
                ['city_id' => 'dist_33.27.01', 'city_name' => 'Kab. Pemalang (Pemalang Kota)'],
                ['city_id' => 'dist_33.28.01', 'city_name' => 'Kab. Tegal (Slawi)'],
                ['city_id' => 'dist_33.29.01', 'city_name' => 'Kab. Brebes (Brebes Kota)'],
                // ===== DI YOGYAKARTA =====
                ['city_id' => 'dist_34.71.01', 'city_name' => 'Kota Yogyakarta (Danurejan)'],
                ['city_id' => 'dist_34.71.02', 'city_name' => 'Kota Yogyakarta (Gondokusuman)'],
                ['city_id' => 'dist_34.71.03', 'city_name' => 'Kota Yogyakarta (Jetis)'],
                ['city_id' => 'dist_34.71.04', 'city_name' => 'Kota Yogyakarta (Mergangsan)'],
                ['city_id' => 'dist_34.71.05', 'city_name' => 'Kota Yogyakarta (Umbulharjo)'],
                ['city_id' => 'dist_34.01.01', 'city_name' => 'Kab. Kulon Progo (Wates)'],
                ['city_id' => 'dist_34.02.01', 'city_name' => 'Kab. Bantul (Bantul Kota)'],
                ['city_id' => 'dist_34.02.02', 'city_name' => 'Kab. Bantul (Sewon)'],
                ['city_id' => 'dist_34.03.01', 'city_name' => 'Kab. Gunungkidul (Wonosari)'],
                ['city_id' => 'dist_34.04.01', 'city_name' => 'Kab. Sleman (Depok)'],
                ['city_id' => 'dist_34.04.02', 'city_name' => 'Kab. Sleman (Mlati)'],
                ['city_id' => 'dist_34.04.03', 'city_name' => 'Kab. Sleman (Kalasan)'],
                ['city_id' => 'dist_34.04.04', 'city_name' => 'Kab. Sleman (Gamping)'],
                // ===== JAWA TIMUR =====
                ['city_id' => 'dist_35.78.01', 'city_name' => 'Kota Surabaya (Tegalsari)'],
                ['city_id' => 'dist_35.78.02', 'city_name' => 'Kota Surabaya (Genteng)'],
                ['city_id' => 'dist_35.78.03', 'city_name' => 'Kota Surabaya (Bubutan)'],
                ['city_id' => 'dist_35.78.04', 'city_name' => 'Kota Surabaya (Simokerto)'],
                ['city_id' => 'dist_35.78.05', 'city_name' => 'Kota Surabaya (Pabean Cantikan)'],
                ['city_id' => 'dist_35.78.06', 'city_name' => 'Kota Surabaya (Semampir)'],
                ['city_id' => 'dist_35.78.07', 'city_name' => 'Kota Surabaya (Krembangan)'],
                ['city_id' => 'dist_35.78.08', 'city_name' => 'Kota Surabaya (Wonokromo)'],
                ['city_id' => 'dist_35.78.09', 'city_name' => 'Kota Surabaya (Rungkut)'],
                ['city_id' => 'dist_35.78.10', 'city_name' => 'Kota Surabaya (Tenggilis Mejoyo)'],
                ['city_id' => 'dist_35.78.11', 'city_name' => 'Kota Surabaya (Gunung Anyar)'],
                ['city_id' => 'dist_35.78.12', 'city_name' => 'Kota Surabaya (Sukolilo)'],
                ['city_id' => 'dist_35.78.13', 'city_name' => 'Kota Surabaya (Tambaksari)'],
                ['city_id' => 'dist_35.78.14', 'city_name' => 'Kota Surabaya (Gubeng)'],
                ['city_id' => 'dist_35.78.15', 'city_name' => 'Kota Surabaya (Wonocolo)'],
                ['city_id' => 'dist_35.78.16', 'city_name' => 'Kota Surabaya (Gayungan)'],
                ['city_id' => 'dist_35.78.17', 'city_name' => 'Kota Surabaya (Wiyung)'],
                ['city_id' => 'dist_35.78.18', 'city_name' => 'Kota Surabaya (Karangpilang)'],
                ['city_id' => 'dist_35.78.19', 'city_name' => 'Kota Surabaya (Lakarsantri)'],
                ['city_id' => 'dist_35.78.20', 'city_name' => 'Kota Surabaya (Benowo)'],
                ['city_id' => 'dist_35.78.21', 'city_name' => 'Kota Surabaya (Pakal)'],
                ['city_id' => 'dist_35.78.22', 'city_name' => 'Kota Surabaya (Asemrowo)'],
                ['city_id' => 'dist_35.78.23', 'city_name' => 'Kota Surabaya (Bulak)'],
                ['city_id' => 'dist_35.78.24', 'city_name' => 'Kota Surabaya (Kenjeran)'],
                ['city_id' => 'dist_35.78.25', 'city_name' => 'Kota Surabaya (Mulyorejo)'],
                ['city_id' => 'dist_35.73.01', 'city_name' => 'Kota Malang (Klojen)'],
                ['city_id' => 'dist_35.73.02', 'city_name' => 'Kota Malang (Blimbing)'],
                ['city_id' => 'dist_35.73.03', 'city_name' => 'Kota Malang (Kedungkandang)'],
                ['city_id' => 'dist_35.73.04', 'city_name' => 'Kota Malang (Sukun)'],
                ['city_id' => 'dist_35.73.05', 'city_name' => 'Kota Malang (Lowokwaru)'],
                ['city_id' => 'dist_35.79.01', 'city_name' => 'Kota Batu (Batu)'],
                ['city_id' => 'dist_35.77.01', 'city_name' => 'Kota Mojokerto (Prajurit Kulon)'],
                ['city_id' => 'dist_35.76.01', 'city_name' => 'Kota Probolinggo (Kanigaran)'],
                ['city_id' => 'dist_35.75.01', 'city_name' => 'Kota Pasuruan (Bugul Kidul)'],
                ['city_id' => 'dist_35.74.01', 'city_name' => 'Kota Blitar (Kepanjenkidul)'],
                ['city_id' => 'dist_35.71.01', 'city_name' => 'Kota Kediri (Kota Kediri)'],
                ['city_id' => 'dist_35.72.01', 'city_name' => 'Kota Madiun (Manguharjo)'],
                ['city_id' => 'dist_35.01.01', 'city_name' => 'Kab. Pacitan (Pacitan Kota)'],
                ['city_id' => 'dist_35.02.01', 'city_name' => 'Kab. Ponorogo (Ponorogo Kota)'],
                ['city_id' => 'dist_35.03.01', 'city_name' => 'Kab. Trenggalek (Trenggalek Kota)'],
                ['city_id' => 'dist_35.04.01', 'city_name' => 'Kab. Tulungagung (Tulungagung Kota)'],
                ['city_id' => 'dist_35.06.01', 'city_name' => 'Kab. Kediri (Pare)'],
                ['city_id' => 'dist_35.07.01', 'city_name' => 'Kab. Malang (Kepanjen)'],
                ['city_id' => 'dist_35.07.02', 'city_name' => 'Kab. Malang (Singosari)'],
                ['city_id' => 'dist_35.08.01', 'city_name' => 'Kab. Lumajang (Lumajang Kota)'],
                ['city_id' => 'dist_35.09.01', 'city_name' => 'Kab. Jember (Kaliwates)'],
                ['city_id' => 'dist_35.09.02', 'city_name' => 'Kab. Jember (Sumbersari)'],
                ['city_id' => 'dist_35.10.01', 'city_name' => 'Kab. Banyuwangi (Banyuwangi Kota)'],
                ['city_id' => 'dist_35.11.01', 'city_name' => 'Kab. Bondowoso (Bondowoso Kota)'],
                ['city_id' => 'dist_35.12.01', 'city_name' => 'Kab. Situbondo (Situbondo Kota)'],
                ['city_id' => 'dist_35.13.01', 'city_name' => 'Kab. Probolinggo (Kraksaan)'],
                ['city_id' => 'dist_35.14.01', 'city_name' => 'Kab. Pasuruan (Bangil)'],
                ['city_id' => 'dist_35.15.01', 'city_name' => 'Kab. Sidoarjo (Sidoarjo Kota)'],
                ['city_id' => 'dist_35.15.02', 'city_name' => 'Kab. Sidoarjo (Waru)'],
                ['city_id' => 'dist_35.15.03', 'city_name' => 'Kab. Sidoarjo (Taman)'],
                ['city_id' => 'dist_35.15.04', 'city_name' => 'Kab. Sidoarjo (Gedangan)'],
                ['city_id' => 'dist_35.16.01', 'city_name' => 'Kab. Mojokerto (Mojosari)'],
                ['city_id' => 'dist_35.17.01', 'city_name' => 'Kab. Jombang (Jombang Kota)'],
                ['city_id' => 'dist_35.18.01', 'city_name' => 'Kab. Nganjuk (Nganjuk Kota)'],
                ['city_id' => 'dist_35.20.01', 'city_name' => 'Kab. Magetan (Magetan Kota)'],
                ['city_id' => 'dist_35.21.01', 'city_name' => 'Kab. Ngawi (Ngawi Kota)'],
                ['city_id' => 'dist_35.22.01', 'city_name' => 'Kab. Bojonegoro (Bojonegoro Kota)'],
                ['city_id' => 'dist_35.23.01', 'city_name' => 'Kab. Tuban (Tuban Kota)'],
                ['city_id' => 'dist_35.24.01', 'city_name' => 'Kab. Lamongan (Lamongan Kota)'],
                ['city_id' => 'dist_35.25.01', 'city_name' => 'Kab. Gresik (Gresik Kota)'],
                ['city_id' => 'dist_35.25.02', 'city_name' => 'Kab. Gresik (Kebomas)'],
                ['city_id' => 'dist_35.26.01', 'city_name' => 'Kab. Bangkalan (Bangkalan Kota)'],
                ['city_id' => 'dist_35.27.01', 'city_name' => 'Kab. Sampang (Sampang Kota)'],
                ['city_id' => 'dist_35.28.01', 'city_name' => 'Kab. Pamekasan (Pamekasan Kota)'],
                ['city_id' => 'dist_35.29.01', 'city_name' => 'Kab. Sumenep (Sumenep Kota)'],
                // ===== BALI =====
                ['city_id' => 'dist_51.71.01', 'city_name' => 'Kota Denpasar (Denpasar Selatan)'],
                ['city_id' => 'dist_51.71.02', 'city_name' => 'Kota Denpasar (Denpasar Timur)'],
                ['city_id' => 'dist_51.71.03', 'city_name' => 'Kota Denpasar (Denpasar Barat)'],
                ['city_id' => 'dist_51.71.04', 'city_name' => 'Kota Denpasar (Denpasar Utara)'],
                ['city_id' => 'dist_51.01.01', 'city_name' => 'Kab. Jembrana (Negara)'],
                ['city_id' => 'dist_51.02.01', 'city_name' => 'Kab. Tabanan (Tabanan Kota)'],
                ['city_id' => 'dist_51.03.01', 'city_name' => 'Kab. Badung (Mengwi / Kuta)'],
                ['city_id' => 'dist_51.03.02', 'city_name' => 'Kab. Badung (Kuta Selatan / Jimbaran)'],
                ['city_id' => 'dist_51.04.01', 'city_name' => 'Kab. Gianyar (Gianyar / Ubud)'],
                ['city_id' => 'dist_51.05.01', 'city_name' => 'Kab. Klungkung (Semarapura)'],
                ['city_id' => 'dist_51.06.01', 'city_name' => 'Kab. Bangli (Bangli Kota)'],
                ['city_id' => 'dist_51.07.01', 'city_name' => 'Kab. Karangasem (Amlapura)'],
                ['city_id' => 'dist_51.08.01', 'city_name' => 'Kab. Buleleng (Singaraja)'],
                // ===== SUMATERA UTARA =====
                ['city_id' => 'dist_12.71.01', 'city_name' => 'Kota Medan (Medan Baru)'],
                ['city_id' => 'dist_12.71.02', 'city_name' => 'Kota Medan (Medan Petisah)'],
                ['city_id' => 'dist_12.71.03', 'city_name' => 'Kota Medan (Medan Polonia)'],
                ['city_id' => 'dist_12.71.04', 'city_name' => 'Kota Medan (Medan Maimun)'],
                ['city_id' => 'dist_12.71.05', 'city_name' => 'Kota Medan (Medan Kota)'],
                ['city_id' => 'dist_12.71.06', 'city_name' => 'Kota Medan (Medan Denai)'],
                ['city_id' => 'dist_12.71.07', 'city_name' => 'Kota Medan (Medan Amplas)'],
                ['city_id' => 'dist_12.71.08', 'city_name' => 'Kota Medan (Medan Tembung)'],
                ['city_id' => 'dist_12.71.09', 'city_name' => 'Kota Medan (Medan Deli)'],
                ['city_id' => 'dist_12.71.10', 'city_name' => 'Kota Medan (Medan Labuhan)'],
                ['city_id' => 'dist_12.71.11', 'city_name' => 'Kota Medan (Medan Belawan)'],
                ['city_id' => 'dist_12.72.01', 'city_name' => 'Kota Pematangsiantar (Siantar Barat)'],
                ['city_id' => 'dist_12.73.01', 'city_name' => 'Kota Tebing Tinggi (Tebing Tinggi Kota)'],
                ['city_id' => 'dist_12.75.01', 'city_name' => 'Kota Binjai (Binjai Kota)'],
                ['city_id' => 'dist_12.76.01', 'city_name' => 'Kota Padangsidimpuan (Padangsidimpuan)'],
                ['city_id' => 'dist_12.07.01', 'city_name' => 'Kab. Deli Serdang (Lubuk Pakam)'],
                ['city_id' => 'dist_12.13.01', 'city_name' => 'Kab. Karo (Kabanjahe)'],
                ['city_id' => 'dist_12.14.01', 'city_name' => 'Kab. Langkat (Stabat)'],
                ['city_id' => 'dist_12.22.01', 'city_name' => 'Kab. Mandailing Natal (Panyabungan)'],
                ['city_id' => 'dist_12.18.01', 'city_name' => 'Kab. Asahan (Kisaran)'],
                ['city_id' => 'dist_12.17.01', 'city_name' => 'Kab. Labuhanbatu (Rantauprapat)'],
                ['city_id' => 'dist_12.74.01', 'city_name' => 'Kota Tanjungbalai (Tanjungbalai Kota)'],
                // ===== SUMATERA BARAT =====
                ['city_id' => 'dist_13.71.01', 'city_name' => 'Kota Padang (Padang Barat)'],
                ['city_id' => 'dist_13.71.02', 'city_name' => 'Kota Padang (Padang Selatan)'],
                ['city_id' => 'dist_13.71.03', 'city_name' => 'Kota Padang (Padang Timur)'],
                ['city_id' => 'dist_13.71.04', 'city_name' => 'Kota Padang (Padang Utara)'],
                ['city_id' => 'dist_13.71.05', 'city_name' => 'Kota Padang (Kuranji)'],
                ['city_id' => 'dist_13.72.01', 'city_name' => 'Kota Solok (Solok Kota)'],
                ['city_id' => 'dist_13.75.01', 'city_name' => 'Kota Bukittinggi (Mandiangin)'],
                ['city_id' => 'dist_13.76.01', 'city_name' => 'Kota Payakumbuh (Payakumbuh)'],
                ['city_id' => 'dist_13.77.01', 'city_name' => 'Kota Pariaman (Pariaman Kota)'],
                ['city_id' => 'dist_13.01.01', 'city_name' => 'Kab. Agam (Lubuk Basung)'],
                ['city_id' => 'dist_13.04.01', 'city_name' => 'Kab. Padang Pariaman (Parit Malintang)'],
                // ===== RIAU =====
                ['city_id' => 'dist_14.71.01', 'city_name' => 'Kota Pekanbaru (Pekanbaru Kota)'],
                ['city_id' => 'dist_14.71.02', 'city_name' => 'Kota Pekanbaru (Senapelan)'],
                ['city_id' => 'dist_14.71.03', 'city_name' => 'Kota Pekanbaru (Bukit Raya)'],
                ['city_id' => 'dist_14.71.04', 'city_name' => 'Kota Pekanbaru (Tampan)'],
                ['city_id' => 'dist_14.71.05', 'city_name' => 'Kota Pekanbaru (Payung Sekaki)'],
                ['city_id' => 'dist_14.71.06', 'city_name' => 'Kota Pekanbaru (Rumbai)'],
                ['city_id' => 'dist_14.72.01', 'city_name' => 'Kota Dumai (Dumai Kota)'],
                ['city_id' => 'dist_14.01.01', 'city_name' => 'Kab. Kampar (Bangkinang)'],
                ['city_id' => 'dist_14.03.01', 'city_name' => 'Kab. Bengkalis (Bengkalis Kota)'],
                ['city_id' => 'dist_14.03.02', 'city_name' => 'Kab. Bengkalis (Mandau)'],
                // ===== KEPULAUAN RIAU =====
                ['city_id' => 'dist_21.71.01', 'city_name' => 'Kota Batam (Batam Kota)'],
                ['city_id' => 'dist_21.71.02', 'city_name' => 'Kota Batam (Batu Aji)'],
                ['city_id' => 'dist_21.71.03', 'city_name' => 'Kota Batam (Sekupang)'],
                ['city_id' => 'dist_21.71.04', 'city_name' => 'Kota Batam (Nongsa)'],
                ['city_id' => 'dist_21.71.05', 'city_name' => 'Kota Batam (Bengkong)'],
                ['city_id' => 'dist_21.71.06', 'city_name' => 'Kota Batam (Batu Ampar)'],
                ['city_id' => 'dist_21.72.01', 'city_name' => 'Kota Tanjungpinang (Tanjungpinang Kota)'],
                ['city_id' => 'dist_21.05.01', 'city_name' => 'Kab. Bintan (Tanjung Uban)'],
                ['city_id' => 'dist_21.02.01', 'city_name' => 'Kab. Karimun (Tanjung Balai Karimun)'],
                // ===== SUMATERA SELATAN =====
                ['city_id' => 'dist_16.71.01', 'city_name' => 'Kota Palembang (Ilir Barat I)'],
                ['city_id' => 'dist_16.71.02', 'city_name' => 'Kota Palembang (Ilir Barat II)'],
                ['city_id' => 'dist_16.71.03', 'city_name' => 'Kota Palembang (Ilir Timur I)'],
                ['city_id' => 'dist_16.71.04', 'city_name' => 'Kota Palembang (Seberang Ulu I)'],
                ['city_id' => 'dist_16.71.05', 'city_name' => 'Kota Palembang (Kemuning)'],
                ['city_id' => 'dist_16.71.06', 'city_name' => 'Kota Palembang (Bukit Kecil)'],
                ['city_id' => 'dist_16.71.07', 'city_name' => 'Kota Palembang (Sukarame)'],
                ['city_id' => 'dist_16.72.01', 'city_name' => 'Kota Prabumulih (Prabumulih Kota)'],
                ['city_id' => 'dist_16.74.01', 'city_name' => 'Kota Lubuklinggau (Lubuklinggau Kota)'],
                ['city_id' => 'dist_16.08.01', 'city_name' => 'Kab. Ogan Komering Ulu (Baturaja)'],
                ['city_id' => 'dist_16.07.01', 'city_name' => 'Kab. Muara Enim (Muara Enim Kota)'],
                ['city_id' => 'dist_16.04.01', 'city_name' => 'Kab. Musi Banyuasin (Sekayu)'],
                // ===== LAMPUNG =====
                ['city_id' => 'dist_18.71.01', 'city_name' => 'Kota Bandar Lampung (Tanjung Karang Pusat)'],
                ['city_id' => 'dist_18.71.02', 'city_name' => 'Kota Bandar Lampung (Tanjung Karang Barat)'],
                ['city_id' => 'dist_18.71.03', 'city_name' => 'Kota Bandar Lampung (Tanjung Karang Timur)'],
                ['city_id' => 'dist_18.71.04', 'city_name' => 'Kota Bandar Lampung (Panjang)'],
                ['city_id' => 'dist_18.71.05', 'city_name' => 'Kota Bandar Lampung (Kedaton)'],
                ['city_id' => 'dist_18.72.01', 'city_name' => 'Kota Metro (Metro Pusat)'],
                ['city_id' => 'dist_18.01.01', 'city_name' => 'Kab. Lampung Selatan (Kalianda)'],
                ['city_id' => 'dist_18.02.01', 'city_name' => 'Kab. Lampung Tengah (Gunung Sugih)'],
                ['city_id' => 'dist_18.08.01', 'city_name' => 'Kab. Lampung Utara (Kotabumi)'],
                // ===== BENGKULU =====
                ['city_id' => 'dist_17.71.01', 'city_name' => 'Kota Bengkulu (Ratu Samban)'],
                ['city_id' => 'dist_17.71.02', 'city_name' => 'Kota Bengkulu (Selebar)'],
                ['city_id' => 'dist_17.71.03', 'city_name' => 'Kota Bengkulu (Gading Cempaka)'],
                ['city_id' => 'dist_17.01.01', 'city_name' => 'Kab. Bengkulu Utara (Arga Makmur)'],
                // ===== JAMBI =====
                ['city_id' => 'dist_15.71.01', 'city_name' => 'Kota Jambi (Pasar Jambi)'],
                ['city_id' => 'dist_15.71.02', 'city_name' => 'Kota Jambi (Kota Baru)'],
                ['city_id' => 'dist_15.71.03', 'city_name' => 'Kota Jambi (Jambi Selatan)'],
                ['city_id' => 'dist_15.71.04', 'city_name' => 'Kota Jambi (Jambi Timur)'],
                ['city_id' => 'dist_15.72.01', 'city_name' => 'Kota Sungai Penuh (Sungai Penuh)'],
                ['city_id' => 'dist_15.03.01', 'city_name' => 'Kab. Batanghari (Muara Bulian)'],
                // ===== BANGKA BELITUNG =====
                ['city_id' => 'dist_19.71.01', 'city_name' => 'Kota Pangkalpinang (Pangkalpinang)'],
                ['city_id' => 'dist_19.01.01', 'city_name' => 'Kab. Bangka (Sungailiat)'],
                ['city_id' => 'dist_19.04.01', 'city_name' => 'Kab. Bangka Selatan (Toboali)'],
                ['city_id' => 'dist_19.03.01', 'city_name' => 'Kab. Bangka Tengah (Koba)'],
                ['city_id' => 'dist_19.05.01', 'city_name' => 'Kab. Belitung (Tanjungpandan)'],
                ['city_id' => 'dist_19.06.01', 'city_name' => 'Kab. Belitung Timur (Manggar)'],
                // ===== ACEH =====
                ['city_id' => 'dist_11.71.01', 'city_name' => 'Kota Banda Aceh (Baiturrahman)'],
                ['city_id' => 'dist_11.71.02', 'city_name' => 'Kota Banda Aceh (Kuta Alam)'],
                ['city_id' => 'dist_11.71.03', 'city_name' => 'Kota Banda Aceh (Syiah Kuala)'],
                ['city_id' => 'dist_11.72.01', 'city_name' => 'Kota Sabang (Sukakarya)'],
                ['city_id' => 'dist_11.73.01', 'city_name' => 'Kota Langsa (Langsa Kota)'],
                ['city_id' => 'dist_11.74.01', 'city_name' => 'Kota Lhokseumawe (Banda Sakti)'],
                ['city_id' => 'dist_11.75.01', 'city_name' => 'Kota Subulussalam (Simpang Kiri)'],
                ['city_id' => 'dist_11.06.01', 'city_name' => 'Kab. Aceh Besar (Ingin Jaya)'],
                ['city_id' => 'dist_11.07.01', 'city_name' => 'Kab. Pidie (Sigli)'],
                ['city_id' => 'dist_11.02.01', 'city_name' => 'Kab. Aceh Utara (Lhoksukon)'],
                // ===== SULAWESI SELATAN =====
                ['city_id' => 'dist_73.71.01', 'city_name' => 'Kota Makassar (Ujung Pandang)'],
                ['city_id' => 'dist_73.71.02', 'city_name' => 'Kota Makassar (Makassar)'],
                ['city_id' => 'dist_73.71.03', 'city_name' => 'Kota Makassar (Mamajang)'],
                ['city_id' => 'dist_73.71.04', 'city_name' => 'Kota Makassar (Tamalate)'],
                ['city_id' => 'dist_73.71.05', 'city_name' => 'Kota Makassar (Rappocini)'],
                ['city_id' => 'dist_73.71.06', 'city_name' => 'Kota Makassar (Panakkukang)'],
                ['city_id' => 'dist_73.71.07', 'city_name' => 'Kota Makassar (Manggala)'],
                ['city_id' => 'dist_73.71.08', 'city_name' => 'Kota Makassar (Biringkanaya)'],
                ['city_id' => 'dist_73.71.09', 'city_name' => 'Kota Makassar (Tamalanrea)'],
                ['city_id' => 'dist_73.71.10', 'city_name' => 'Kota Makassar (Mariso)'],
                ['city_id' => 'dist_73.71.11', 'city_name' => 'Kota Makassar (Wajo)'],
                ['city_id' => 'dist_73.71.12', 'city_name' => 'Kota Makassar (Bontoala)'],
                ['city_id' => 'dist_73.71.13', 'city_name' => 'Kota Makassar (Tallo)'],
                ['city_id' => 'dist_73.72.01', 'city_name' => 'Kota Parepare (Bacukiki)'],
                ['city_id' => 'dist_73.73.01', 'city_name' => 'Kota Palopo (Wara)'],
                ['city_id' => 'dist_73.01.01', 'city_name' => 'Kab. Gowa (Somba Opu)'],
                ['city_id' => 'dist_73.04.01', 'city_name' => 'Kab. Maros (Maros Baru)'],
                ['city_id' => 'dist_73.11.01', 'city_name' => 'Kab. Bone (Watampone)'],
                ['city_id' => 'dist_73.17.01', 'city_name' => 'Kab. Bulukumba (Bulukumba Kota)'],
                // ===== SULAWESI UTARA =====
                ['city_id' => 'dist_71.71.01', 'city_name' => 'Kota Manado (Wenang)'],
                ['city_id' => 'dist_71.71.02', 'city_name' => 'Kota Manado (Malalayang)'],
                ['city_id' => 'dist_71.71.03', 'city_name' => 'Kota Manado (Sario)'],
                ['city_id' => 'dist_71.71.04', 'city_name' => 'Kota Manado (Wanea)'],
                ['city_id' => 'dist_71.71.05', 'city_name' => 'Kota Manado (Mapanget)'],
                ['city_id' => 'dist_71.72.01', 'city_name' => 'Kota Bitung (Bitung Barat)'],
                ['city_id' => 'dist_71.73.01', 'city_name' => 'Kota Tomohon (Tomohon Utara)'],
                ['city_id' => 'dist_71.74.01', 'city_name' => 'Kota Kotamobagu (Kotamobagu Barat)'],
                ['city_id' => 'dist_71.07.01', 'city_name' => 'Kab. Minahasa (Tondano Selatan)'],
                // ===== KALIMANTAN TIMUR =====
                ['city_id' => 'dist_64.71.01', 'city_name' => 'Kota Balikpapan (Balikpapan Kota)'],
                ['city_id' => 'dist_64.71.02', 'city_name' => 'Kota Balikpapan (Balikpapan Selatan)'],
                ['city_id' => 'dist_64.71.03', 'city_name' => 'Kota Balikpapan (Balikpapan Timur)'],
                ['city_id' => 'dist_64.71.04', 'city_name' => 'Kota Balikpapan (Balikpapan Utara)'],
                ['city_id' => 'dist_64.71.05', 'city_name' => 'Kota Balikpapan (Balikpapan Barat)'],
                ['city_id' => 'dist_64.72.01', 'city_name' => 'Kota Samarinda (Samarinda Kota)'],
                ['city_id' => 'dist_64.72.02', 'city_name' => 'Kota Samarinda (Samarinda Ulu)'],
                ['city_id' => 'dist_64.72.03', 'city_name' => 'Kota Samarinda (Samarinda Ilir)'],
                ['city_id' => 'dist_64.72.04', 'city_name' => 'Kota Samarinda (Samarinda Utara)'],
                ['city_id' => 'dist_64.73.01', 'city_name' => 'Kota Bontang (Bontang Utara)'],
                ['city_id' => 'dist_64.02.01', 'city_name' => 'Kab. Kutai Kartanegara (Tenggarong)'],
                // ===== KALIMANTAN SELATAN =====
                ['city_id' => 'dist_63.71.01', 'city_name' => 'Kota Banjarmasin (Banjarmasin Tengah)'],
                ['city_id' => 'dist_63.71.02', 'city_name' => 'Kota Banjarmasin (Banjarmasin Barat)'],
                ['city_id' => 'dist_63.71.03', 'city_name' => 'Kota Banjarmasin (Banjarmasin Timur)'],
                ['city_id' => 'dist_63.71.04', 'city_name' => 'Kota Banjarmasin (Banjarmasin Selatan)'],
                ['city_id' => 'dist_63.71.05', 'city_name' => 'Kota Banjarmasin (Banjarmasin Utara)'],
                ['city_id' => 'dist_63.72.01', 'city_name' => 'Kota Banjarbaru (Banjarbaru Kota)'],
                ['city_id' => 'dist_63.03.01', 'city_name' => 'Kab. Banjar (Martapura)'],
                ['city_id' => 'dist_63.01.01', 'city_name' => 'Kab. Tanah Laut (Pelaihari)'],
                // ===== KALIMANTAN BARAT =====
                ['city_id' => 'dist_61.71.01', 'city_name' => 'Kota Pontianak (Pontianak Selatan)'],
                ['city_id' => 'dist_61.71.02', 'city_name' => 'Kota Pontianak (Pontianak Barat)'],
                ['city_id' => 'dist_61.71.03', 'city_name' => 'Kota Pontianak (Pontianak Timur)'],
                ['city_id' => 'dist_61.71.04', 'city_name' => 'Kota Pontianak (Pontianak Utara)'],
                ['city_id' => 'dist_61.71.05', 'city_name' => 'Kota Pontianak (Pontianak Kota)'],
                ['city_id' => 'dist_61.72.01', 'city_name' => 'Kota Singkawang (Singkawang Tengah)'],
                ['city_id' => 'dist_61.03.01', 'city_name' => 'Kab. Sanggau (Sanggau Kota)'],
                ['city_id' => 'dist_61.01.01', 'city_name' => 'Kab. Sambas (Sambas Kota)'],
                ['city_id' => 'dist_61.07.01', 'city_name' => 'Kab. Ketapang (Ketapang Kota)'],
                // ===== KALIMANTAN TENGAH =====
                ['city_id' => 'dist_62.71.01', 'city_name' => 'Kota Palangka Raya (Pahandut)'],
                ['city_id' => 'dist_62.71.02', 'city_name' => 'Kota Palangka Raya (Jekan Raya)'],
                ['city_id' => 'dist_62.05.01', 'city_name' => 'Kab. Kotawaringin Timur (Sampit)'],
                ['city_id' => 'dist_62.04.01', 'city_name' => 'Kab. Kotawaringin Barat (Pangkalan Bun)'],
                // ===== KALIMANTAN UTARA =====
                ['city_id' => 'dist_65.71.01', 'city_name' => 'Kota Tarakan (Tarakan Tengah)'],
                ['city_id' => 'dist_65.01.01', 'city_name' => 'Kab. Nunukan (Nunukan Kota)'],
                ['city_id' => 'dist_65.03.01', 'city_name' => 'Kab. Bulungan (Tanjung Selor)'],
                // ===== NUSA TENGGARA BARAT =====
                ['city_id' => 'dist_52.71.01', 'city_name' => 'Kota Mataram (Ampenan)'],
                ['city_id' => 'dist_52.71.02', 'city_name' => 'Kota Mataram (Cakranegara)'],
                ['city_id' => 'dist_52.71.03', 'city_name' => 'Kota Mataram (Mataram)'],
                ['city_id' => 'dist_52.71.04', 'city_name' => 'Kota Mataram (Sandubaya)'],
                ['city_id' => 'dist_52.72.01', 'city_name' => 'Kota Bima (Rasa Nae Barat)'],
                ['city_id' => 'dist_52.01.01', 'city_name' => 'Kab. Lombok Barat (Gerung)'],
                ['city_id' => 'dist_52.02.01', 'city_name' => 'Kab. Lombok Tengah (Praya)'],
                ['city_id' => 'dist_52.03.01', 'city_name' => 'Kab. Lombok Timur (Selong)'],
                ['city_id' => 'dist_52.08.01', 'city_name' => 'Kab. Lombok Utara (Tanjung)'],
                // ===== NUSA TENGGARA TIMUR =====
                ['city_id' => 'dist_53.71.01', 'city_name' => 'Kota Kupang (Kelapa Lima)'],
                ['city_id' => 'dist_53.71.02', 'city_name' => 'Kota Kupang (Oebobo)'],
                ['city_id' => 'dist_53.71.03', 'city_name' => 'Kota Kupang (Maulafa)'],
                ['city_id' => 'dist_53.11.01', 'city_name' => 'Kab. Sikka (Maumere)'],
                ['city_id' => 'dist_53.08.01', 'city_name' => 'Kab. Ende (Ende Kota)'],
                // ===== SULAWESI TENGAH =====
                ['city_id' => 'dist_72.71.01', 'city_name' => 'Kota Palu (Palu Selatan)'],
                ['city_id' => 'dist_72.71.02', 'city_name' => 'Kota Palu (Palu Timur)'],
                ['city_id' => 'dist_72.71.03', 'city_name' => 'Kota Palu (Palu Barat)'],
                ['city_id' => 'dist_72.71.04', 'city_name' => 'Kota Palu (Palu Utara)'],
                ['city_id' => 'dist_72.01.01', 'city_name' => 'Kab. Banggai (Luwuk)'],
                // ===== SULAWESI TENGGARA =====
                ['city_id' => 'dist_74.71.01', 'city_name' => 'Kota Kendari (Kendari)'],
                ['city_id' => 'dist_74.71.02', 'city_name' => 'Kota Kendari (Kendari Barat)'],
                ['city_id' => 'dist_74.72.01', 'city_name' => 'Kota Baubau (Wolio)'],
                // ===== GORONTALO =====
                ['city_id' => 'dist_75.71.01', 'city_name' => 'Kota Gorontalo (Kota Selatan)'],
                ['city_id' => 'dist_75.71.02', 'city_name' => 'Kota Gorontalo (Kota Utara)'],
                ['city_id' => 'dist_75.71.03', 'city_name' => 'Kota Gorontalo (Kota Tengah)'],
                ['city_id' => 'dist_75.01.01', 'city_name' => 'Kab. Gorontalo (Limboto)'],
                // ===== SULAWESI BARAT =====
                ['city_id' => 'dist_76.01.01', 'city_name' => 'Kab. Mamuju (Mamuju Kota)'],
                // ===== MALUKU =====
                ['city_id' => 'dist_81.71.01', 'city_name' => 'Kota Ambon (Sirimau)'],
                ['city_id' => 'dist_81.71.02', 'city_name' => 'Kota Ambon (Nusaniwe)'],
                ['city_id' => 'dist_81.71.03', 'city_name' => 'Kota Ambon (Baguala)'],
                ['city_id' => 'dist_81.72.01', 'city_name' => 'Kota Tual (Tual)'],
                ['city_id' => 'dist_81.01.01', 'city_name' => 'Kab. Maluku Tengah (Masohi)'],
                // ===== MALUKU UTARA =====
                ['city_id' => 'dist_82.71.01', 'city_name' => 'Kota Ternate (Ternate Selatan)'],
                ['city_id' => 'dist_82.71.02', 'city_name' => 'Kota Ternate (Ternate Utara)'],
                ['city_id' => 'dist_82.72.01', 'city_name' => 'Kota Tidore Kepulauan (Tidore)'],
                ['city_id' => 'dist_82.06.01', 'city_name' => 'Kab. Halmahera Utara (Tobelo)'],
                // ===== PAPUA =====
                ['city_id' => 'dist_94.71.01', 'city_name' => 'Kota Jayapura (Jayapura Utara)'],
                ['city_id' => 'dist_94.71.02', 'city_name' => 'Kota Jayapura (Jayapura Selatan)'],
                ['city_id' => 'dist_94.71.03', 'city_name' => 'Kota Jayapura (Abepura)'],
                ['city_id' => 'dist_94.04.01', 'city_name' => 'Kab. Merauke (Merauke Kota)'],
                ['city_id' => 'dist_94.08.01', 'city_name' => 'Kab. Mimika (Timika)'],
                ['city_id' => 'dist_94.06.01', 'city_name' => 'Kab. Jayawijaya (Wamena)'],
                // ===== PAPUA BARAT =====
                ['city_id' => 'dist_91.71.01', 'city_name' => 'Kota Sorong (Sorong Kota)'],
                ['city_id' => 'dist_91.71.02', 'city_name' => 'Kota Sorong (Sorong Utara)'],
                ['city_id' => 'dist_91.01.01', 'city_name' => 'Kab. Manokwari (Manokwari Kota)'],
                ['city_id' => 'dist_91.06.01', 'city_name' => 'Kab. Fakfak (Fakfak Kota)'],
            ];
        });
    }
}
