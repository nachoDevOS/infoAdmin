<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'permissions'])]
class Role extends Model
{
    public const PERMISSIONS = [
        'mensajes.enviar'    => 'Enviar mensajes',
        'historial.ver'      => 'Ver historial',
        'confirmaciones.ver' => 'Ver confirmaciones',
        'tipos.gestionar'    => 'Gestionar tipos de mensaje',
        'grupos.gestionar'   => 'Gestionar grupos de PCs',
        'usuarios.gestionar' => 'Gestionar usuarios',
        'roles.gestionar'    => 'Gestionar roles y permisos',
    ];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
        ];
    }

    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions ?? [], true);
    }
}
