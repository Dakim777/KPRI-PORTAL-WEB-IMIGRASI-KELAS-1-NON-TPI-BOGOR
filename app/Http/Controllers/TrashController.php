<?php

namespace App\Http\Controllers;

use App\Models\ProdukKantin;
use App\Models\TransaksiKantin;
use App\Models\TransaksiPaspor;
use Illuminate\Http\Request;

class TrashController extends Controller
{
    public function index()
    {
        $trashedProduk = ProdukKantin::onlyTrashed()->with('kategori')->get();
        $trashedPaspor = TransaksiPaspor::onlyTrashed()->with('kasir')->get();
        $trashedTransaksiKantin = TransaksiKantin::onlyTrashed()->with('kasir')->get();

        return view('admin.trash', compact('trashedProduk', 'trashedPaspor', 'trashedTransaksiKantin'));
    }

    public function restoreProduk($id)
    {
        ProdukKantin::onlyTrashed()->findOrFail($id)->restore();
        return back()->with('success', 'Produk berhasil direstore.');
    }

    public function forceDeleteProduk($id)
    {
        ProdukKantin::onlyTrashed()->findOrFail($id)->forceDelete();
        return back()->with('success', 'Produk dihapus permanen.');
    }

    public function restorePaspor($id)
    {
        TransaksiPaspor::onlyTrashed()->findOrFail($id)->restore();
        return back()->with('success', 'Transaksi paspor berhasil direstore.');
    }

    public function forceDeletePaspor($id)
    {
        TransaksiPaspor::onlyTrashed()->findOrFail($id)->forceDelete();
        return back()->with('success', 'Transaksi paspor dihapus permanen.');
    }

    public function restoreTransaksiKantin($id)
    {
        TransaksiKantin::onlyTrashed()->findOrFail($id)->restore();
        return back()->with('success', 'Transaksi kantin berhasil direstore.');
    }

    public function forceDeleteTransaksiKantin($id)
    {
        TransaksiKantin::onlyTrashed()->findOrFail($id)->forceDelete();
        return back()->with('success', 'Transaksi kantin dihapus permanen.');
    }
}
