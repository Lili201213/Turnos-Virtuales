<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reportes - Turnos Virtuales</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f0f7fc; color: #12344d; }
        header { background: #0b84c6; color: #fff; padding: 16px 28px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; }
        header h1 { font-size: 1.4rem; }
        header small { display: block; opacity: .85; font-size: .9rem; }
        .nav { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
        .nav a, .nav button { background: rgba(255,255,255,.18); color: #fff; border: none; padding: 8px 14px; border-radius: 8px;
                              cursor: pointer; font-size: .9rem; text-decoration: none; }
        .nav form { display: inline; }
        main { max-width: 1100px; margin: 24px auto; padding: 0 20px 40px; }
        .filtros { background: #fff; border-radius: 14px; padding: 16px 20px; display: flex; gap: 14px; align-items: end; flex-wrap: wrap;
                   box-shadow: 0 4px 14px rgba(11,132,198,.08); margin-bottom: 22px; }
        .filtros label { font-size: .85rem; color: #4f7088; display: block; margin-bottom: 4px; }
        .filtros input { padding: 9px 10px; border: 1px solid #c9dbe6; border-radius: 8px; font-size: 1rem; }
        .btn { background: #0b84c6; color: #fff; border: none; padding: 10px 18px; border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn.sec { background: #e8f4fb; color: #0b84c6; }
        .kpis { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 14px; margin-bottom: 22px; }
        .kpi { background: #fff; border-radius: 14px; padding: 18px; box-shadow: 0 4px 14px rgba(11,132,198,.08); }
        .kpi b { display: block; font-size: 1.9rem; color: #0b84c6; }
        .kpi span { color: #4f7088; font-size: .9rem; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
        @media (max-width: 800px) { .grid { grid-template-columns: 1fr; } }
        .card { background: #fff; border-radius: 14px; padding: 20px; box-shadow: 0 4px 14px rgba(11,132,198,.08); }
        .card.ancho { grid-column: 1 / -1; }
        h2 { font-size: 1.05rem; margin-bottom: 14px; }
        table { width: 100%; border-collapse: collapse; font-size: .95rem; }
        th { text-align: left; color: #4f7088; font-weight: 600; padding: 8px 6px; border-bottom: 2px solid #e3eef5; }
        td { padding: 9px 6px; border-bottom: 1px solid #eef4f8; }
        .barra { display: flex; align-items: center; gap: 10px; margin: 6px 0; font-size: .9rem; }
        .barra .et { width: 52px; color: #4f7088; flex-shrink: 0; }
        .barra .rel { flex: 1; background: #e8f4fb; border-radius: 6px; height: 18px; overflow: hidden; }
        .barra .rel i { display: block; height: 100%; background: #0b84c6; border-radius: 6px; }
        .barra .val { width: 70px; text-align: right; font-weight: 600; }
        .vacio { color: #8aa0b0; text-align: center; padding: 14px; }
    </style>
</head>
<body>
    <header>
        <div>
            <h1>Reportes y estadísticas</h1>
            <small>{{ auth()->user()->name }} &middot; Administrador</small>
        </div>
        <div class="nav">
            <a href="{{ route('asesor.index') }}">Panel asesor</a>
            <a href="{{ route('pantalla.index') }}" target="_blank">Pantalla</a>
            <a href="{{ route('kiosco.index') }}" target="_blank">Kiosco</a>
            <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Salir</button></form>
        </div>
    </header>

    <main>
        <form class="filtros" method="GET" action="{{ route('admin.reportes') }}">
            <div><label for="desde">Desde</label><input type="date" id="desde" name="desde" value="{{ $desde }}"></div>
            <div><label for="hasta">Hasta</label><input type="date" id="hasta" name="hasta" value="{{ $hasta }}"></div>
            <button class="btn" type="submit">Filtrar</button>
            <a class="btn sec" href="{{ route('admin.reportes.csv', ['desde' => $desde, 'hasta' => $hasta]) }}">Exportar CSV</a>
        </form>

        <section class="kpis">
            <div class="kpi"><b>{{ $kpis['total'] }}</b><span>Turnos emitidos</span></div>
            <div class="kpi"><b>{{ $kpis['finalizados'] }}</b><span>Atendidos</span></div>
            <div class="kpi"><b>{{ $kpis['cancelados'] }}</b><span>No se presentaron</span></div>
            <div class="kpi"><b>{{ $kpis['en_espera'] }}</b><span>Sin atender</span></div>
            <div class="kpi"><b style="font-size:1.4rem">{{ $kpis['espera_prom'] }}</b><span>Espera promedio</span></div>
            <div class="kpi"><b style="font-size:1.4rem">{{ $kpis['atencion_prom'] }}</b><span>Atención promedio</span></div>
        </section>

        <section class="grid">
            <div class="card">
                <h2>Por servicio</h2>
                <table>
                    <thead><tr><th>Servicio</th><th>Total</th><th>Atendidos</th><th>Espera prom.</th></tr></thead>
                    <tbody>
                    @forelse ($porServicio as $s)
                        <tr><td>{{ $s['nombre'] }}</td><td>{{ $s['total'] }}</td><td>{{ $s['atendidos'] }}</td><td>{{ $s['espera_prom'] }}</td></tr>
                    @empty
                        <tr><td colspan="4" class="vacio">Sin datos en el rango</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="card">
                <h2>Por módulo</h2>
                <table>
                    <thead><tr><th>Módulo</th><th>Llamados</th><th>Atendidos</th><th>Atención prom.</th></tr></thead>
                    <tbody>
                    @forelse ($porModulo as $m)
                        <tr><td>{{ $m['nombre'] }}</td><td>{{ $m['total'] }}</td><td>{{ $m['atendidos'] }}</td><td>{{ $m['atencion_prom'] }}</td></tr>
                    @empty
                        <tr><td colspan="4" class="vacio">Sin datos en el rango</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="card">
                <h2>Turnos por día</h2>
                @php $maxDia = max(1, $porDia->max('total') ?? 1); @endphp
                @forelse ($porDia as $d)
                    <div class="barra">
                        <span class="et">{{ $d['etiqueta'] }}</span>
                        <span class="rel"><i style="width: {{ round($d['total'] / $maxDia * 100) }}%"></i></span>
                        <span class="val">{{ $d['total'] }}</span>
                    </div>
                @empty
                    <div class="vacio">Sin datos en el rango</div>
                @endforelse
            </div>

            <div class="card">
                <h2>Horas de mayor demanda</h2>
                @php $maxHora = max(1, $porHora->max('total') ?? 1); @endphp
                @forelse ($porHora as $h)
                    <div class="barra">
                        <span class="et">{{ $h['etiqueta'] }}</span>
                        <span class="rel"><i style="width: {{ round($h['total'] / $maxHora * 100) }}%"></i></span>
                        <span class="val">{{ $h['total'] }}</span>
                    </div>
                @empty
                    <div class="vacio">Sin datos en el rango</div>
                @endforelse
            </div>
        </section>
    </main>
</body>
</html>
