<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('presupuestos', function (Blueprint $table) {
            $table->enum('estado', ['pendiente', 'aceptado', 'rechazado'])->default('pendiente')->after('cliente_telefono');
            $table->foreignId('tipo_presupuesto_id')->nullable()->after('estado')->constrained('tipos_presupuesto')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('presupuestos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tipo_presupuesto_id');
            $table->dropColumn('estado');
        });
    }
};
