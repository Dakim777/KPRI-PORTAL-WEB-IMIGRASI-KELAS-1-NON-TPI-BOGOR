<?php

namespace App\Http\Controllers;

use App\Models\TransaksiPaspor;
use Illuminate\Http\Request;

class TrashController extends Controller
{
    public function index()
    {
        $trashedPaspor = TransaksiPaspor::onlyTrashed()->with('kasir')->get();

        return view('admin.trash', compact('trashedPaspor'));
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
}
