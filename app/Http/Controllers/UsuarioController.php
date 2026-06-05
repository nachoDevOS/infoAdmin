<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UsuarioController extends Controller
{
    public function index(): View
    {
        $usuarios = User::query()
            ->latest()
            ->get();
        $roles = Role::orderBy('name')->get();

        return view('usuarios', compact('usuarios', 'roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::exists('roles', 'name')],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'password' => Hash::make($data['password']),
        ]);

        return redirect()
            ->route('usuarios.index')
            ->with('ok', 'Usuario creado correctamente.');
    }

    public function destroy(User $usuario): RedirectResponse
    {
        if ($usuario->is(Auth::user())) {
            return redirect()
                ->route('usuarios.index')
                ->with('err', 'No puedes eliminar tu propio usuario.');
        }

        $usuario->delete();

        return redirect()
            ->route('usuarios.index')
            ->with('ok', 'Usuario eliminado correctamente.');
    }
}
