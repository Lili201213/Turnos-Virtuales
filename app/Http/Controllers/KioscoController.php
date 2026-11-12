<?php

namespace App\Http\Controllers;

use App\Events\ColaActualizada;
use App\Models\Servicio;
use App\Models\Turno;
use App\Support\Difusion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KioscoController extends Controller
{
    public function index()
    {
        $servicios = Servicio::where('activo', true)->orderBy('id')->get();

        return view('kiosco.index', compact('servicios'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'servicio_id'      => 'required|exists:servicios,id',
            'nombre_ciudadano' => 'nullable|string|max:100',
            'documento'        => 'nullable|string|max:20',
        ]);

        $turno = DB::transaction(function () use ($data) {
            $servicio = Servicio::lockForUpdate()->findOrFail($data['servicio_id']);

            $hoy = now()->toDateString();

            $ultimo = Turno::where('servicio_id', $servicio->id)
                ->whereDate('fecha', $hoy)
                ->max('numero');

            $numero = ($ultimo ?? 0) + 1;

            return Turno::create([
                'servicio_id'      => $servicio->id,
                'numero'           => $numero,
                'codigo'           => $servicio->prefijo . $numero,
                'nombre_ciudadano' => $data['nombre_ciudadano'] ?? null,
                'documento'        => $data['documento'] ?? null,
                'estado'           => 'espera',
                'fecha'            => $hoy,
            ]);
        });

        $enEspera = Turno::where('servicio_id', $turno->servicio_id)
            ->whereDate('fecha', $turno->fecha)
            ->where('estado', 'espera')
            ->where('id', '<', $turno->id)
            ->count();

        // Avisa en tiempo real a los paneles y a la pantalla
        Difusion::emitir(new ColaActualizada($turno->servicio_id));

        return response()->json([
            'codigo'    => $turno->codigo,
            'servicio'  => $turno->servicio->nombre,
            'hora'      => $turno->created_at->format('H:i'),
            'fecha'     => $turno->created_at->format('d/m/Y'),
            'en_espera' => $enEspera,
        ]);
    }
}
