<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pantalla de turnos</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #eaf4fb; color: #12344d; overflow: hidden; }

        .tv { height: 100vh; display: grid; grid-template-columns: 34% 1fr; grid-template-rows: 1fr auto; }

        /* Columna izquierda: lista */
        .lista { background: #fff; padding: 3vh 2vw; border-right: 4px solid #0b84c6; }
        .logo { font-size: 3.2vh; font-weight: 800; color: #0b84c6; }
        .logo small { display: block; font-size: 1.8vh; font-weight: 400; color: #4f7088; margin-bottom: 3vh; }
        table { width: 100%; border-collapse: collapse; }
        thead th { background: #0b84c6; color: #fff; padding: 1.4vh 1vw; text-align: left; font-size: 2vh; }
        tbody td { padding: 2vh 1vw; border-bottom: 1px solid #dbe8f1; font-size: 2.6vh; }
        tbody td.turno { font-weight: 800; color: #0b84c6; font-size: 3vh; }
        tbody tr:first-child { background: #e8f4fb; }
        tbody tr.vacia td { color: #8aa0b0; text-align: center; font-size: 2.2vh; }

        /* Zona principal */
        .principal { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 3vh 3vw; gap: 4vh;
                     background: linear-gradient(135deg, #0b84c6, #1fa6e8); color: #fff; }
        .actual { text-align: center; }
        .actual .tit { font-size: 4vh; letter-spacing: .4vh; opacity: .9; }
        .actual .codigo { font-size: 24vh; font-weight: 900; line-height: 1; margin: 1vh 0; }
        .actual .modulo { font-size: 6vh; font-weight: 700; text-transform: uppercase; }
        .actual .servicio { font-size: 3vh; opacity: .9; margin-top: 1vh; }
        .actual.destacar { animation: pulso 1.2s ease-in-out 3; }
        @keyframes pulso { 0%,100% { transform: scale(1); } 50% { transform: scale(1.07); } }

        .espera { display: flex; gap: 2vw; }
        .espera .item { background: rgba(255,255,255,.18); border-radius: 2vh; padding: 2vh 2.5vw; text-align: center; min-width: 16vw; }
        .espera .item b { display: block; font-size: 6vh; }
        .espera .item span { font-size: 2.2vh; }

        /* Barra inferior */
        footer { grid-column: 1 / -1; background: #12344d; color: #fff; display: flex; justify-content: space-between; align-items: center;
                 padding: 1.6vh 2vw; font-size: 2.4vh; gap: 2vw; }
        #reloj { font-weight: 700; white-space: nowrap; }
        .vivo { display: flex; align-items: center; gap: .6vw; font-size: 1.8vh; opacity: .9; white-space: nowrap; }
        .punto { width: 1.4vh; height: 1.4vh; border-radius: 50%; background: #f5a623; }
        .punto.on { background: #3ddc84; }
        .punto.off { background: #ff5d5d; }

        /* Activar sonido */
        #activar { position: fixed; inset: 0; background: rgba(18,52,77,.92); color: #fff; display: flex; flex-direction: column;
                   align-items: center; justify-content: center; gap: 3vh; z-index: 10; text-align: center; }
        #activar h2 { font-size: 5vh; }
        #activar p { font-size: 2.6vh; opacity: .85; }
        #activar button { background: #0b84c6; color: #fff; border: none; border-radius: 2vh; padding: 3vh 5vw; font-size: 3.4vh; font-weight: 700; cursor: pointer; }
    </style>
</head>
<body>
    <div id="activar">
        <h2>Pantalla de turnos</h2>
        <p>Para escuchar los anuncios de voz, active el sonido.</p>
        <button id="btn-activar">Activar sonido y comenzar</button>
    </div>

    <div class="tv">
        <aside class="lista">
            <div class="logo">Turnos Virtuales<small>Contigo, con todo</small></div>
            <table>
                <thead><tr><th>Nombre</th><th>Turno</th><th>Módulo</th></tr></thead>
                <tbody id="lista"></tbody>
            </table>
        </aside>

        <main class="principal">
            <div class="actual" id="actual">
                <div class="tit">TURNO</div>
                <div class="codigo" id="codigo">--</div>
                <div class="modulo" id="modulo">&nbsp;</div>
                <div class="servicio" id="servicio">&nbsp;</div>
            </div>
            <div class="espera" id="espera"></div>
        </main>

        <footer>
            <div>Bienvenidos, por favor espere su turno.</div>
            <div class="vivo"><span class="punto" id="punto"></span><span id="txt-vivo">Conectando...</span></div>
            <div id="reloj"></div>
        </footer>
    </div>

    @include('partials.realtime')

    <script>
        const datosUrl = @json(route('pantalla.datos'));
        const $ = id => document.getElementById(id);

        let ultimaClave = null;
        let cargado = false;
        let sonidoActivo = false;
        let audioCtx = null;
        let vozEspanol = null;

        /* ---------- Voz ---------- */
        function elegirVoz() {
            const voces = window.speechSynthesis ? speechSynthesis.getVoices() : [];
            const prefer = ['es-PE', 'es-MX', 'es-US', 'es-419', 'es-ES'];
            for (const p of prefer) {
                const v = voces.find(v => v.lang && v.lang.replace('_', '-').toLowerCase() === p.toLowerCase());
                if (v) { vozEspanol = v; return; }
            }
            vozEspanol = voces.find(v => v.lang && v.lang.toLowerCase().startsWith('es')) || null;
        }
        if (window.speechSynthesis) {
            elegirVoz();
            speechSynthesis.addEventListener('voiceschanged', elegirVoz);
        }

        function hablar(texto) {
            if (!window.speechSynthesis) return;
            const u = new SpeechSynthesisUtterance(texto);
            u.lang = vozEspanol ? vozEspanol.lang : 'es-ES';
            if (vozEspanol) u.voice = vozEspanol;
            u.rate = 0.9;
            u.volume = 1;
            speechSynthesis.speak(u);
        }

        function pitido() {
            try {
                if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                const o = audioCtx.createOscillator();
                const g = audioCtx.createGain();
                o.connect(g); g.connect(audioCtx.destination);
                o.frequency.value = 880;
                g.gain.setValueAtTime(0.25, audioCtx.currentTime);
                g.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.6);
                o.start(); o.stop(audioCtx.currentTime + 0.6);
            } catch (e) { /* sin audio */ }
        }

        function anunciar(t) {
            if (!sonidoActivo) return;
            pitido();
            setTimeout(() => hablar(`Turno ${t.prefijo} ${t.numero}, diríjase al ${t.modulo}`), 700);
        }

        $('btn-activar').addEventListener('click', () => {
            sonidoActivo = true;
            $('activar').style.display = 'none';
            pitido();
            hablar('Sonido activado');
            const el = document.documentElement;
            if (el.requestFullscreen) el.requestFullscreen().catch(() => {});
        });

        /* ---------- Pintar datos ---------- */
        function pintar(d) {
            const a = d.actual;

            if (a) {
                $('codigo').textContent = a.codigo;
                $('modulo').textContent = a.modulo;
                $('servicio').textContent = a.servicio;

                const clave = a.id + '-' + a.llamado_at;
                if (clave !== ultimaClave) {
                    if (cargado) {            // no anunciar al abrir la pantalla
                        anunciar(a);
                        const caja = $('actual');
                        caja.classList.remove('destacar');
                        void caja.offsetWidth; // reinicia la animación
                        caja.classList.add('destacar');
                    }
                    ultimaClave = clave;
                }
            }
            cargado = true;

            $('lista').innerHTML = d.lista.length
                ? d.lista.map(t => `<tr><td>${t.nombre}</td><td class="turno">${t.codigo}</td><td>${(t.modulo.match(/\d+/) || [t.modulo])[0]}</td></tr>`).join('')
                : '<tr class="vacia"><td colspan="3">Aún no hay turnos llamados</td></tr>';

            $('espera').innerHTML = d.espera
                .map(e => `<div class="item"><b>${e.cantidad}</b><span>En espera: ${e.servicio}</span></div>`).join('');
        }

        async function cargar() {
            try {
                const r = await fetch(datosUrl, { headers: { 'Accept': 'application/json' } });
                if (r.ok) pintar(await r.json());
            } catch (e) { /* reintenta en el siguiente ciclo */ }
        }

        /* ---------- Reloj ---------- */
        function reloj() {
            $('reloj').textContent = new Date().toLocaleString('es-PE', {
                weekday: 'long', day: '2-digit', month: 'long', hour: '2-digit', minute: '2-digit'
            });
        }
        reloj(); setInterval(reloj, 15000);

        /* ---------- Tiempo real ---------- */
        conectarTiempoReal({
            'turno.llamado': cargar,
            'cola.actualizada': cargar,
        }, estado => {
            const punto = $('punto'), txt = $('txt-vivo');
            punto.className = 'punto ' + (estado === 'connected' ? 'on' : (estado === 'connecting' ? '' : 'off'));
            txt.textContent = estado === 'connected' ? 'En vivo' : (estado === 'connecting' ? 'Conectando...' : 'Sin conexión en vivo');
        });

        cargar();
        setInterval(cargar, 5000); // respaldo por si cae el WebSocket
    </script>
</body>
</html>
