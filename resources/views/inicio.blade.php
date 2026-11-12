<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Turnos Virtuales</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: linear-gradient(135deg, #e8f4fb, #fff);
               min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center;
               padding: 24px; color: #12344d; }
        h1 { font-size: 2.2rem; color: #0b84c6; text-align: center; }
        p.sub { color: #4f7088; margin: 8px 0 32px; text-align: center; max-width: 560px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 18px; width: 100%; max-width: 900px; }
        a.card { background: #fff; border-radius: 18px; padding: 28px 24px; text-decoration: none; color: inherit;
                 box-shadow: 0 8px 22px rgba(11,132,198,.15); border: 2px solid transparent; transition: .15s; }
        a.card:hover { border-color: #0b84c6; transform: translateY(-3px); }
        a.card h2 { color: #0b84c6; font-size: 1.4rem; margin-bottom: 6px; }
        a.card p { color: #4f7088; font-size: .95rem; }
        .nota { margin-top: 28px; font-size: .9rem; color: #4f7088; text-align: center; max-width: 640px; line-height: 1.5; }
    </style>
</head>
<body>
    <h1>Turnos Virtuales</h1>
    <p class="sub">Sistema web de turnos y gestión de colas en tiempo real</p>

    <div class="grid">
        <a class="card" href="{{ route('kiosco.index') }}" target="_blank">
            <h2>1. Kiosco</h2>
            <p>El ciudadano elige un servicio y toma su turno.</p>
        </a>
        <a class="card" href="{{ route('pantalla.index') }}" target="_blank">
            <h2>2. Pantalla</h2>
            <p>Muestra el turno llamado con voz. Presionar "Activar sonido".</p>
        </a>
        <a class="card" href="{{ route('login') }}" target="_blank">
            <h2>3. Asesor / Admin</h2>
            <p>Llama y atiende turnos, o consulta los reportes.</p>
        </a>
    </div>

    <div class="nota">
        <b>Usuarios de prueba</b> (clave: 12345678)<br>
        Asesor: asesor@demo.com &middot; Administrador: admin@demo.com<br><br>
        <b>Para probarlo:</b> abra la Pantalla, saque turnos en el Kiosco y llámelos desde el panel del asesor.
        La Pantalla cambia al instante, sin recargar.
    </div>
</body>
</html>
