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
    public function lacakResi(string $nomorResi, string $courier = 'pos'): array
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
        $cached = Cache::get($cacheKey);
        if ($cached && !empty($cached['data']['summary']) && empty($cached['is_fallback']) && empty($cached['is_error'])) {
            return $this->enrichTrackingResult($cached);
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
                // Cache hasil sukses selama 30 menit
                Cache::put($cacheKey, $json, 30 * 60);
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
        // Pastikan deskripsi real terkini dari ekspedisi selalu terisi dan akurat
        $summary['desc'] = $summary['desc'] ?? ($summary['description'] ?? ($summary['message'] ?? ($summary['note'] ?? $rawStatus)));

        // Normalisasi seluruh riwayat (history) agar deskripsi real dari ekspedisi tidak hilang atau kosong
        if (is_array($history)) {
            foreach ($history as $idx => $h) {
                $rawDesc = $h['desc'] ?? ($h['description'] ?? ($h['note'] ?? ($h['message'] ?? ($h['status'] ?? '-'))));
                
                // Jika deskripsi hanya berisi satu kata status Inggris standar (seperti DELIVERED / ON PROCESS), berikan penjelasan terjemahannya
                $upperDesc = strtoupper(trim((string) $rawDesc));
                if (isset($statusMap[$upperDesc])) {
                    $rawDesc = $statusMap[$upperDesc];
                }
                $history[$idx]['desc'] = $rawDesc;
                $history[$idx]['date'] = $h['date'] ?? ($h['time'] ?? ($h['timestamp'] ?? '-'));
                $history[$idx]['location'] = $h['location'] ?? ($h['city'] ?? '');
            }
        }

        $result['data']['summary'] = $summary;
        $result['data']['history'] = $history;

        return $result;
    }

    /**
     * Ambil daftar kota tujuan pengiriman dari RajaOngkir (atau cache / daftar kota utama Indonesia).
     *
     * @return array
     */
    public function getDaftarKota(): array
    {
        return Cache::remember('binderbyte_daftar_kota_v3', 30 * 24 * 60 * 60, function () {
            // Daftar kota-kota utama Indonesia dengan ID kecamatan (dist_) resmi BinderByte
            return [
                ['city_id' => 'dist_32.71.01', 'city_name' => 'Kota Bogor (Bogor Tengah / Kota)'],
                ['city_id' => 'dist_32.01.01', 'city_name' => 'Kabupaten Bogor (Cibinong / Sekitar)'],
                ['city_id' => 'dist_32.76.01', 'city_name' => 'Kota Depok (Pancoran Mas / Sekitar)'],
                ['city_id' => 'dist_31.71.01', 'city_name' => 'Jakarta Selatan (Kebayoran Baru)'],
                ['city_id' => 'dist_31.73.01', 'city_name' => 'Jakarta Pusat (Menteng / Gambir)'],
                ['city_id' => 'dist_31.72.01', 'city_name' => 'Jakarta Timur (Jatinegara)'],
                ['city_id' => 'dist_31.74.01', 'city_name' => 'Jakarta Barat (Grogol Petamburan)'],
                ['city_id' => 'dist_31.75.01', 'city_name' => 'Jakarta Utara (Kelapa Gading)'],
                ['city_id' => 'dist_32.75.01', 'city_name' => 'Kota Bekasi (Bekasi Barat)'],
                ['city_id' => 'dist_36.71.01', 'city_name' => 'Kota Tangerang (Tangerang)'],
                ['city_id' => 'dist_36.74.01', 'city_name' => 'Kota Tangerang Selatan (Serpong)'],
                ['city_id' => 'dist_32.73.01', 'city_name' => 'Kota Bandung (Sumur Bandung)'],
                ['city_id' => 'dist_32.72.01', 'city_name' => 'Kota Sukabumi (Cikole)'],
                ['city_id' => 'dist_33.74.01', 'city_name' => 'Kota Semarang (Semarang Tengah)'],
                ['city_id' => 'dist_34.71.01', 'city_name' => 'Kota Yogyakarta (Danurejan)'],
                ['city_id' => 'dist_33.72.01', 'city_name' => 'Kota Surakarta / Solo (Banjarsari)'],
                ['city_id' => 'dist_35.78.01', 'city_name' => 'Kota Surabaya (Tegalsari)'],
                ['city_id' => 'dist_35.73.01', 'city_name' => 'Kota Malang (Klojen)'],
                ['city_id' => 'dist_51.71.01', 'city_name' => 'Kota Denpasar (Denpasar Selatan)'],
                ['city_id' => 'dist_12.71.01', 'city_name' => 'Kota Medan (Medan Baru)'],
                ['city_id' => 'dist_16.71.01', 'city_name' => 'Kota Palembang (Ilir Barat I)'],
                ['city_id' => 'dist_14.71.01', 'city_name' => 'Kota Pekanbaru (Pekanbaru Kota)'],
                ['city_id' => 'dist_21.71.01', 'city_name' => 'Kota Batam (Batam Kota)'],
                ['city_id' => 'dist_13.71.01', 'city_name' => 'Kota Padang (Padang Barat)'],
                ['city_id' => 'dist_18.71.01', 'city_name' => 'Kota Bandar Lampung (Tanjung Karang Pusat)'],
                ['city_id' => 'dist_73.71.01', 'city_name' => 'Kota Makassar (Ujung Pandang)'],
                ['city_id' => 'dist_71.71.01', 'city_name' => 'Kota Manado (Wenang)'],
                ['city_id' => 'dist_64.71.01', 'city_name' => 'Kota Balikpapan (Balikpapan Kota)'],
                ['city_id' => 'dist_64.72.01', 'city_name' => 'Kota Samarinda (Samarinda Kota)'],
                ['city_id' => 'dist_61.71.01', 'city_name' => 'Kota Pontianak (Pontianak Selatan)'],
                ['city_id' => 'dist_63.71.01', 'city_name' => 'Kota Banjarmasin (Banjarmasin Tengah)'],
            ];
        });
    }
}
