<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\TipoPresupuesto;

class Presupuesto extends Model
{
    use HasFactory;

    protected $fillable = [
        'titulo',
        'metros_cuadrados',
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
        'estado',
        'tipo_presupuesto_id',
    ];

    protected $casts = [
        'fecha_presupuesto' => 'date',
        'lineas' => 'array',
        'metros_cuadrados' => 'float',
    ];

    public function tipoPresupuesto()
    {
        return $this->belongsTo(TipoPresupuesto::class);
    }
}
