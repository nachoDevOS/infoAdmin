<?php

namespace App\Console\Commands;

use App\Events\MensajeEnviado;
use App\Models\Mensaje;
use Illuminate\Console\Command;

class EnviarMensajesProgramados extends Command
{
    protected $signature   = 'mensajes:enviar-programados';
    protected $description = 'Envía mensajes programados cuya hora ya llegó';

    public function handle(): void
    {
        $pendientes = Mensaje::where('estado', 'programado')
            ->where('scheduled_at', '<=', now())
            ->get();

        foreach ($pendientes as $mensaje) {
            try {
                event(new MensajeEnviado($mensaje));
                $mensaje->update(['estado' => 'enviado']);
                $this->info("Enviado: #{$mensaje->id} — {$mensaje->titulo}");
            } catch (\Throwable $e) {
                $this->error("Error enviando #{$mensaje->id}: {$e->getMessage()}");
            }
        }

        if ($pendientes->isEmpty()) {
            $this->line('Sin mensajes programados pendientes.');
        }
    }
}
