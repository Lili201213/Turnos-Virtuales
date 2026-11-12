<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@demo.com'],
            ['name' => 'Administrador', 'password' => bcrypt('12345678')]
        );
        $admin->forceFill(['rol' => 'admin'])->save();

        User::where('email', 'asesor@demo.com')->update(['rol' => 'asesor']);
    }
}
