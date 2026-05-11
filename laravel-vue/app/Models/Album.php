<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Album extends Model
{
    protected $fillable = [
        'nombre',
        'descripcion',
        'categoria',
        'fecha_creacion',
    ];

    protected $casts = [
        'fecha_creacion' => 'datetime',
    ];

    public function fotos()
    {
        return $this->hasMany(Foto::class);
    }
}
