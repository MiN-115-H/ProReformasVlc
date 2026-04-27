<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presupuestos', function (Blueprint $table) {
            $table->id();
            $table->string('cliente_nombre');
            $table->string('cliente_telefono');
            $table->string('cliente_email');
            $table->string('direccion')->nullable();
            $table->string('ciudad')->nullable();
            $table->text('observaciones')->nullable();
            $table->date('fecha_presupuesto');
            $table->json('lineas');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('iva', 12, 2);
            $table->decimal('total', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presupuestos');
    }
};
