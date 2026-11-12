<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Turno extends Model
{
    protected $fillable = [
        'servicio_id', 'modulo_id', 'numero', 'codigo', 'nombre_ciudadano',
        'documento', 'estado', 'fecha', 'llamado_at', 'finalizado_at',
    ];

    protected $casts = [
        'fecha' => 'date',
        'llamado_at' => 'datetime',
        'finalizado_at' => 'datetime',
    ];

    public function servicio() { return $this->belongsTo(Servicio::class); }
    public function modulo()   { return $this->belongsTo(Modulo::class); }
}
