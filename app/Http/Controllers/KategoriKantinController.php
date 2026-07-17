<?php

namespace App\Http\Controllers;

use App\Models\KategoriKantin;
use Illuminate\Http\Request;

class KategoriKantinController extends Controller
{
    public function index()
    {
        $kategoris = KategoriKantin::withCount('produks')->orderBy('nama_kategori')->paginate(15);
        return view('kantin.kategori.index', compact('kategoris'));
    }

    public function create()
    {
        return view('kantin.kategori.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_kategori' => ['required', 'string', 'max:100', 'unique:kategori_kantins,nama_kategori'],
            'deskripsi'     => ['nullable', 'string', 'max:255'],
        ]);

        KategoriKantin::create($data);

        return redirect()->route('kantin.kategori.index')
            ->with('success', "Kategori \"{$data['nama_kategori']}\" berhasil ditambahkan.");
    }

    public function edit(KategoriKantin $kategori)
    {
        return view('kantin.kategori.edit', compact('kategori'));
    }

    public function update(Request $request, KategoriKantin $kategori)
    {
        $data = $request->validate([
            'nama_kategori' => ['required', 'string', 'max:100', 'unique:kategori_kantins,nama_kategori,' . $kategori->id],
            'deskripsi'     => ['nullable', 'string', 'max:255'],
        ]);

        $kategori->update($data);

        return redirect()->route('kantin.kategori.index')
            ->with('success', "Kategori \"{$data['nama_kategori']}\" berhasil diperbarui.");
    }

    public function destroy(KategoriKantin $kategori)
    {
        $nama = $kategori->nama_kategori;
        // Products become kategori_id = null (nullOnDelete migration)
        $kategori->delete();

        return redirect()->route('kantin.kategori.index')
            ->with('success', "Kategori \"{$nama}\" berhasil dihapus. Produk terkait tidak dihapus.");
    }

    public function show(KategoriKantin $kategori)
    {
        return redirect()->route('kantin.kategori.edit', $kategori);
    }
}
