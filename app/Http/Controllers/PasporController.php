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
        
        // Generate ID Transaksi (TRX-PASPOR-YYMMDD-XXX)
        $dateCode = Carbon::parse($today)->format('ymd');
        $data['id_transaksi'] = 'TRX-PASPOR-' . $dateCode . '-' . str_pad($data['no_struk'], 3, '0', STR_PAD_LEFT);
        
        $data['kasir_id'] = Auth::id();
        $data['status_pengiriman'] = 'transaksi';
        $data['jenis_kurir'] = $data['jenis_kurir'] ?? 'POS';
        $data['layanan_kurir'] = $data['layanan_kurir'] ?? 'Reguler';

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
    public function trackResi(TransaksiPaspor $paspor, LogistikService $logistikService)
    {
        if (empty($paspor->no_resi)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Nomor resi belum diinput pada transaksi ini.'
            ], 400);
        }

        $result = $logistikService->lacakResi($paspor->no_resi, strtolower($paspor->jenis_kurir ?? 'pos'));

        // ── Real-Time Lazy Auto-Sync untuk Kasir / Admin ──
        if (($result['status'] ?? 0) == 200 && !empty($result['data']['summary']['status']) && empty($result['is_fallback'])) {
            $statusResi = strtoupper(trim($result['data']['summary']['status']));
            if ($paspor->status_pengiriman === 'diproses' && in_array($statusResi, ['DELIVERED', 'SELESAI', 'TERKIRIM', 'SUCCESS', 'DITERIMA'])) {
                $tanggalDiterima = today();
                try {
                    if (!empty($result['data']['summary']['date'])) {
                        $tanggalDiterima = \Carbon\Carbon::parse($result['data']['summary']['date'])->format('Y-m-d');
                    } elseif (!empty($result['data']['history'][0]['date'])) {
                        $tanggalDiterima = \Carbon\Carbon::parse($result['data']['history'][0]['date'])->format('Y-m-d');
                    }
                } catch (\Exception $e) {
                    $tanggalDiterima = today();
                }

                $paspor->update([
                    'status_pengiriman'       => 'selesai',
                    'tanggal_paspor_diterima' => $tanggalDiterima
                ]);
            }
        }

        return response()->json($result);
    }
}
