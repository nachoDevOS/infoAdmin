<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PcActiva extends Model
{
    protected $table = 'pcs_activas';

    protected $fillable = ['nombre', 'ip', 'grupo'];

    public static function registrar(string $nombre, string $ip, ?string $grupo = null): void
    {
        $datos = ['ip' => $ip];
        if ($grupo !== null) {
            $datos['grupo'] = $grupo ?: null;
        }
        $pc = static::updateOrCreate(['nombre' => $nombre], $datos);
        $pc->touch();
    }

    public static function activas()
    {
        return static::where('updated_at', '>=', now()->subSeconds(180));
    }

    public static function contarActivas(): int
    {
        return static::activas()->count();
    }
}
