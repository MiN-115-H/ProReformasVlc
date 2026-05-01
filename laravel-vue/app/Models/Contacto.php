<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contacto extends Model
{
    protected $fillable = [
        'nombre',
        'email',
        'telefono',
        'asunto',
        'mensaje',
        'leido',
        'respondido',
        'fecha_recepcion',
    ];

    protected $casts = [
        'leido' => 'boolean',
        'respondido' => 'boolean',
        'fecha_recepcion' => 'datetime',
    ];
}
