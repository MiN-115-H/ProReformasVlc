<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Concepto extends Model
{
    protected $fillable = [
        'descripcion',
        'precio_base',
        'unidad_id',
        'tipo_presupuesto_id',
        'activo',
    ];

    public function unidad()
    {
        return $this->belongsTo(Unidad::class);
    }

    public function tipoPresupuesto()
    {
        return $this->belongsTo(TipoPresupuesto::class);
    }
}
