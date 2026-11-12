<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Servicio extends Model
{
    protected $fillable = ['nombre', 'prefijo', 'activo'];

    public function modulos() { return $this->hasMany(Modulo::class); }
    public function turnos()  { return $this->hasMany(Turno::class); }
}
