<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ingreso de asesores</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: linear-gradient(135deg, #e8f4fb, #fff);
               min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; color: #12344d; }
        .card { background: #fff; border-radius: 18px; padding: 36px 32px; width: 100%; max-width: 400px;
                box-shadow: 0 10px 30px rgba(11,132,198,.18); }
        h1 { font-size: 1.6rem; color: #0b84c6; margin-bottom: 4px; }
        p.sub { color: #4f7088; margin-bottom: 24px; }
        label { display: block; font-weight: 600; margin: 14px 0 6px; }
        input[type=email], input[type=password] { width: 100%; padding: 12px; border: 1px solid #c9dbe6; border-radius: 10px; font-size: 1rem; }
        .recordar { margin-top: 14px; font-size: .95rem; }
        button { margin-top: 22px; width: 100%; padding: 14px; border: none; border-radius: 10px; background: #0b84c6;
                 color: #fff; font-size: 1.05rem; font-weight: 700; cursor: pointer; }
        .error { background: #fdecec; color: #b3261e; padding: 10px 12px; border-radius: 8px; margin-bottom: 8px; font-size: .95rem; }
        .demo { margin-top: 18px; font-size: .85rem; color: #8aa0b0; text-align: center; }
    </style>
</head>
<body>
    <form class="card" method="POST" action="{{ route('login.post') }}">
        @csrf
        <h1>Turnos Virtuales</h1>
        <p class="sub">Ingreso de asesores</p>

        @if ($errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif

        <label for="email">Correo</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>

        <label for="password">Contraseña</label>
        <input id="password" type="password" name="password" required>

        <div class="recordar"><label><input type="checkbox" name="remember"> Mantener sesión</label></div>

        <button type="submit">Ingresar</button>
        <div class="demo">Demo: asesor@demo.com / 12345678</div>
    </form>
</body>
</html>
