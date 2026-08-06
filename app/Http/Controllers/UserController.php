<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Daftar semua pengguna (Kasir + Admin) dengan pencarian.
     */
    public function index(Request $request)
    {
        $query = User::query()->orderBy('role')->orderBy('name');

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->paginate(15)->withQueryString();

        $stats = [
            'total'         => User::count(),
            'super_admin'   => User::where('role', 'super_admin')->count(),
            'kasir_paspor'  => User::where('role', 'kasir_paspor')->count(),
        ];

        return view('admin.users.index', compact('users', 'stats'));
    }

    /**
     * Form tambah akun baru.
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Simpan akun baru ke database.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', \Illuminate\Validation\Rule::unique('users', 'email')->whereNull('deleted_at')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role'     => ['required', 'in:super_admin,kasir_paspor'],
        ], [
            'name.required'      => 'Nama wajib diisi.',
            'email.unique'       => 'Email sudah terdaftar, gunakan email lain.',
            'password.min'       => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        return redirect()
            ->route('admin.users.show', $user->id)
            ->with('success', "Akun \"{$user->name}\" berhasil dibuat.");
    }

    /**
     * Detail akun tertentu beserta statistik transaksinya.
     */
    public function show(User $user)
    {
        $user->load('transaksiPaspors');

        $stats = [
            'total_paspor' => $user->transaksiPaspors()->count(),
            'omset_paspor' => $user->transaksiPaspors()->sum('total_pembayaran_pemohon'),
        ];

        return view('admin.users.show', compact('user', 'stats'));
    }

    /**
     * Form edit akun.
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update data akun.
     */
    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role'  => ['required', 'in:super_admin,kasir_paspor'],
        ], [
            'email.unique' => 'Email sudah dipakai oleh akun lain.',
        ]);

        // Ganti password hanya jika diisi
        if ($request->filled('password')) {
            $request->validate([
                'password' => ['string', 'min:8', 'confirmed'],
            ], [
                'password.min'       => 'Password baru minimal 8 karakter.',
                'password.confirmed' => 'Konfirmasi password tidak cocok.',
            ]);
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()
            ->route('admin.users.show', $user->id)
            ->with('success', "Data akun \"{$user->name}\" berhasil diperbarui.");
    }

    /**
     * Hapus akun — tidak boleh menghapus akun diri sendiri.
     */
    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Kamu tidak bisa menghapus akun yang sedang login!');
        }

        $nama = $user->name;
        $user->forceDelete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Akun \"{$nama}\" berhasil dihapus.");
    }
}
