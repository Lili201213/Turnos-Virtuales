<?php

namespace App\Support;

class Difusion
{
    /**
     * Emite un evento por WebSocket. Si Reverb está apagado,
     * registra el error en el log pero NO rompe el sistema.
     */
    public static function emitir(object $evento): void
    {
        try {
            event($evento);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
