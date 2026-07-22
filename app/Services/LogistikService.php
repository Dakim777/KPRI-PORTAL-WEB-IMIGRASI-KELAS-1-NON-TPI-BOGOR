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

            $response = Http::asForm()->timeout(15)->post("{$baseUrl}/cost", [
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

        // Cache selama 120 menit (2 jam) agar tidak boros kuota BinderByte untuk resi yang sama
        $cacheKey = "tracking_resi_" . strtolower($courier) . "_{$nomorResi}";

        return Cache::remember($cacheKey, 120 * 60, function () use ($nomorResi, $courier) {
            $apiKey = config('services.binderbyte.key');
            $baseUrl = config('services.binderbyte.base_url', 'https://api.binderbyte.com/v1');

            if (empty($apiKey)) {
                // Mode simulasi/mock jika BINDERBYTE_API_KEY belum dipasang di .env
                return [
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
                ];
            }

            $couriersToTry = array_unique([strtolower($courier), 'pos', 'jne', 'sicepat', 'jnt', 'tiki', 'anteraja', 'lion', 'ninja', 'ide']);
            
            foreach ($couriersToTry as $tryCourier) {
                try {
                    $response = Http::timeout(12)->get("{$baseUrl}/track", [
                        'api_key' => $apiKey,
                        'courier' => $tryCourier,
                        'awb'     => $nomorResi,
                    ]);

                    if ($response->successful() && !empty($response->json()['data']['summary'])) {
                        $json = $response->json();
                        $json['is_mock'] = false;
                        return $json;
                    }
                } catch (\Exception $e) {
                    Log::warning("Gangguan/Timeout API BinderByte lacakResi ({$nomorResi} via {$tryCourier}): " . $e->getMessage());
                }
            }

            Cache::forget("tracking_resi_" . strtolower($courier) . "_{$nomorResi}");

            // Fallback transparan: jika resi tidak/belum ditemukan di server ekspedisi mana pun (karena baru dicetak / belum di-scan)
            return [
                'status'  => 200,
                'message' => 'Resi terdaftar di sistem Koperasi (Belum terindeks/ditemukan pada pemindaian live ekspedisi)',
                'is_fallback' => true,
                'is_mock' => false,
                'data'    => [
                    'summary' => [
                        'awb'     => $nomorResi,
                        'courier' => strtoupper($courier),
                        'status'  => 'BELUM TERINDEKS DI POS / EKSPEDISI',
                        'date'    => now()->format('Y-m-d H:i:s'),
                        'desc'    => "Nomor resi {$nomorResi} telah dicatat pada sistem Koperasi Kantor Imigrasi Bogor, namun saat ini belum ditemukan pada pangkalan data pemindaian ekspedisi ({$courier}). Jika resi baru dicetak, harap menunggu proses pemindaian fisik oleh kurir atau pastikan penulisan nomor resi sudah benar."
                    ],
                    'history' => [
                        [
                            'date' => now()->format('Y-m-d H:i:s'),
                            'desc' => "Nomor resi {$nomorResi} (" . strtoupper($courier) . ") belum muncul di pemindaian live POS Indonesia. Status saat ini: Menunggu sinkronisasi atau pemindaian fisik dari kurir ekspedisi.",
                            'location' => 'POS Indonesia / Dalam Proses'
                        ],
                        [
                            'date' => now()->subMinutes(10)->format('Y-m-d H:i:s'),
                            'desc' => "Data transaksi pengiriman paspor dicatat pada sistem Koperasi Kantor Imigrasi Kelas I Non-TPI Bogor.",
                            'location' => 'Kantor Imigrasi Bogor'
                        ]
                    ]
                ]
            ];
        });
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
