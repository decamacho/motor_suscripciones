<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cliente_suscripcion', function (Blueprint $table) {
            $table->uuid('cliente_suscripcion_id')->primary();
            $table->foreignUuid('cliente_id')->constrained('cliente', 'cliente_id')->cascadeOnDelete();
            $table->foreignUuid('suscripcion_id')->constrained('suscripcion', 'suscripcion_id')->cascadeOnDelete();
            $table->enum('estado_cliente_suscripcion', ['activa', 'pausada', 'cancelada'])->default('activa');
            $table->timestamp('fecha_ultimo_cobro')->nullable();
            $table->timestamp('fecha_proximo_cobro')->nullable();
            $table->timestamps();

            $table->unique(['cliente_id', 'suscripcion_id'], 'cliente_suscripcion_unico');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cliente_suscripcion');
    }
};
