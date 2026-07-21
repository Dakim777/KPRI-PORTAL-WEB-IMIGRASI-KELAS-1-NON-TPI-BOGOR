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

        if (empty($apiKey)) {
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
            $response = Http::withHeaders([
                'key' => $apiKey
            ])->timeout(10)->post("{$baseUrl}/cost", [
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

            return [
                'status'  => 'error',
                'message' => 'Gagal mengambil data ongkir dari layanan RajaOngkir: ' . $response->body()
            ];
        } catch (\Exception $e) {
            Log::error('Error RajaOngkir cekOngkir: ' . $e->getMessage());
            return [
                'status'  => 'error',
                'message' => 'Terjadi kesalahan koneksi ke API RajaOngkir: ' . $e->getMessage()
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
}
