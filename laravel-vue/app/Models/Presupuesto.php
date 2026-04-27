<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Presupuesto extends Model
{
    use HasFactory;

    protected $fillable = [
        'cliente_nombre',
        'cliente_telefono',
        'cliente_email',
        'direccion',
        'ciudad',
        'observaciones',
        'fecha_presupuesto',
        'lineas',
        'subtotal',
        'iva',
        'total',
    ];

    protected $casts = [
        'fecha_presupuesto' => 'date',
        'lineas' => 'array',
    ];
}
