<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conceptos', function (Blueprint $table) {
            $table->id();
            $table->string('descripcion', 150);
            $table->decimal('precio_base', 10, 2);
            $table->foreignId('unidad_id')->constrained('unidades')->restrictOnDelete();
            $table->foreignId('tipo_presupuesto_id')->constrained('tipos_presupuesto')->restrictOnDelete();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conceptos');
    }
};
