<?php

namespace App\Http\Controllers;

use App\Events\MensajeEnviado;
use App\Models\Grupo;
use App\Models\Mensaje;
use App\Models\PcActiva;
use App\Models\TipoMensaje;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PanelController extends Controller
{
    public function index(): View
    {
        $ultimosMensajes = Mensaje::latest()->limit(5)->get();
        $tipos           = TipoMensaje::activos();
        $grupos          = Grupo::activos();

        return view('panel', compact('ultimosMensajes', 'tipos', 'grupos'));
    }

    public function enviar(Request $request): JsonResponse
    {
        try {
            $slugsValidos = implode(',', TipoMensaje::slugsActivos());
            $request->validate([
                'tipo'           => ['required', 'in:'.$slugsValidos],
                'titulo'         => ['required', 'string', 'max:200'],
                'cuerpo'         => ['required', 'string', 'max:20000'],
                'archivo'        => ['nullable', 'file', 'max:20480', 'mimes:pdf,jpg,jpeg,png,gif'],
                'grupo_destino'  => ['nullable', 'string', 'exists:grupos,nombre'],
                'scheduled_at'   => ['nullable', 'date', 'after:now'],
            ]);
        } catch (ValidationException $e) {
            return response()->json(['ok' => false, 'errors' => $e->errors()], 422);
        }

        try {
            $grupoDestino = $request->grupo_destino ?: null;

            $scheduledAt = $request->scheduled_at ? \Carbon\Carbon::parse($request->scheduled_at) : null;
            $estado      = $scheduledAt ? 'programado' : 'enviado';

            $datos = [
                'tipo'          => $request->tipo,
                'titulo'        => $request->titulo,
                'cuerpo'        => $request->cuerpo,
                'remitente'     => $request->user()->name,
                'grupo_destino' => $grupoDestino,
                'scheduled_at'  => $scheduledAt,
                'estado'        => $estado,
            ];

            if ($request->hasFile('archivo')) {
                $file        = $request->file('archivo');
                $ext         = strtolower($file->getClientOriginalExtension());
                $nombreUnico = uniqid('mensa_', true).'.'.$ext;
                $tamanio     = $file->getSize();

                $file->move(public_path('uploads'), $nombreUnico);

                $datos['tiene_archivo']   = true;
                $datos['archivo_nombre']  = $file->getClientOriginalName();
                $datos['archivo_ruta']    = $nombreUnico;
                $datos['archivo_tipo']    = ($ext === 'pdf') ? 'pdf' : 'imagen';
                $datos['archivo_tamanio'] = $tamanio;
            }

            $mensaje = Mensaje::create($datos);

            // Contar PCs objetivo (todas o solo las del grupo)
            $pcsConectadas = $grupoDestino
                ? PcActiva::activas()->where('grupo', $grupoDestino)->count()
                : PcActiva::contarActivas();

            $mensaje->update(['total_pcs_enviado' => $pcsConectadas]);

            if ($estado === 'enviado') {
                try {
                    event(new MensajeEnviado($mensaje->fresh()));
                } catch (\Throwable) {
                    // Mensaje guardado aunque Reverb no esté disponible
                }
            }

            return response()->json([
                'ok'         => true,
                'mensaje_id' => $mensaje->id,
                'enviados'   => $estado === 'programado' ? 0 : $pcsConectadas,
                'programado' => $estado === 'programado',
                'scheduled_at' => $scheduledAt?->format('d/m/Y H:i'),
            ]);

        } catch (\Throwable $e) {
            Log::error('Error al enviar mensaje: '.$e->getMessage());

            return response()->json(['ok' => false, 'error' => 'Error interno: '.$e->getMessage()], 500);
        }
    }

    public function pcs(): JsonResponse
    {
        return response()->json([
            'count' => PcActiva::contarActivas(),
        ]);
    }
}
