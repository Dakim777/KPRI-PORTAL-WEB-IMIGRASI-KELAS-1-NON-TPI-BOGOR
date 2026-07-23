<?php

namespace App\Http\Controllers;

use App\Services\WhatsappService;
use Illuminate\Http\Request;

class WhatsappController extends Controller
{
    protected WhatsappService $waService;

    public function __construct(WhatsappService $waService)
    {
        $this->waService = $waService;
    }

    /**
     * Tampilkan halaman dasbor status WhatsApp dan pemindaian QR Code.
     */
    public function index()
    {
        $status = $this->waService->getStatus();
        return view('paspor.whatsapp', compact('status'));
    }

    /**
     * Endpoint AJAX API untuk polling status & QR Code live secara periodik.
     */
    public function statusAjax()
    {
        return response()->json($this->waService->getStatus());
    }

    /**
     * Endpoint untuk tes kirim pesan dari dasbor Admin.
     */
    public function sendTest(Request $request)
    {
        $request->validate([
            'phone'   => 'required|string',
            'message' => 'required|string',
        ]);

        $result = $this->waService->send($request->phone, $request->message);

        if (($result['status'] ?? '') === 'success') {
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message'] ?? 'Gagal mengirim pesan tes.');
    }

    /**
     * Putuskan koneksi (Logout/Disconnect) dan reset sesi nomor HP.
     */
    public function logout()
    {
        $result = $this->waService->logout();

        if (($result['status'] ?? '') === 'success') {
            return back()->with('success', 'Sesi WhatsApp berhasil diputus (Logout). QR Code baru sedang disiapkan.');
        }

        return back()->with('error', $result['message'] ?? 'Gagal melakukan logout bot.');
    }

    /**
     * Kirim notifikasi resi paspor dari tombol kasir.
     */
    public function sendResiPaspor($pasporId)
    {
        $transaksi = \App\Models\TransaksiPaspor::findOrFail($pasporId);
        $result = $this->waService->sendNotificationResi($transaksi);

        if (($result['status'] ?? '') === 'success') {
            return response()->json(['status' => 'success', 'message' => 'Notifikasi WhatsApp berhasil dikirim ke ' . $transaksi->nomor_hp_pemohon]);
        }

        return response()->json(['status' => 'error', 'message' => $result['message'] ?? 'Gagal mengirim WhatsApp.'], 500);
    }
}
