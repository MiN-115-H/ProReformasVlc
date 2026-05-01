<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unidades', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('abreviatura', 20);
            $table->timestamps();
            $table->unique('abreviatura');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unidades');
    }
};
