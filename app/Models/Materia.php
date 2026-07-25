<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Materia extends Model
{
    use SoftDeletes; // Si decides usar papelera como en Periodos

    protected $fillable = [
        'sigla',
        'nombre',
        'descripcion',
        'horas_academicas',
        'tipo_materia',
        'es_comun',
        'estado_id'
    ];

    // Relación con Estados
    public function estado()
    {
        return $this->belongsTo(Estado::class, 'estado_id');
    }
    public function pensums()
    {
        return $this->hasMany(Pensum::class);
    }
    public function carreras()
    {
        return $this->hasManyThrough(Carrera::class, Pensum::class, 'materia_id', 'id', 'id', 'carrera_id');
    }
}
