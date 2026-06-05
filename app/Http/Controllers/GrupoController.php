<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use App\Models\PcActiva;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GrupoController extends Controller
{
    public function index(): View
    {
        $grupos = Grupo::orderBy('nombre')->get();
        $pcs    = PcActiva::orderBy('nombre')->get();

        return view('grupos', compact('grupos', 'pcs'));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'nombre'      => ['required', 'string', 'max:100', 'unique:grupos,nombre'],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ]);

        $grupo = Grupo::create([
            'nombre'      => $request->nombre,
            'descripcion' => $request->descripcion,
            'activo'      => true,
        ]);

        return response()->json(['ok' => true, 'grupo' => $grupo]);
    }

    public function update(Request $request, Grupo $grupo): JsonResponse
    {
        $request->validate([
            'nombre'      => ['required', 'string', 'max:100', 'unique:grupos,nombre,' . $grupo->id],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'activo'      => ['boolean'],
        ]);

        $grupo->update($request->only('nombre', 'descripcion', 'activo'));

        return response()->json(['ok' => true, 'grupo' => $grupo->fresh()]);
    }

    public function destroy(Grupo $grupo): JsonResponse
    {
        // Desasignar PCs que pertenecían a este grupo
        PcActiva::where('grupo', $grupo->nombre)->update(['grupo' => null]);

        $grupo->delete();

        return response()->json(['ok' => true]);
    }

    public function asignarPc(Request $request): JsonResponse
    {
        $request->validate([
            'pc_nombre' => ['required', 'string', 'exists:pcs_activas,nombre'],
            'grupo'     => ['nullable', 'string'],
        ]);

        PcActiva::where('nombre', $request->pc_nombre)
            ->update(['grupo' => $request->grupo ?: null]);

        return response()->json(['ok' => true]);
    }
}
