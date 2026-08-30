<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cobro_suscripcion', function (Blueprint $table) {
            $table->uuid('cobro_suscripcion_id')->primary();
            $table->foreignUuid('suscripcion_id')->constrained('suscripcion', 'suscripcion_id')->cascadeOnDelete();
            $table->unsignedInteger('cobro_monto');
            $table->enum('cobro_estado', ['pendiente', 'exitoso', 'fallido'])->default('pendiente');
            $table->unsignedTinyInteger('cobro_intento_numero')->default(1);
            $table->string('cobro_resultado_pasarela', 20)->nullable();
            $table->timestamp('cobro_fecha')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cobro_suscripcion');
    }
};