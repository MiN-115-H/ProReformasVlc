<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('rol', ['admin', 'editor'])
                ->default('editor')
                ->after('password')
                ->comment('Rol del usuario');
            $table->boolean('activo')
                ->default(true)
                ->after('rol')
                ->comment('Indica si la cuenta está activa');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['rol', 'activo']);
        });
    }
};
