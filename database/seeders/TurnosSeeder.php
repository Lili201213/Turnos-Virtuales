<?php

namespace Database\Seeders;

use App\Models\Modulo;
use App\Models\Servicio;
use App\Models\User;
use Illuminate\Database\Seeder;

class TurnosSeeder extends Seeder
{
    public function run(): void
    {
        $caja  = Servicio::create(['nombre' => 'Caja',  'prefijo' => 'C']);
        $citas = Servicio::create(['nombre' => 'Citas', 'prefijo' => 'A']);

        Modulo::create(['nombre' => 'Módulo 1', 'servicio_id' => $citas->id]);
        Modulo::create(['nombre' => 'Módulo 2', 'servicio_id' => $citas->id]);
        Modulo::create(['nombre' => 'Módulo 3', 'servicio_id' => $caja->id]);
        Modulo::create(['nombre' => 'Módulo 4', 'servicio_id' => $caja->id]);

        User::factory()->create([
            'name' => 'Asesor Demo',
            'email' => 'asesor@demo.com',
            'password' => bcrypt('12345678'),
        ]);
    }
}
