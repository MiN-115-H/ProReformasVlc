<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contactos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('email', 150);
            $table->string('telefono', 20)->nullable();
            $table->string('asunto', 150)->nullable();
            $table->text('mensaje');
            $table->boolean('leido')->default(false);
            $table->boolean('respondido')->default(false);
            $table->timestamp('fecha_recepcion')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contactos');
    }
};
