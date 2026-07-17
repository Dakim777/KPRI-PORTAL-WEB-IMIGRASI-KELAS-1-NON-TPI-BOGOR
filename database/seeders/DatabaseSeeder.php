<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name'     => 'Super Admin',
            'email'    => 'admin@koperasi.com',
            'password' => Hash::make('password'),
            'role'     => 'super_admin',
        ]);

        User::create([
            'name'     => 'Kasir Kantin',
            'email'    => 'kantin@koperasi.com',
            'password' => Hash::make('password'),
            'role'     => 'kasir_kantin',
        ]);

        User::create([
            'name'     => 'Kasir Paspor',
            'email'    => 'paspor@koperasi.com',
            'password' => Hash::make('password'),
            'role'     => 'kasir_paspor',
        ]);
    }
}
