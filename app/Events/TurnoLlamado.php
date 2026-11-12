<?php

namespace App\Events;

use App\Models\Turno;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class TurnoLlamado implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public array $turno;

    public function __construct(Turno $turno)
    {
        $turno->loadMissing(['servicio', 'modulo']);

        $this->turno = [
            'id'       => $turno->id,
            'codigo'   => $turno->codigo,
            'servicio' => $turno->servicio->nombre,
            'modulo'   => $turno->modulo?->nombre,
        ];
    }

    public function broadcastOn(): array
    {
        return [new Channel('turnos')];
    }

    public function broadcastAs(): string
    {
        return 'turno.llamado';
    }
}
