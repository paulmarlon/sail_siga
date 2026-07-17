<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Domicilio extends Model
{
    use SoftDeletes;
    protected $table = 'domicilios';

    protected $fillable = [
        'pais',
        'departamento',
        'ciudad',
        'zona',
        'avenida',
        'numero',
        'referencia',
        'latitud',
        'longitud',
        'tipo_domicilio',
    ];
    // Relación inversa 1:1 con Configuracion
    public function configuracion(): HasOne
    {
        return $this->hasOne(Configuracion::class, 'domicilio_id');
    }
    public function persona()
    {
        return $this->hasMany(Persona::class);
    }
}
