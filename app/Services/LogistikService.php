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
    public function cekOngkir($destination, int $weight = 200, string $courier = 'pos', $origin = 'bogor'): array
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

                $errorDesc = $result['message'] ?? 'Layanan kurir ' . strtoupper($courier) . ' tidak tersedia untuk rute kota ini dari BinderByte.';
                return [
                    'status'  => 'error',
                    'message' => $errorDesc
                ];
            }

            $errorDesc = $response->json()['message'] ?? $response->body();
            return [
                'status'  => 'error',
                'message' => 'Gagal mengambil data ongkir live dari BinderByte: ' . $errorDesc
            ];
        } catch (\Exception $e) {
            Log::error('Error BinderByte cekOngkir live: ' . $e->getMessage());
            return [
                'status'  => 'error',
                'message' => 'Terjadi kesalahan koneksi live ke server BinderByte: ' . $e->getMessage()
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

            try {
                $response = Http::timeout(10)->get("{$baseUrl}/track", [
                    'api_key' => $apiKey,
                    'courier' => strtolower($courier),
                    'awb'     => $nomorResi,
                ]);

                if ($response->successful()) {
                    $json = $response->json();
                    $json['is_mock'] = false;
                    return $json;
                }

                // Jika error, tidak perlu di-cache permanen (atau buang cache agar bisa retried)
                Cache::forget("tracking_resi_" . strtolower($courier) . "_{$nomorResi}");
                return [
                    'status'  => $response->status(),
                    'message' => 'Gagal melacak resi dari BinderByte: ' . ($response->json()['message'] ?? $response->body())
                ];
            } catch (\Exception $e) {
                Cache::forget("tracking_resi_" . strtolower($courier) . "_{$nomorResi}");
                Log::error('Error BinderByte lacakResi: ' . $e->getMessage());
                return [
                    'status'  => 500,
                    'message' => 'Terjadi kesalahan koneksi ke API BinderByte: ' . $e->getMessage()
                ];
            }
        });
    }

    /**
     * Ambil daftar kota tujuan pengiriman dari RajaOngkir (atau cache / daftar kota utama Indonesia).
     *
     * @return array
     */
    public function getDaftarKota(): array
    {
        return Cache::remember('binderbyte_daftar_kota_v2', 30 * 24 * 60 * 60, function () {
            // Daftar kota-kota utama Jabodetabek & seluruh Indonesia dengan ID kota resmi BinderByte
            return [
                ['city_id' => 'bogor',       'city_name' => 'Kota & Kabupaten Bogor (Jawa Barat)'],
                ['city_id' => 'depok',       'city_name' => 'Kota Depok (Jawa Barat)'],
                ['city_id' => 'jakarta',     'city_name' => 'DKI Jakarta (Seluruh Wilayah)'],
                ['city_id' => 'bekasi',      'city_name' => 'Kota & Kabupaten Bekasi (Jawa Barat)'],
                ['city_id' => 'tangerang',   'city_name' => 'Kota & Kabupaten Tangerang (Banten)'],
                ['city_id' => 'sukabumi',    'city_name' => 'Kota & Kabupaten Sukabumi (Jawa Barat)'],
                ['city_id' => 'cianjur',     'city_name' => 'Kabupaten Cianjur (Jawa Barat)'],
                ['city_id' => 'bandung',     'city_name' => 'Kota & Kabupaten Bandung (Jawa Barat)'],
                ['city_id' => 'cimahi',      'city_name' => 'Kota Cimahi (Jawa Barat)'],
                ['city_id' => 'karawang',    'city_name' => 'Kabupaten Karawang (Jawa Barat)'],
                ['city_id' => 'purwakarta',  'city_name' => 'Kabupaten Purwakarta (Jawa Barat)'],
                ['city_id' => 'subang',      'city_name' => 'Kabupaten Subang (Jawa Barat)'],
                ['city_id' => 'sumedang',    'city_name' => 'Kabupaten Sumedang (Jawa Barat)'],
                ['city_id' => 'garut',       'city_name' => 'Kabupaten Garut (Jawa Barat)'],
                ['city_id' => 'tasikmalaya', 'city_name' => 'Kota & Kabupaten Tasikmalaya (Jawa Barat)'],
                ['city_id' => 'cirebon',     'city_name' => 'Kota & Kabupaten Cirebon (Jawa Barat)'],
                ['city_id' => 'semarang',    'city_name' => 'Kota Semarang (Jawa Tengah)'],
                ['city_id' => 'surabaya',    'city_name' => 'Kota Surabaya (Jawa Timur)'],
                ['city_id' => 'yogyakarta',  'city_name' => 'Kota Yogyakarta (DI Yogyakarta)'],
                ['city_id' => 'solo',        'city_name' => 'Kota Surakarta / Solo (Jawa Tengah)'],
                ['city_id' => 'malang',      'city_name' => 'Kota Malang (Jawa Timur)'],
                ['city_id' => 'denpasar',    'city_name' => 'Kota Denpasar (Bali)'],
                ['city_id' => 'medan',       'city_name' => 'Kota Medan (Sumatera Utara)'],
                ['city_id' => 'palembang',   'city_name' => 'Kota Palembang (Sumatera Selatan)'],
                ['city_id' => 'pekanbaru',   'city_name' => 'Kota Pekanbaru (Riau)'],
                ['city_id' => 'batam',       'city_name' => 'Kota Batam (Kepulauan Riau)'],
                ['city_id' => 'padang',      'city_name' => 'Kota Padang (Sumatera Barat)'],
                ['city_id' => 'lampung',     'city_name' => 'Kota Bandar Lampung (Lampung)'],
                ['city_id' => 'makassar',    'city_name' => 'Kota Makassar (Sulawesi Selatan)'],
                ['city_id' => 'manado',      'city_name' => 'Kota Manado (Sulawesi Utara)'],
                ['city_id' => 'palu',        'city_name' => 'Kota Palu (Sulawesi Tengah)'],
                ['city_id' => 'balikpapan',  'city_name' => 'Kota Balikpapan (Kalimantan Timur)'],
                ['city_id' => 'samarinda',   'city_name' => 'Kota Samarinda (Kalimantan Timur)'],
                ['city_id' => 'palangkaraya','city_name' => 'Kota Palangkaraya (Kalimantan Tengah)'],
                ['city_id' => 'pontianak',   'city_name' => 'Kota Pontianak (Kalimantan Barat)'],
                ['city_id' => 'banjarmasin', 'city_name' => 'Kota Banjarmasin (Kalimantan Selatan)'],
                ['city_id' => 'kendari',     'city_name' => 'Kota Kendari (Sulawesi Tenggara)'],
                ['city_id' => 'ambon',       'city_name' => 'Kota Ambon (Maluku)'],
                ['city_id' => 'jayapura',    'city_name' => 'Kota Jayapura (Papua)'],
            ];
        });
    }
}
