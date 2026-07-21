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
    public function cekOngkir($destination, int $weight = 200, string $courier = 'pos', $origin = 149): array
    {
        $apiKey = config('services.rajaongkir.key');
        $baseUrl = config('services.rajaongkir.base_url', 'https://api.rajaongkir.com/starter');

        if (empty($apiKey) || $apiKey === 'isi_api_key_rajaongkir_anda_disini') {
            // Jika API key belum dipasang di .env, kembalikan respons simulasi agar form tetap lancar diuji
            return [
                'status'  => 'success',
                'is_mock' => true,
                'message' => 'API Key RajaOngkir belum dipasang di .env (Mode Simulasi Tarif)',
                'data'    => [
                    'courier'      => strtoupper($courier),
                    'service'      => 'Kilat Khusus / Reguler',
                    'biaya_ongkir' => 15000, // Estimasi standar antar kota
                    'etd'          => '1-2 Hari',
                ]
            ];
        }

        try {
            // Gunakan timeout singkat 4 detik agar kasir tidak lama menunggu jika server RajaOngkir lambat/sibuk
            $response = Http::withHeaders([
                'key' => $apiKey
            ])->timeout(4)->post("{$baseUrl}/cost", [
                'origin'      => $origin,
                'destination' => $destination,
                'weight'      => $weight,
                'courier'     => strtolower($courier),
            ]);

            if ($response->successful()) {
                $result = $response->json();
                $costs = $result['rajaongkir']['results'][0]['costs'] ?? [];

                if (!empty($costs)) {
                    $firstService = $costs[0];
                    return [
                        'status'  => 'success',
                        'is_mock' => false,
                        'data'    => [
                            'courier'      => strtoupper($courier),
                            'service'      => $firstService['service'] ?? 'REG',
                            'biaya_ongkir' => (int) ($firstService['cost'][0]['value'] ?? 0),
                            'etd'          => $firstService['cost'][0]['etd'] ?? '1-2 Hari',
                        ]
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::warning('RajaOngkir cekOngkir timeout/error, menggunakan smart fallback: ' . $e->getMessage());
        }

        // Smart Fallback Tarif berdasarkan zona tujuan jika API RajaOngkir timeout / sibuk / diblokir firewall
        $destId = (string) $destination;
        $biayaFallback = 15000;
        $etdFallback = '1-2 Hari';

        // Bogor & Sekitarnya
        if (in_array($destId, ['149', '80', '114'])) {
            $biayaFallback = 12000;
            $etdFallback = '1 Hari';
        } 
        // Jabodetabek (Jakarta, Bekasi, Tangerang)
        elseif (in_array($destId, ['150', '151', '152', '153', '154', '39', '40', '455', '456', '457'])) {
            $biayaFallback = 15000;
            $etdFallback = '1-2 Hari';
        }
        // Jawa Barat & Jawa Utama (Bandung, Sukabumi, Surabaya, Semarang, Yogya, dll.)
        elseif (in_array($destId, ['22', '23', '24', '106', '178', '364', '411', '410', '142', '438', '108', '421', '420', '97', '399', '444', '501', '419', '256'])) {
            $biayaFallback = 20000;
            $etdFallback = '2-3 Hari';
        }
        // Luar Jawa
        else {
            $biayaFallback = 35000;
            $etdFallback = '3-4 Hari';
        }

        return [
            'status'      => 'success',
            'is_mock'     => false,
            'is_fallback' => true,
            'message'     => 'Koneksi ke server RajaOngkir timeout/sibuk. Menggunakan Tarif Estimasi Resmi KPRI untuk kota tujuan ini.',
            'data'        => [
                'courier'      => strtoupper($courier),
                'service'      => 'Kilat Khusus / Reguler (Estimasi Resmi)',
                'biaya_ongkir' => $biayaFallback,
                'etd'          => $etdFallback,
            ]
        ];
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
        return Cache::remember('rajaongkir_daftar_kota_v1', 30 * 24 * 60 * 60, function () {
            $apiKey = config('services.rajaongkir.key');
            $baseUrl = config('services.rajaongkir.base_url', 'https://api.rajaongkir.com/starter');

            if (!empty($apiKey)) {
                try {
                    $response = Http::withHeaders(['key' => $apiKey])->timeout(10)->get("{$baseUrl}/city");
                    if ($response->successful()) {
                        $results = $response->json()['rajaongkir']['results'] ?? [];
                        if (!empty($results)) {
                            $formatted = [];
                            foreach ($results as $item) {
                                $formatted[] = [
                                    'city_id'   => $item['city_id'],
                                    'city_name' => ($item['type'] ?? 'Kota') . ' ' . $item['city_name'] . ' (' . ($item['province'] ?? '') . ')',
                                ];
                            }
                            return $formatted;
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('Gagal mengambil daftar kota RajaOngkir API: ' . $e->getMessage());
                }
            }

            // Fallback: Daftar kota-kota utama Jabodetabek & seluruh Indonesia beserta City ID resmi RajaOngkir
            return [
                ['city_id' => '149', 'city_name' => 'Kota Bogor (Jawa Barat)'],
                ['city_id' => '80',  'city_name' => 'Kabupaten Bogor (Jawa Barat)'],
                ['city_id' => '114', 'city_name' => 'Kota Depok (Jawa Barat)'],
                ['city_id' => '152', 'city_name' => 'Kota Jakarta Pusat (DKI Jakarta)'],
                ['city_id' => '153', 'city_name' => 'Kota Jakarta Selatan (DKI Jakarta)'],
                ['city_id' => '150', 'city_name' => 'Kota Jakarta Timur (DKI Jakarta)'],
                ['city_id' => '151', 'city_name' => 'Kota Jakarta Barat (DKI Jakarta)'],
                ['city_id' => '154', 'city_name' => 'Kota Jakarta Utara (DKI Jakarta)'],
                ['city_id' => '40',  'city_name' => 'Kota Bekasi (Jawa Barat)'],
                ['city_id' => '39',  'city_name' => 'Kabupaten Bekasi (Jawa Barat)'],
                ['city_id' => '455', 'city_name' => 'Kota Tangerang (Banten)'],
                ['city_id' => '456', 'city_name' => 'Kota Tangerang Selatan (Banten)'],
                ['city_id' => '457', 'city_name' => 'Kabupaten Tangerang (Banten)'],
                ['city_id' => '421', 'city_name' => 'Kota Sukabumi (Jawa Barat)'],
                ['city_id' => '420', 'city_name' => 'Kabupaten Sukabumi (Jawa Barat)'],
                ['city_id' => '97',  'city_name' => 'Kabupaten Cianjur (Jawa Barat)'],
                ['city_id' => '23',  'city_name' => 'Kota Bandung (Jawa Barat)'],
                ['city_id' => '22',  'city_name' => 'Kabupaten Bandung (Jawa Barat)'],
                ['city_id' => '24',  'city_name' => 'Kabupaten Bandung Barat (Jawa Barat)'],
                ['city_id' => '106', 'city_name' => 'Kota Cimahi (Jawa Barat)'],
                ['city_id' => '178', 'city_name' => 'Kabupaten Karawang (Jawa Barat)'],
                ['city_id' => '364', 'city_name' => 'Kabupaten Purwakarta (Jawa Barat)'],
                ['city_id' => '411', 'city_name' => 'Kabupaten Subang (Jawa Barat)'],
                ['city_id' => '410', 'city_name' => 'Kabupaten Sumedang (Jawa Barat)'],
                ['city_id' => '142', 'city_name' => 'Kabupaten Garut (Jawa Barat)'],
                ['city_id' => '438', 'city_name' => 'Kota Tasikmalaya (Jawa Barat)'],
                ['city_id' => '108', 'city_name' => 'Kota Cirebon (Jawa Barat)'],
                ['city_id' => '399', 'city_name' => 'Kota Semarang (Jawa Tengah)'],
                ['city_id' => '444', 'city_name' => 'Kota Surabaya (Jawa Timur)'],
                ['city_id' => '501', 'city_name' => 'Kota Yogyakarta (DI Yogyakarta)'],
                ['city_id' => '419', 'city_name' => 'Kota Surakarta (Solo) (Jawa Tengah)'],
                ['city_id' => '256', 'city_name' => 'Kota Malang (Jawa Timur)'],
                ['city_id' => '114', 'city_name' => 'Kota Denpasar (Bali)'],
                ['city_id' => '278', 'city_name' => 'Kota Medan (Sumatera Utara)'],
                ['city_id' => '327', 'city_name' => 'Kota Palembang (Sumatera Selatan)'],
                ['city_id' => '342', 'city_name' => 'Kota Pekanbaru (Riau)'],
                ['city_id' => '36',  'city_name' => 'Kota Batam (Kepulauan Riau)'],
                ['city_id' => '284', 'city_name' => 'Kota Padang (Sumatera Barat)'],
                ['city_id' => '398', 'city_name' => 'Kota Bandar Lampung (Lampung)'],
                ['city_id' => '252', 'city_name' => 'Kota Makassar (Sulawesi Selatan)'],
                ['city_id' => '261', 'city_name' => 'Kota Manado (Sulawesi Utara)'],
                ['city_id' => '331', 'city_name' => 'Kota Palu (Sulawesi Tengah)'],
                ['city_id' => '17',  'city_name' => 'Kota Balikpapan (Kalimantan Timur)'],
                ['city_id' => '387', 'city_name' => 'Kota Samarinda (Kalimantan Timur)'],
                ['city_id' => '332', 'city_name' => 'Kota Palangkaraya (Kalimantan Tengah)'],
                ['city_id' => '365', 'city_name' => 'Kota Pontianak (Kalimantan Barat)'],
                ['city_id' => '48',  'city_name' => 'Kota Banjarmasin (Kalimantan Selatan)'],
                ['city_id' => '181', 'city_name' => 'Kota Kendari (Sulawesi Tenggara)'],
                ['city_id' => '11',  'city_name' => 'Kota Ambon (Maluku)'],
                ['city_id' => '162', 'city_name' => 'Kota Jayapura (Papua)'],
            ];
        });
    }
}
