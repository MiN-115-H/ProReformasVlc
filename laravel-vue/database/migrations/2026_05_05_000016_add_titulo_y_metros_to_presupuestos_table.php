<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('presupuestos', function (Blueprint $table) {
            $table->string('titulo', 180)->nullable()->after('id');
            $table->decimal('metros_cuadrados', 8, 2)->nullable()->after('tipo_presupuesto_id');
        });
    }

    public function down(): void
    {
        Schema::table('presupuestos', function (Blueprint $table) {
            $table->dropColumn(['titulo', 'metros_cuadrados']);
        });
    }
};
