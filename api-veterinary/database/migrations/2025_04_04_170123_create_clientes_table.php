<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_tipodocidentidad');
            $table->string('nombres',250)->nullable();
            $table->string('apellidos',250)->nullable();
            $table->string('razon_social', 250);
            $table->string('nombre_comercial', 250);
            $table->string('codigo', 20);
            $table->string('num_doc', 20);
            $table->string('direccion_fiscal')->nullable();
            $table->string('email')->nullable();
            $table->string('celular', 20)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->boolean('estado')->default(true); 
            $table->string('num_cuenta_detraccion')->nullable();
            $table->date('fecha_registro');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
