<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Grado extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nombre',
        'orden',
        'ciclo',
        'nivel_id',
        'estado_id'
    ];

    // Relación con Nivel
    public function nivel()
    {
        return $this->belongsTo(Nivel::class);
    }

    // Relación con Estado
    public function estado()
    {
        return $this->belongsTo(Estado::class);
    }
    public function pensums()
    {
        return $this->hasMany(Pensum::class);
    }
}
