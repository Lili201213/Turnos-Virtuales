<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Panel del asesor - {{ $modulo->nombre }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f0f7fc; color: #12344d; min-height: 100vh; }
        header { background: #0b84c6; color: #fff; padding: 16px 28px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; }
        header h1 { font-size: 1.4rem; }
        header small { opacity: .85; display: block; font-size: .9rem; }
        .acciones-header { display: flex; gap: 10px; align-items: center; }
        .acciones-header form { display: inline; }
        .link { background: rgba(255,255,255,.18); color: #fff; border: none; padding: 8px 14px; border-radius: 8px; cursor: pointer; font-size: .9rem; }
        .en-vivo { font-size: .85rem; display: flex; align-items: center; gap: 6px; }
        .punto { width: 10px; height: 10px; border-radius: 50%; background: #f5a623; }
        .punto.on { background: #3ddc84; }
        .punto.off { background: #ff5d5d; }

        main { max-width: 1000px; margin: 24px auto; padding: 0 20px; display: grid; grid-template-columns: 1.4fr 1fr; gap: 22px; }
        @media (max-width: 800px) { main { grid-template-columns: 1fr; } }
        .card { background: #fff; border-radius: 18px; padding: 26px; box-shadow: 0 6px 18px rgba(11,132,198,.10); }
        .actual { text-align: center; }
        .actual .etiqueta { color: #4f7088; font-size: 1rem; }
        .actual .codigo { font-size: 6rem; font-weight: 800; color: #0b84c6; line-height: 1.1; }
        .actual .estado { font-size: 1.1rem; color: #4f7088; margin-bottom: 22px; min-height: 1.4em; }
        .botones { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .botones button { padding: 16px 10px; border: none; border-radius: 12px; font-size: 1.05rem; font-weight: 700; cursor: pointer; color: #fff; }
        .botones button:disabled { opacity: .35; cursor: not-allowed; }
        .b-llamar { background: #0b84c6; grid-column: 1 / -1; font-size: 1.3rem !important; padding: 20px !important; }
        .b-rellamar { background: #6c7ae0; }
        .b-atender { background: #1a9d6c; }
        .b-finalizar { background: #0f6b4a; }
        .b-ausente { background: #d9534f; }
        .stats { display: flex; gap: 14px; margin-bottom: 18px; }
        .stat { flex: 1; background: #e8f4fb; border-radius: 12px; padding: 14px; text-align: center; }
        .stat b { display: block; font-size: 2rem; color: #0b84c6; }
        .stat span { font-size: .85rem; color: #4f7088; }
        h2 { font-size: 1.1rem; margin-bottom: 10px; }
        ul { list-style: none; max-height: 380px; overflow: auto; }
        li { display: flex; justify-content: space-between; padding: 10px 6px; border-bottom: 1px solid #e3eef5; font-size: 1.05rem; }
        li b { color: #0b84c6; }
        li span { color: #8aa0b0; }
        li.vacio { color: #8aa0b0; justify-content: center; }
    </style>
</head>
<body>
    <header>
        <div>
            <h1>{{ $modulo->nombre }}</h1>
            <small>Servicio: {{ $modulo->servicio->nombre }} &middot; {{ auth()->user()->name }}</small>
        </div>
        <div class="acciones-header">
            <div class="en-vivo"><span class="punto" id="punto"></span><span id="txt-vivo">Conectando...</span></div>
            <form method="POST" action="{{ route('asesor.cambiar') }}">@csrf<button class="link" type="submit">Cambiar módulo</button></form>
            <form method="POST" action="{{ route('logout') }}">@csrf<button class="link" type="submit">Salir</button></form>
        </div>
    </header>

    <main>
        <section class="card actual">
            <div class="etiqueta">Turno actual</div>
            <div class="codigo" id="codigo">--</div>
            <div class="estado" id="estado">Sin turno activo</div>

            <div class="botones">
                <button class="b-llamar" id="btn-llamar" data-url="{{ route('asesor.llamar') }}">Llamar siguiente</button>
                <button class="b-rellamar" id="btn-rellamar" data-url="{{ route('asesor.rellamar') }}">Volver a llamar</button>
                <button class="b-atender" id="btn-atender" data-url="{{ route('asesor.atender') }}">Iniciar atención</button>
                <button class="b-finalizar" id="btn-finalizar" data-url="{{ route('asesor.finalizar') }}">Finalizar</button>
                <button class="b-ausente" id="btn-ausente" data-url="{{ route('asesor.ausente') }}">No se presentó</button>
            </div>
        </section>

        <section class="card">
            <div class="stats">
                <div class="stat"><b id="n-espera">0</b><span>En espera</span></div>
                <div class="stat"><b id="n-atendidos">0</b><span>Atendidos hoy</span></div>
            </div>
            <h2>Cola de {{ $modulo->servicio->nombre }}</h2>
            <ul id="cola"></ul>
        </section>
    </main>

    @include('partials.realtime')

    <script>
        const token = document.querySelector('meta[name="csrf-token"]').content;
        const estadoUrl = @json(route('asesor.estado'));
        const $ = id => document.getElementById(id);
        let ocupado = false;

        function render(d) {
            const a = d.actual;
            $('codigo').textContent = a ? a.codigo : '--';
            $('estado').textContent = a
                ? (a.estado === 'llamado' ? 'Llamado: esperando al ciudadano' : 'En atención')
                : 'Sin turno activo';

            $('btn-llamar').disabled    = !!a || d.en_espera === 0;
            $('btn-rellamar').disabled  = !a || a.estado !== 'llamado';
            $('btn-atender').disabled   = !a || a.estado !== 'llamado';
            $('btn-ausente').disabled   = !a || a.estado !== 'llamado';
            $('btn-finalizar').disabled = !a || a.estado !== 'atendiendo';

            $('n-espera').textContent = d.en_espera;
            $('n-atendidos').textContent = d.atendidos;
            $('cola').innerHTML = d.cola.length
                ? d.cola.map(t => `<li><b>${t.codigo}</b><span>${t.hora}</span></li>`).join('')
                : '<li class="vacio">No hay turnos en espera</li>';
        }

        async function cargar() {
            try {
                const r = await fetch(estadoUrl, { headers: { 'Accept': 'application/json' } });
                if (r.ok) render(await r.json());
            } catch (e) { /* sin red momentánea */ }
        }

        async function accion(url) {
            if (ocupado) return;
            ocupado = true;
            try {
                const r = await fetch(url, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': token }
                });
                const d = await r.json().catch(() => ({}));
                if (!r.ok) alert(d.error || d.message || 'Ocurrió un error.');
                if (d.estado) render(d.estado); else await cargar();
            } finally {
                ocupado = false;
            }
        }

        document.querySelectorAll('.botones button').forEach(b =>
            b.addEventListener('click', () => accion(b.dataset.url)));

        // Tiempo real: cualquier cambio en la cola refresca el panel
        conectarTiempoReal({
            'cola.actualizada': cargar,
            'turno.llamado': cargar,
        }, estado => {
            const punto = $('punto'), txt = $('txt-vivo');
            punto.className = 'punto ' + (estado === 'connected' ? 'on' : (estado === 'connecting' ? '' : 'off'));
            txt.textContent = estado === 'connected' ? 'En vivo' : (estado === 'connecting' ? 'Conectando...' : 'Sin conexión en vivo');
        });

        cargar();
        setInterval(cargar, 15000); // respaldo por si cae el WebSocket
    </script>
</body>
</html>
