<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Modulo extends Model
{
    protected $fillable = ['nombre', 'servicio_id', 'user_id', 'activo'];

    public function servicio() { return $this->belongsTo(Servicio::class); }
    public function user()     { return $this->belongsTo(User::class); }
    public function turnos()   { return $this->hasMany(Turno::class); }
}
