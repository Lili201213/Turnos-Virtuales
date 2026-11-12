<?php

namespace App\Http\Controllers;

use App\Models\Servicio;
use App\Models\Turno;

class PantallaController extends Controller
{
    public function index()
    {
        return view('pantalla.index');
    }

    public function datos()
    {
        $hoy = now()->toDateString();

        $llamados = Turno::with(['servicio', 'modulo'])
            ->whereDate('fecha', $hoy)
            ->whereNotNull('llamado_at')
            ->whereNotNull('modulo_id')
            ->orderByDesc('llamado_at')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        $espera = Servicio::where('activo', true)->orderBy('id')->get()->map(fn ($s) => [
            'servicio' => $s->nombre,
            'cantidad' => Turno::where('servicio_id', $s->id)
                ->whereDate('fecha', $hoy)
                ->where('estado', 'espera')
                ->count(),
        ]);

        return response()->json([
            'actual' => $llamados->isNotEmpty() ? $this->formatear($llamados->first()) : null,
            'lista'  => $llamados->map(fn ($t) => $this->formatear($t))->values(),
            'espera' => $espera,
        ]);
    }

    private function formatear(Turno $t): array
    {
        return [
            'id'         => $t->id,
            'codigo'     => $t->codigo,
            'prefijo'    => $t->servicio->prefijo,
            'numero'     => $t->numero,
            'servicio'   => $t->servicio->nombre,
            'nombre'     => $t->nombre_ciudadano ?: $t->servicio->nombre,
            'modulo'     => $t->modulo->nombre,
            'llamado_at' => $t->llamado_at->timestamp,
        ];
    }
}
