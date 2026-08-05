<?php

namespace App\Http\Controllers;

use App\Exports\LaporanPasporDasarExport;
use App\Exports\LaporanPasporKompleksExport;
use App\Models\TransaksiPaspor;
use App\Services\LogistikService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class PasporController extends Controller
{
    private function buildQuery(Request $request)
    {
        $query = TransaksiPaspor::query()->with('kasir');

        $query->search($request->input('search'));
        $query->status($request->input('status'));

        if ($request->filled('tanggal_dari') || $request->filled('tanggal_sampai')) {
            $query->tanggalRange($request->input('tanggal_dari'), $request->input('tanggal_sampai'));
        } elseif ($request->filled('tanggal')) {
            $query->tanggal($request->input('tanggal'));
        } else {
            $query->bulanTahun($request->input('bulan'), $request->input('tahun'));
        }

        return $query->orderBy('tanggal_transaksi', 'desc')->orderBy('id', 'desc');
    }

    public function index(Request $request)
    {
        $filteredQuery = $this->buildQuery($request);

        $summary = (clone $filteredQuery)->reorder()->selectRaw('
            COUNT(*)                    AS total_pengiriman,
            SUM(total_pembayaran_pemohon) AS grand_total
        ')->first();

        $today     = now()->toDateString();
        $thisMonth = now()->month;
        $thisYear  = now()->year;

        $statHariIni    = TransaksiPaspor::whereDate('tanggal_transaksi', $today)->count();
        $statBulanIni   = TransaksiPaspor::whereMonth('tanggal_transaksi', $thisMonth)
                                         ->whereYear('tanggal_transaksi', $thisYear)->count();
        $omsetHariIni   = TransaksiPaspor::whereDate('tanggal_transaksi', $today)->sum('total_pembayaran_pemohon');

        $transaksis = (clone $filteredQuery)->paginate(20)->withQueryString();

        return view('paspor.index', compact(
            'transaksis',
            'summary',
            'statHariIni',
            'statBulanIni',
            'omsetHariIni'
        ));
    }

    public function create(LogistikService $logistikService)
    {
        $daftarKota = $logistikService->getDaftarKota();
        return view('paspor.create', compact('daftarKota'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_penerima'            => ['required', 'string', 'max:255'],
            'jumlah_paspor'            => ['required', 'integer', 'min:1'],
            'nama_paspor_list'         => ['required', 'array'],
            'nama_paspor_list.*'       => ['required', 'string', 'max:255'],
            'alamat_tujuan_pengiriman' => ['required', 'string'],
            'tanggal_transaksi'        => ['required', 'date'],
            
            'total_pembayaran_pemohon' => ['nullable', 'numeric', 'min:0'],
            'no_hp'                    => ['nullable', 'string', 'max:20'],
            'metode_pembayaran'        => ['nullable', 'in:Cash,Transfer,QRIS'],
            'handling_fee'             => ['nullable', 'numeric', 'min:0'],
            'biaya_kurir'              => ['nullable', 'numeric', 'min:0'],
            'jenis_kurir'              => ['nullable', 'string', 'max:50'],
            'layanan_kurir'            => ['nullable', 'string', 'max:100'],
            'biaya_modal_packaging'    => ['nullable', 'numeric', 'min:0'],
            'harga_materai'            => ['nullable', 'numeric', 'min:0'],
            'harga_surat_kuasa'        => ['nullable', 'numeric', 'min:0'],
            'estimasi_tanggal_selesai' => ['nullable', 'date'],
        ]);

        $today = Carbon::parse($data['tanggal_transaksi'])->format('Y-m-d');
        
        // Generate No Struk (Reset harian)
        $lastStruk = TransaksiPaspor::withTrashed()
                        ->whereDate('tanggal_transaksi', $today)
                        ->max('no_struk');
        $data['no_struk'] = $lastStruk ? $lastStruk + 1 : 1;
        
        // Generate ID Transaksi (KBG-YYMMDD-XXX) yang profesional, singkat, dan 100% unik
        $dateCode = Carbon::parse($today)->format('ymd');
        $data['id_transaksi'] = 'KBG-' . $dateCode . '-' . str_pad($data['no_struk'], 3, '0', STR_PAD_LEFT);
        
        $data['kasir_id'] = Auth::id();
        $data['status_pengiriman'] = 'transaksi';
        $data['jenis_kurir'] = $data['jenis_kurir'] ?? 'POS';
        $data['layanan_kurir'] = $data['layanan_kurir'] ?? 'Reguler';
        // Pastikan field numerik tidak pernah NULL saat disimpan ke database
        $data['biaya_kurir']           = isset($data['biaya_kurir']) && $data['biaya_kurir'] !== '' ? (float) $data['biaya_kurir'] : 0;
        $data['handling_fee']          = isset($data['handling_fee']) && $data['handling_fee'] !== '' ? (float) $data['handling_fee'] : 0;
        $data['biaya_modal_packaging'] = isset($data['biaya_modal_packaging']) && $data['biaya_modal_packaging'] !== '' ? (float) $data['biaya_modal_packaging'] : 0;
        $data['harga_materai']         = isset($data['harga_materai']) && $data['harga_materai'] !== '' ? (float) $data['harga_materai'] : 0;
        $data['harga_surat_kuasa']     = isset($data['harga_surat_kuasa']) && $data['harga_surat_kuasa'] !== '' ? (float) $data['harga_surat_kuasa'] : 0;

        $transaksi = TransaksiPaspor::create($data);

        return redirect()
            ->route('paspor.show', $transaksi->id)
            ->with('success', "Data pengiriman untuk \"{$data['nama_penerima']}\" berhasil dibuat (Status: Transaksi).");
    }

    public function show(TransaksiPaspor $paspor)
    {
        $paspor->load('kasir');
        return view('paspor.show', compact('paspor'));
    }

    public function updateStatus(Request $request, TransaksiPaspor $paspor)
    {
        $request->validate([
            'status_pengiriman'        => ['required', 'in:transaksi,diproses,selesai,batal'],
            'total_pembayaran_pemohon' => ['nullable', 'numeric', 'min:0'],
            'biaya_kurir'              => ['nullable', 'numeric', 'min:0'],
            'jenis_kurir'              => ['nullable', 'string', 'max:50'],
            'layanan_kurir'            => ['nullable', 'string', 'max:100'],
            'biaya_modal_packaging'    => ['nullable', 'numeric', 'min:0'],
            'harga_materai'            => ['nullable', 'numeric', 'min:0'],
            'harga_surat_kuasa'        => ['nullable', 'numeric', 'min:0'],
            'handling_fee'             => ['nullable', 'numeric', 'min:0'],
            'no_resi'                  => ['nullable', 'string', 'max:255'],
        ]);

        $newStatus = $request->status_pengiriman;
        $updates = ['status_pengiriman' => $newStatus];

        // Save biaya fields if provided
        $biayaFields = ['total_pembayaran_pemohon', 'biaya_kurir', 'jenis_kurir', 'layanan_kurir', 'biaya_modal_packaging', 'harga_materai', 'harga_surat_kuasa', 'handling_fee', 'no_resi'];
        foreach ($biayaFields as $field) {
            if ($request->filled($field)) {
                $updates[$field] = $request->input($field);
            }
        }

        if ($newStatus === 'diproses') {
            if (!$paspor->tanggal_pengiriman_paspor) {
                $updates['tanggal_pengiriman_paspor'] = now()->format('Y-m-d');
            }
            // Only auto-set packaging cost if operator didn't supply one
            if (!isset($updates['biaya_modal_packaging']) && ($paspor->biaya_modal_packaging === null || $paspor->biaya_modal_packaging == 0)) {
                $updates['biaya_modal_packaging'] = 1000;
            }
        } elseif ($newStatus === 'selesai') {
            if (!$paspor->tanggal_paspor_diterima) {
                $updates['tanggal_paspor_diterima'] = now()->format('Y-m-d');
            }
        }

        $paspor->update($updates);

        // Jika status pengiriman diubah ke 'diproses' atau 'selesai' dan ada nomor resi serta nomor HP, otomatis kirim notifikasi WhatsApp
        if (in_array($newStatus, ['diproses', 'selesai']) && !empty($paspor->no_resi) && !empty($paspor->no_hp)) {
            try {
                app(\App\Services\WhatsappService::class)->sendNotificationResi($paspor);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Gagal kirim notifikasi WA otomatis saat updateStatus: ' . $e->getMessage());
            }
        }

        return back()->with('success', "Status dan data biaya untuk \"{$paspor->nama_penerima}\" berhasil diperbarui.");
    }

    public function edit(TransaksiPaspor $paspor, LogistikService $logistikService)
    {
        $daftarKota = $logistikService->getDaftarKota();
        return view('paspor.edit', compact('paspor', 'daftarKota'));
    }

    public function update(Request $request, TransaksiPaspor $paspor)
    {
        $validated = $request->validate([
            'nama_penerima'            => 'required|string|max:255',
            'jumlah_paspor'            => 'required|integer|min:1',
            'nama_paspor_list'         => 'required|array',
            'nama_paspor_list.*'       => 'required|string|max:255',
            'alamat_tujuan_pengiriman' => 'required|string',
            'tanggal_transaksi'        => 'required|date',
            
            // Kolom yg diisi opsional atau saat diproses
            'total_pembayaran_pemohon' => 'nullable|numeric|min:0',
            'no_hp'                    => 'nullable|string|max:20',
            'metode_pembayaran'        => 'nullable|in:Cash,Transfer,QRIS',
            'handling_fee'             => 'nullable|numeric|min:0',
            'no_resi'                  => 'nullable|string|max:255',
            'biaya_kurir'              => 'nullable|numeric|min:0',
            'biaya_modal_packaging'    => 'nullable|numeric|min:0',
            'harga_materai'            => 'nullable|numeric|min:0',
            'harga_surat_kuasa'        => 'nullable|numeric|min:0',
            'tanggal_pengiriman_paspor'=> 'nullable|date',
            'tanggal_paspor_diterima'  => 'nullable|date',
            'estimasi_tanggal_selesai' => 'nullable|date',
            'status_pengiriman'        => 'required|in:transaksi,diproses,batal,selesai'
        ]);

        $paspor->update($validated);

        return redirect()->route('paspor.show', $paspor->id)
            ->with('success', 'Transaksi pengiriman paspor berhasil diperbarui.');
    }

    public function destroy(TransaksiPaspor $paspor)
    {
        if (auth()->user()->role !== 'super_admin') {
            abort(403, 'Aksi tidak diizinkan. Hanya Super Admin yang dapat menghapus data.');
        }

        $nama = $paspor->nama_penerima;
        $paspor->delete();

        return redirect()->route('paspor.index')
            ->with('success', "Riwayat transaksi atas nama \"{$nama}\" berhasil dihapus.");
    }
    
    // --- Laporan Exports ---


    
    public function exportKompleks(Request $request)
    {
        if (auth()->user()->role !== 'super_admin') {
            abort(403, 'Aksi tidak diizinkan. Hanya Super Admin yang dapat mengunduh Laporan Kompleks.');
        }
        
        $dari   = $request->input('tanggal_dari');
        $sampai = $request->input('tanggal_sampai');

        $suffix   = ($dari && $sampai) ? "{$dari}_sd_{$sampai}" : now()->format('Ymd_His');
        $filename = "laporan_paspor_kompleks_{$suffix}.xlsx";

        if (ob_get_level() > 0) {
            @ob_end_clean();
        }

        return Excel::download(new LaporanPasporKompleksExport($dari, $sampai), $filename);
    }
    
    // --- Cetak Struk ---
    public function printReceipt(TransaksiPaspor $paspor)
    {
        return view('paspor.receipt', compact('paspor'));
    }

    // --- API Logistik: Cek Ongkir (RajaOngkir Starter) ---
    public function cekOngkir(Request $request, LogistikService $logistikService)
    {
        $request->validate([
            'destination' => 'required',
            'courier'     => 'nullable|string',
            'weight'      => 'nullable|integer'
        ]);

        $result = $logistikService->cekOngkir(
            $request->input('destination'),
            (int) $request->input('weight', 200),
            $request->input('courier', 'pos')
        );

        return response()->json($result);
    }

    // --- API Logistik: Lacak Resi (BinderByte) ---
    public function trackResi(Request $request, TransaksiPaspor $paspor, LogistikService $logistikService)
    {
        if (empty($paspor->no_resi)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Nomor resi belum diinput pada transaksi ini.'
            ], 400);
        }

        if ($paspor->status_pengiriman === 'batal') {
            return response()->json([
                'status'  => 200,
                'message' => 'Status pengiriman paspor ini telah dibatalkan.',
                'is_fallback' => false,
                'is_mock' => false,
                'data'    => [
                    'summary' => [
                        'awb'     => $paspor->no_resi,
                        'courier' => strtoupper($paspor->jenis_kurir ?? 'POS'),
                        'status'  => 'DIBATALKAN (CANCELLED)',
                        'status_indonesia' => 'DIBATALKAN (CANCELLED)',
                        'date'    => $paspor->updated_at ? $paspor->updated_at->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s'),
                        'desc'    => 'Pengiriman atau pemrosesan paspor ini telah DIBATALKAN. Riwayat pergerakan paket tidak dapat diakses.'
                    ],
                    'history' => []
                ]
            ]);
        }

        $forceRefresh = $request->boolean('refresh');
        $result = $logistikService->lacakResi($paspor->no_resi, strtolower($paspor->jenis_kurir ?? 'pos'), $forceRefresh);

        return response()->json($result);
    }
}
