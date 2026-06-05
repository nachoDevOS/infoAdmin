<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Role::updateOrCreate(
            ['name' => 'Administrador'],
            ['permissions' => array_keys(Role::PERMISSIONS)]
        );

        Role::updateOrCreate(
            ['name' => 'Admin'],
            ['permissions' => [
                'mensajes.enviar',
                'historial.ver',
                'confirmaciones.ver',
            ]]
        );

        User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name'     => 'Administrador',
                'role'     => 'Administrador',
                'password' => Hash::make('password'),
            ]
        );

        $this->call(TiposMensajeSeeder::class);
    }
}
