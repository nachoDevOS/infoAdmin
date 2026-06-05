<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grupos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->unique();
            $table->string('descripcion', 255)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // Asignar grupo a cada PC activa (nullable = sin grupo)
        Schema::table('pcs_activas', function (Blueprint $table) {
            $table->string('grupo', 100)->nullable()->after('ip');
        });

        // Grupo destino en cada mensaje (null = todas las PCs)
        Schema::table('mensajes', function (Blueprint $table) {
            $table->string('grupo_destino', 100)->nullable()->after('remitente');
        });
    }

    public function down(): void
    {
        Schema::table('mensajes', fn (Blueprint $t) => $t->dropColumn('grupo_destino'));
        Schema::table('pcs_activas', fn (Blueprint $t) => $t->dropColumn('grupo'));
        Schema::dropIfExists('grupos');
    }
};
