<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'role', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function roleModel(): ?Role
    {
        return Role::where('name', $this->role)->first();
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->role === 'Administrador') {
            return true;
        }

        return $this->roleModel()?->hasPermission($permission) ?? false;
    }

    public function firstAllowedRoute(): string
    {
        return match (true) {
            $this->hasPermission('mensajes.enviar') => 'panel',
            $this->hasPermission('historial.ver') => 'historial',
            $this->hasPermission('confirmaciones.ver') => 'confirmaciones',
            $this->hasPermission('usuarios.gestionar') => 'usuarios.index',
            $this->hasPermission('roles.gestionar') => 'roles.index',
            $this->hasPermission('tipos.gestionar') => 'tipos.index',
            default => 'login',
        };
    }
}
