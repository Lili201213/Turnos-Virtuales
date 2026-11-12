<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Seleccionar módulo</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f0f7fc; min-height: 100vh; padding: 30px 20px; color: #12344d; }
        .wrap { max-width: 640px; margin: 0 auto; }
        h1 { color: #0b84c6; margin-bottom: 6px; }
        p.sub { color: #4f7088; margin-bottom: 24px; }
        .grid { display: grid; gap: 14px; }
        .mod { width: 100%; text-align: left; background: #fff; border: 2px solid #d6e8f3; border-radius: 14px;
               padding: 20px 22px; font-size: 1.2rem; cursor: pointer; display: flex; justify-content: space-between; align-items: center; }
        .mod:hover { border-color: #0b84c6; }
        .mod small { color: #4f7088; font-size: .95rem; }
        .salir { margin-top: 24px; }
        .salir button { background: none; border: none; color: #0b84c6; cursor: pointer; font-size: 1rem; text-decoration: underline; }
    </style>
</head>
<body>
    <div class="wrap">
        <h1>Hola, {{ auth()->user()->name }}</h1>
        <p class="sub">Seleccione el módulo desde el que va a atender</p>

        <form method="POST" action="{{ route('asesor.seleccionar') }}" class="grid">
            @csrf
            @foreach ($modulos as $m)
                <button class="mod" type="submit" name="modulo_id" value="{{ $m->id }}">
                    <span>{{ $m->nombre }}</span>
                    <small>{{ $m->servicio->nombre }}</small>
                </button>
            @endforeach
        </form>

        <form class="salir" method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit">Cerrar sesión</button>
        </form>
    </div>
</body>
</html>
