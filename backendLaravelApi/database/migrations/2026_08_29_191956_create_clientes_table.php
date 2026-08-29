<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('cliente', function (Blueprint $table) {
            // creacion atributos de la tabla cliente
            $table->uuid('cliente_id')->primary();
            $table->string('cliente_nombre', 100);
            $table->string('cliente_correo', 150)->unique();
            $table->string('cliente_documento', 10)->unique();
            $table->string('cliente_telefono', 10);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cliente');
    }
};
