<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'nombres_completos' => 'Administrador',
            'documento_identidad' => '1234567890',
            'correo' => 'admin@admin.com',
            'password' => Hash::make('admin123'),
            'rol' => 'admin',
            'qr_code' => null,
        ]);
    }
}