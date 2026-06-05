<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(): View
    {
        $roles = Role::orderBy('name')->get();
        $permissions = Role::PERMISSIONS;

        return view('roles', compact('roles', 'permissions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateRole($request);

        Role::create($data);

        return redirect()
            ->route('roles.index')
            ->with('ok', 'Rol creado correctamente.');
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $data = $this->validateRole($request, $role);

        $oldName = $role->name;
        $role->update($data);

        if ($oldName !== $role->name) {
            User::where('role', $oldName)->update(['role' => $role->name]);
        }

        return redirect()
            ->route('roles.index')
            ->with('ok', 'Rol actualizado correctamente.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if (User::where('role', $role->name)->exists()) {
            return redirect()
                ->route('roles.index')
                ->with('err', 'No puedes eliminar un rol que tiene usuarios asignados.');
        }

        $role->delete();

        return redirect()
            ->route('roles.index')
            ->with('ok', 'Rol eliminado correctamente.');
    }

    private function validateRole(Request $request, ?Role $role = null): array
    {
        $permissions = array_keys(Role::PERMISSIONS);

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:80',
                Rule::unique('roles', 'name')->ignore($role),
            ],
            'permissions' => ['array'],
            'permissions.*' => [Rule::in($permissions)],
        ]);

        $data['permissions'] = array_values($data['permissions'] ?? []);

        return $data;
    }
}
