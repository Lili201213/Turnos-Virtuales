<?php

namespace App\Http\Controllers;

use App\Events\ColaActualizada;
use App\Events\TurnoLlamado;
use App\Models\Modulo;
use App\Models\Turno;
use App\Support\Difusion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AsesorController extends Controller
{
    /* ---------- Selección de módulo ---------- */

    public function index()
    {
        $modulo = $this->moduloActual();

        if (! $modulo) {
            $modulos = Modulo::with('servicio')->where('activo', true)->orderBy('id')->get();

            return view('asesor.modulos', compact('modulos'));
        }

        return view('asesor.panel', compact('modulo'));
    }

    public function seleccionar(Request $request)
    {
        $data = $request->validate(['modulo_id' => 'required|exists:modulos,id']);

        session(['modulo_id' => $data['modulo_id']]);

        return redirect()->route('asesor.index');
    }

    public function cambiarModulo()
    {
        session()->forget('modulo_id');

        return redirect()->route('asesor.index');
    }

    /* ---------- Estado (JSON) ---------- */

    public function estado()
    {
        return response()->json($this->estadoJson($this->moduloOFalla()));
    }

    /* ---------- Acciones del asesor ---------- */

    public function llamar()
    {
        $modulo = $this->moduloOFalla();
        $hoy = now()->toDateString();

        $resultado = DB::transaction(function () use ($modulo, $hoy) {
            $pendiente = Turno::where('modulo_id', $modulo->id)
                ->whereDate('fecha', $hoy)
                ->whereIn('estado', ['llamado', 'atendiendo'])
                ->lockForUpdate()
                ->first();

            if ($pendiente) {
                return ['error' => 'Finalice el turno actual antes de llamar al siguiente.'];
            }

            $siguiente = Turno::where('servicio_id', $modulo->servicio_id)
                ->whereDate('fecha', $hoy)
                ->where('estado', 'espera')
                ->orderBy('id')
                ->lockForUpdate()
                ->first();

            if (! $siguiente) {
                return ['error' => 'No hay turnos en espera.'];
            }

            $siguiente->update([
                'estado'     => 'llamado',
                'modulo_id'  => $modulo->id,
                'llamado_at' => now(),
            ]);

            return ['turno' => $siguiente];
        });

        if (isset($resultado['error'])) {
            return response()->json(['error' => $resultado['error']], 422);
        }

        Difusion::emitir(new TurnoLlamado($resultado['turno']));
        Difusion::emitir(new ColaActualizada($modulo->servicio_id));

        return $this->respuesta($modulo);
    }

    public function rellamar()
    {
        $modulo = $this->moduloOFalla();
        $turno = $this->turnoActivo($modulo, ['llamado']);

        if (! $turno) {
            return response()->json(['error' => 'No hay un turno llamado para repetir.'], 422);
        }

        // Se actualiza llamado_at para que la pantalla lo detecte como una llamada nueva
        $turno->update(['llamado_at' => now()]);

        Difusion::emitir(new TurnoLlamado($turno));

        return $this->respuesta($modulo);
    }

    public function atender()
    {
        return $this->cambiarEstado(['llamado'], 'atendiendo', 'No hay un turno llamado para atender.');
    }

    public function finalizar()
    {
        return $this->cambiarEstado(['atendiendo'], 'finalizado', 'No hay un turno en atención.', true);
    }

    public function ausente()
    {
        return $this->cambiarEstado(['llamado'], 'cancelado', 'No hay un turno llamado.', true);
    }

    /* ---------- Auxiliares ---------- */

    private function cambiarEstado(array $desde, string $hacia, string $mensajeError, bool $cierra = false)
    {
        $modulo = $this->moduloOFalla();
        $turno = $this->turnoActivo($modulo, $desde);

        if (! $turno) {
            return response()->json(['error' => $mensajeError], 422);
        }

        $campos = ['estado' => $hacia];
        if ($cierra) {
            $campos['finalizado_at'] = now();
        }
        $turno->update($campos);

        Difusion::emitir(new ColaActualizada($modulo->servicio_id));

        return $this->respuesta($modulo);
    }

    private function turnoActivo(Modulo $modulo, array $estados): ?Turno
    {
        return Turno::where('modulo_id', $modulo->id)
            ->whereDate('fecha', now()->toDateString())
            ->whereIn('estado', $estados)
            ->latest('llamado_at')
            ->first();
    }

    private function moduloActual(): ?Modulo
    {
        $id = session('modulo_id');

        return $id ? Modulo::with('servicio')->where('activo', true)->find($id) : null;
    }

    private function moduloOFalla(): Modulo
    {
        $modulo = $this->moduloActual();

        if (! $modulo) {
            abort(409, 'Seleccione un módulo de atención.');
        }

        return $modulo;
    }

    private function respuesta(Modulo $modulo)
    {
        return response()->json(['ok' => true, 'estado' => $this->estadoJson($modulo)]);
    }

    private function estadoJson(Modulo $modulo): array
    {
        $hoy = now()->toDateString();

        $actual = $this->turnoActivo($modulo, ['llamado', 'atendiendo']);

        $cola = Turno::where('servicio_id', $modulo->servicio_id)
            ->whereDate('fecha', $hoy)
            ->where('estado', 'espera')
            ->orderBy('id')
            ->get();

        $atendidos = Turno::where('modulo_id', $modulo->id)
            ->whereDate('fecha', $hoy)
            ->where('estado', 'finalizado')
            ->count();

        return [
            'actual' => $actual ? [
                'id'     => $actual->id,
                'codigo' => $actual->codigo,
                'estado' => $actual->estado,
            ] : null,
            'en_espera' => $cola->count(),
            'atendidos' => $atendidos,
            'cola'      => $cola->take(12)->map(fn ($t) => [
                'codigo' => $t->codigo,
                'hora'   => $t->created_at->format('H:i'),
            ])->values(),
        ];
    }
}
