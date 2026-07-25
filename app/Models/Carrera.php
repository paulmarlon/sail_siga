<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Carrera extends Model
{
    use SoftDeletes;

    // Campos permitidos para asignación masiva
    protected $fillable = [
        'carrera_base_id',
        'nombre',
        'sigla',
        'resolucion',
        'duracion',
        'titulo',
        'es_tronco_comun',
        'nivel_id',
        'estado_id'
    ];

    /**
     * Relación: Un Carrera pertenece a un Nivel
     */
    public function nivel()
    {
        return $this->belongsTo(Nivel::class);
    }

    /**
     * Relación: Una Carrera pertenece a un Estado
     */
    public function estado()
    {
        return $this->belongsTo(Estado::class);
    }

    /**
     * Relación recursiva: Una carrera puede depender de otra (carrera base)
     */
    public function carreraBase()
    {
        return $this->belongsTo(Carrera::class, 'carrera_base_id');
    }

    /**
     * Relación: Una carrera puede tener muchos ítems en el Pensum
     */
    /*public function pensums()
    {
        return $this->hasMany(Pensum::class);
    }*/
    public function pensums()
    {
        return $this->hasMany(Pensum::class);
    }
    public function materias()
    {
        return $this->hasManyThrough(Materia::class, Pensum::class, 'carrera_id', 'id', 'id', 'materia_id');
    }
}
