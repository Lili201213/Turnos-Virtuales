<?php

namespace App\Http\Controllers;

use App\Models\Turno;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReporteController extends Controller
{
    public function index(Request $request)
    {
        [$desde, $hasta] = $this->rango($request);
        $base = Turno::query()->whereBetween('turnos.fecha', [$desde, $hasta]);

        $t = (clone $base)->toBase()->selectRaw("
            COUNT(*) AS total,
            COALESCE(SUM(turnos.estado = 'finalizado'), 0) AS finalizados,
            COALESCE(SUM(turnos.estado = 'cancelado'), 0)  AS cancelados,
            COALESCE(SUM(turnos.estado = 'espera'), 0)     AS en_espera,
            AVG(CASE WHEN turnos.llamado_at IS NOT NULL
                THEN TIMESTAMPDIFF(SECOND, turnos.created_at, turnos.llamado_at) END) AS espera_prom,
            AVG(CASE WHEN turnos.estado = 'finalizado' AND turnos.llamado_at IS NOT NULL AND turnos.finalizado_at IS NOT NULL
                THEN TIMESTAMPDIFF(SECOND, turnos.llamado_at, turnos.finalizado_at) END) AS atencion_prom
        ")->first();

        $kpis = [
            'total'       => (int) $t->total,
            'finalizados' => (int) $t->finalizados,
            'cancelados'  => (int) $t->cancelados,
            'en_espera'   => (int) $t->en_espera,
            'espera_prom' => $this->fmt($t->espera_prom),
            'atencion_prom' => $this->fmt($t->atencion_prom),
        ];

        $porDia = (clone $base)->toBase()
            ->selectRaw("turnos.fecha, COUNT(*) AS total, COALESCE(SUM(turnos.estado = 'finalizado'), 0) AS atendidos")
            ->groupBy('turnos.fecha')->orderBy('turnos.fecha')->get()
            ->map(fn ($r) => [
                'etiqueta' => Carbon::parse($r->fecha)->format('d/m'),
                'total'    => (int) $r->total,
                'atendidos' => (int) $r->atendidos,
            ]);

        $porServicio = (clone $base)->toBase()
            ->join('servicios', 'servicios.id', '=', 'turnos.servicio_id')
            ->selectRaw("servicios.nombre,
                COUNT(*) AS total,
                COALESCE(SUM(turnos.estado = 'finalizado'), 0) AS atendidos,
                COALESCE(SUM(turnos.estado = 'cancelado'), 0) AS cancelados,
                AVG(CASE WHEN turnos.llamado_at IS NOT NULL
                    THEN TIMESTAMPDIFF(SECOND, turnos.created_at, turnos.llamado_at) END) AS espera_prom")
            ->groupBy('servicios.id', 'servicios.nombre')->get()
            ->map(fn ($r) => [
                'nombre' => $r->nombre,
                'total' => (int) $r->total,
                'atendidos' => (int) $r->atendidos,
                'cancelados' => (int) $r->cancelados,
                'espera_prom' => $this->fmt($r->espera_prom),
            ]);

        $porModulo = (clone $base)->toBase()
            ->join('modulos', 'modulos.id', '=', 'turnos.modulo_id')
            ->selectRaw("modulos.nombre,
                COUNT(*) AS total,
                COALESCE(SUM(turnos.estado = 'finalizado'), 0) AS atendidos,
                AVG(CASE WHEN turnos.estado = 'finalizado' AND turnos.llamado_at IS NOT NULL AND turnos.finalizado_at IS NOT NULL
                    THEN TIMESTAMPDIFF(SECOND, turnos.llamado_at, turnos.finalizado_at) END) AS atencion_prom")
            ->groupBy('modulos.id', 'modulos.nombre')->get()
            ->map(fn ($r) => [
                'nombre' => $r->nombre,
                'total' => (int) $r->total,
                'atendidos' => (int) $r->atendidos,
                'atencion_prom' => $this->fmt($r->atencion_prom),
            ]);

        $porHora = (clone $base)->toBase()
            ->selectRaw('HOUR(turnos.created_at) AS hora, COUNT(*) AS total')
            ->groupBy('hora')->orderBy('hora')->get()
            ->map(fn ($r) => [
                'etiqueta' => str_pad($r->hora, 2, '0', STR_PAD_LEFT) . ':00',
                'total'    => (int) $r->total,
            ]);

        return view('admin.reportes', compact('desde', 'hasta', 'kpis', 'porDia', 'porServicio', 'porModulo', 'porHora'));
    }

    public function csv(Request $request)
    {
        [$desde, $hasta] = $this->rango($request);

        $turnos = Turno::with(['servicio', 'modulo'])
            ->whereBetween('fecha', [$desde, $hasta])
            ->orderBy('fecha')->orderBy('id');

        $nombre = "turnos_{$desde}_a_{$hasta}.csv";

        return response()->streamDownload(function () use ($turnos) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM para que Excel lea bien las tildes
            fputcsv($out, ['Fecha', 'Turno', 'Servicio', 'Modulo', 'Estado', 'Creado', 'Llamado', 'Finalizado', 'Espera (seg)', 'Atencion (seg)']);

            $turnos->chunkById(500, function ($lote) use ($out) {
                foreach ($lote as $t) {
                    fputcsv($out, [
                        $t->fecha->format('Y-m-d'),
                        $t->codigo,
                        $t->servicio->nombre,
                        $t->modulo?->nombre,
                        $t->estado,
                        $t->created_at,
                        $t->llamado_at,
                        $t->finalizado_at,
                        $t->llamado_at ? (int) round($t->llamado_at->diffInSeconds($t->created_at, true)) : '',
                        ($t->llamado_at && $t->finalizado_at) ? (int) round($t->finalizado_at->diffInSeconds($t->llamado_at, true)) : '',
                    ]);
                }
            });

            fclose($out);
        }, $nombre, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function rango(Request $request): array
    {
        $request->validate(['desde' => 'nullable|date', 'hasta' => 'nullable|date']);

        $hasta = $request->input('hasta') ?: now()->toDateString();
        $desde = $request->input('desde') ?: now()->subDays(6)->toDateString();

        if ($desde > $hasta) {
            [$desde, $hasta] = [$hasta, $desde];
        }

        return [$desde, $hasta];
    }

    private function fmt($segundos): string
    {
        if ($segundos === null) {
            return '—';
        }

        $s = (int) round($segundos);

        return intdiv($s, 60) . ' min ' . str_pad($s % 60, 2, '0', STR_PAD_LEFT) . ' s';
    }
}
