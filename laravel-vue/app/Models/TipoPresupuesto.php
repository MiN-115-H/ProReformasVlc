<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoPresupuesto extends Model
{
    protected $table = 'tipos_presupuesto';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];
}
