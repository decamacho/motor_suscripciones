<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suscripcion', function (Blueprint $table) {
            $table->uuid('suscripcion_id')->primary();
            $table->foreignUuid('cliente_id')->constrained('cliente', 'cliente_id')->cascadeOnDelete();
            $table->string('suscripcion_nombre', 100);
            $table->string('suscripcion_descripcion', 255)->nullable();
            $table->unsignedInteger('suscripcion_precio');
            $table->enum('suscripcion_periodo', ['mensual', 'anual']);
            $table->enum('suscripcion_estado', ['activa', 'pausada', 'cancelada'])->default('activa');
            $table->timestamp('ultimo_cobro_at')->nullable();
            $table->timestamp('proximo_cobro_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suscripcion');
    }
};