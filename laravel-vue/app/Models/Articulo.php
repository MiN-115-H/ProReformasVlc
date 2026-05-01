<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Articulo extends Model
{
    protected $fillable = [
        'servicio_id',
        'nombre',
        'descripcion',
        'precio',
        'activo',
        'fecha_creacion',
    ];

    protected $casts = [
        'fecha_creacion' => 'datetime',
    ];

    public function servicio()
    {
        return $this->belongsTo(Servicio::class);
    }
}
