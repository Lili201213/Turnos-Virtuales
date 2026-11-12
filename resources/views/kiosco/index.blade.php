<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Toma tu turno</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #e8f4fb, #ffffff);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px;
            color: #12344d;
        }
        h1 { font-size: 2.4rem; margin-bottom: 8px; text-align: center; }
        p.sub { font-size: 1.2rem; color: #4f7088; margin-bottom: 40px; text-align: center; }
        .botones { display: flex; flex-direction: column; gap: 24px; width: 100%; max-width: 560px; }
        .btn-servicio {
            background: #0b84c6;
            color: #fff;
            border: none;
            border-radius: 18px;
            padding: 44px 20px;
            font-size: 2.6rem;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 8px 20px rgba(11, 132, 198, .35);
            transition: transform .1s;
        }
        .btn-servicio:active { transform: scale(.97); }
        .btn-servicio:disabled { opacity: .6; }

        /* Ventana del ticket */
        .overlay {
            display: none;
            position: fixed; inset: 0;
            background: rgba(0, 0, 0, .55);
            align-items: center; justify-content: center;
            padding: 20px;
        }
        .overlay.activo { display: flex; }
        .ticket {
            background: #fff;
            border-radius: 20px;
            padding: 36px 30px;
            width: 100%; max-width: 420px;
            text-align: center;
        }
        .ticket .etiqueta { font-size: 1.1rem; color: #4f7088; }
        .ticket .codigo { font-size: 5.5rem; font-weight: 800; color: #0b84c6; line-height: 1.1; margin: 8px 0; }
        .ticket .servicio { font-size: 1.6rem; font-weight: 600; }
        .ticket .detalle { margin-top: 14px; font-size: 1.05rem; color: #4f7088; }
        .ticket .acciones { margin-top: 26px; display: flex; gap: 12px; justify-content: center; }
        .ticket button {
            padding: 12px 22px; font-size: 1rem; border-radius: 10px;
            border: none; cursor: pointer; font-weight: 600;
        }
        .btn-imprimir { background: #e8f4fb; color: #0b84c6; }
        .btn-cerrar { background: #0b84c6; color: #fff; }
        .cierre-auto { margin-top: 14px; font-size: .9rem; color: #8aa0b0; }

        @media print {
            body * { visibility: hidden; }
            .ticket, .ticket * { visibility: visible; }
            .ticket { position: absolute; left: 0; top: 0; width: 100%; box-shadow: none; }
            .acciones, .cierre-auto { display: none; }
        }
    </style>
</head>
<body>
    <h1>Bienvenido</h1>
    <p class="sub">Seleccione el servicio y tome su turno</p>

    <div class="botones">
        @foreach ($servicios as $servicio)
            <button class="btn-servicio" data-id="{{ $servicio->id }}">
                {{ $servicio->nombre }}
            </button>
        @endforeach
    </div>

    <div class="overlay" id="overlay">
        <div class="ticket">
            <div class="etiqueta">Su turno es</div>
            <div class="codigo" id="t-codigo">--</div>
            <div class="servicio" id="t-servicio"></div>
            <div class="detalle" id="t-detalle"></div>
            <div class="acciones">
                <button class="btn-imprimir" onclick="window.print()">Imprimir</button>
                <button class="btn-cerrar" onclick="cerrarTicket()">Aceptar</button>
            </div>
            <div class="cierre-auto">Esta ventana se cerrará sola en <span id="cuenta">10</span> s</div>
        </div>
    </div>

    <script>
        const token = document.querySelector('meta[name="csrf-token"]').content;
        const overlay = document.getElementById('overlay');
        let temporizador = null;

        document.querySelectorAll('.btn-servicio').forEach(btn => {
            btn.addEventListener('click', async () => {
                document.querySelectorAll('.btn-servicio').forEach(b => b.disabled = true);

                try {
                    const res = await fetch("{{ route('kiosco.store') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token
                        },
                        body: JSON.stringify({ servicio_id: btn.dataset.id })
                    });

                    if (!res.ok) throw new Error('Error al generar el turno');
                    mostrarTicket(await res.json());
                } catch (e) {
                    alert('No se pudo generar el turno. Intente nuevamente.');
                    document.querySelectorAll('.btn-servicio').forEach(b => b.disabled = false);
                }
            });
        });

        function mostrarTicket(t) {
            document.getElementById('t-codigo').textContent = t.codigo;
            document.getElementById('t-servicio').textContent = t.servicio;
            document.getElementById('t-detalle').innerHTML =
                `${t.fecha} - ${t.hora}<br>Personas delante de usted: <b>${t.en_espera}</b>`;
            overlay.classList.add('activo');

            let seg = 10;
            document.getElementById('cuenta').textContent = seg;
            temporizador = setInterval(() => {
                seg--;
                document.getElementById('cuenta').textContent = seg;
                if (seg <= 0) cerrarTicket();
            }, 1000);
        }

        function cerrarTicket() {
            clearInterval(temporizador);
            overlay.classList.remove('activo');
            document.querySelectorAll('.btn-servicio').forEach(b => b.disabled = false);
        }
    </script>
</body>
</html>
