<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // <--- 1. Importar el trait
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Estudiante extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'estudiantes';

    protected $fillable = [
        'persona_id',
        'registro_universitario',
        'estado_id',
    ];

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'persona_id');
    }

    public function estado()
    {
        return $this->belongsTo(Estado::class, 'estado_id');
    }

    // Apoderados relacionados directamente desde la tabla personas
    public function ppffs()
    {
        return $this->belongsToMany(Persona::class, 'estudiante_ppff', 'estudiante_id', 'ppff_persona_id')
            ->withPivot('parentesco', 'es_tutor_principal')
            ->withTimestamps();
    }
}
