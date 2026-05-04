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
            $table->string('cliente_nombre')->comment('Nombre del cliente');
            $table->string('cliente_telefono')->comment('Teléfono de contacto');
            $table->string('cliente_email')->comment('Correo electrónico del cliente');
            $table->string('direccion')->nullable()->comment('Dirección de la obra');
            $table->string('ciudad')->nullable();
            $table->text('observaciones')->nullable()->comment('Observaciones del presupuesto');
            $table->date('fecha_presupuesto')->comment('Fecha de emisión');
            $table->json('lineas')->comment('Líneas del presupuesto en formato JSON');
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
