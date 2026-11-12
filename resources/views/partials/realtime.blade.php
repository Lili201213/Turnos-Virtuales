{{-- Conexión WebSocket con Laravel Reverb (protocolo Pusher) --}}
<script src="https://cdn.jsdelivr.net/npm/pusher-js@8.4.0/dist/web/pusher.min.js"></script>
<script>
    /**
     * conectarTiempoReal({ 'turno.llamado': fn, 'cola.actualizada': fn }, estado => {...})
     */
    window.conectarTiempoReal = function (eventos, alCambiarEstado) {
        try {
            const pusher = new Pusher(@json(config('reverb.apps.apps.0.key')), {
                cluster: 'mt1',
                wsHost: window.location.hostname,
                wsPort: {{ (int) config('reverb.apps.apps.0.options.port', 8080) }},
                wssPort: {{ (int) config('reverb.apps.apps.0.options.port', 8080) }},
                forceTLS: @json(config('reverb.apps.apps.0.options.scheme', 'http') === 'https'),
                enabledTransports: ['ws', 'wss'],
            });

            const canal = pusher.subscribe('turnos');
            Object.entries(eventos).forEach(([nombre, fn]) => canal.bind(nombre, fn));

            pusher.connection.bind('state_change', e => {
                if (alCambiarEstado) alCambiarEstado(e.current);
            });
        } catch (error) {
            console.error('No se pudo conectar al WebSocket', error);
            if (alCambiarEstado) alCambiarEstado('failed');
        }
    };
</script>
