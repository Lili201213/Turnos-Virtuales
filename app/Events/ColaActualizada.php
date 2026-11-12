<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class ColaActualizada implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(public ?int $servicioId = null)
    {
    }

    public function broadcastOn(): array
    {
        return [new Channel('turnos')];
    }

    public function broadcastAs(): string
    {
        return 'cola.actualizada';
    }
}
