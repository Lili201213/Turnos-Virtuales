@echo off
start "Servidor web" cmd /k php artisan serve --host=0.0.0.0 --port=8000
start "Reverb" cmd /k php artisan reverb:start

echo Esperando a que arranquen los servidores...
timeout /t 5 /nobreak >nul

start "" http://127.0.0.1:8000/pantalla
start "" http://127.0.0.1:8000/kiosco
start "" http://127.0.0.1:8000/login
