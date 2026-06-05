<?php

namespace App\Http\Controllers;

use App\Models\Confirmacion;
use App\Models\Mensaje;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ConfirmacionesController extends Controller
{
    public function index(): View
    {
        $mensajes = Mensaje::latest()->limit(50)->get(['id', 'titulo', 'created_at']);

        return view('confirmaciones', compact('mensajes'));
    }

    public function exportCsv(Request $request): Response
    {
        $request->validate(['mensaje_id' => ['required', 'exists:mensajes,id']]);

        $mensaje = Mensaje::findOrFail($request->mensaje_id);

        $confirmaciones = Confirmacion::where('mensaje_id', $mensaje->id)
            ->get()
            ->groupBy('pc_nombre')
            ->map(function ($group) use ($mensaje) {
                $recibido  = $group->firstWhere('accion', 'recibido');
                $visto     = $group->firstWhere('accion', 'visto');
                $descargado = $group->firstWhere('accion', 'descargado');

                return [
                    $group->first()->pc_nombre,
                    $group->first()->pc_ip,
                    $recibido  ? 'Sí' : 'No',
                    $recibido?->created_at?->format('d/m/Y H:i:s') ?? '-',
                    $visto     ? 'Sí' : 'No',
                    $visto?->created_at?->format('d/m/Y H:i:s') ?? '-',
                    $mensaje->tiene_archivo ? ($descargado ? 'Sí' : 'No')              : 'Sin archivo',
                    $mensaje->tiene_archivo ? ($descargado?->created_at?->format('d/m/Y H:i:s') ?? '-') : '-',
                ];
            })
            ->values();

        $filename = 'confirmaciones_' . $mensaje->id . '_' . now()->format('Ymd_His') . '.csv';

        $rows = [];
        $rows[] = implode(';', ['PC', 'IP', 'Recibido', 'Hora Recibido', 'Leído', 'Hora Leído', 'Descargado', 'Hora Descargado']);
        foreach ($confirmaciones as $row) {
            $rows[] = implode(';', array_map(fn($v) => '"' . str_replace('"', '""', $v) . '"', $row));
        }
        $csv = "\xEF\xBB\xBF" . implode("\r\n", $rows); // BOM para Excel

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function reenviar(Request $request): JsonResponse
    {
        $request->validate(['mensaje_id' => ['required', 'exists:mensajes,id']]);

        $mensaje = Mensaje::with('confirmaciones')->findOrFail($request->mensaje_id);

        // PCs que aún no confirmaron recepción
        $recibidas = $mensaje->confirmaciones
            ->where('accion', 'recibido')
            ->pluck('pc_nombre')
            ->toArray();

        try {
            event(new \App\Events\MensajeEnviado($mensaje));
        } catch (\Throwable) {
            return response()->json(['ok' => false, 'error' => 'Reverb no disponible'], 503);
        }

        return response()->json([
            'ok'      => true,
            'mensaje' => "Mensaje reenviado. {$mensaje->total_pcs_enviado} PC(s) objetivo. " . count($recibidas) . ' ya lo habían recibido.',
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $request->validate(['mensaje_id' => ['required', 'exists:mensajes,id']]);

        $mensaje = Mensaje::findOrFail($request->mensaje_id);

        $confirmaciones = Confirmacion::where('mensaje_id', $mensaje->id)
            ->get()
            ->groupBy('pc_nombre')
            ->map(function ($group) {
                $recibido = $group->firstWhere('accion', 'recibido');
                $visto = $group->firstWhere('accion', 'visto');
                $descargado = $group->firstWhere('accion', 'descargado');

                return [
                    'pc_nombre'      => $group->first()->pc_nombre,
                    'pc_ip'          => $group->first()->pc_ip,
                    'recibido'       => (bool) $recibido,
                    'visto'          => (bool) $visto,
                    'descargado'     => (bool) $descargado,
                    'recibido_hora'  => $recibido?->created_at?->format('d/m/Y H:i:s'),
                    'leido_hora'     => $visto?->created_at?->format('d/m/Y H:i:s'),
                    'descargado_hora'=> $descargado?->created_at?->format('d/m/Y H:i:s'),
                ];
            })
            ->values();

        $total     = $mensaje->total_pcs_enviado;
        $recibidos = $confirmaciones->where('recibido', true)->count();
        $descargas = $confirmaciones->where('descargado', true)->count();

        return response()->json([
            'mensaje' => [
                'tiene_archivo' => (bool) $mensaje->tiene_archivo,
            ],
            'confirmaciones' => $confirmaciones,
            'stats' => [
                'total'       => $total,
                'recibidos'   => $recibidos,
                'pct_recibido' => $total > 0 ? round($recibidos / $total * 100) : 0,
                'descargas'   => $descargas,
                'pct_descarga' => $total > 0 ? round($descargas / $total * 100) : 0,
            ],
        ]);
    }
}
