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
        Schema::create('detalle_recibo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recibo_id')->constrained('recibos')->onDelete('cascade');
            $table->foreignId('concepto_id')->constrained('conceptos');
            $table->string('description_detail')->nullable();
            $table->decimal('monto_usd', 10, 2)->default(0);
            $table->decimal('monto_soles', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_recibo');
    }
};
