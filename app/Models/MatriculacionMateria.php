<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MatriculacionMateria extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'estudiante_id',
        'oferta_id',
        'estado_id',
        'fecha_registro',
    ];

    /**
     * Relación con el estudiante.
     */
    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class);
    }

    /**
     * Relación con la oferta académica.
     */
    public function oferta()
    {
        return $this->belongsTo(OfertaAcademica::class);
    }

    /**
     * Relación con el estado (ej: Matriculado, Retirado, Finalizado).
     */
    public function estado()
    {
        return $this->belongsTo(Estado::class);
    }

    /**
     * Scope para filtrar solo materias activas (muy útil para reportes).
     */
    public function scopeActivas($query)
    {
        return $query->whereHas('estado', function ($q) {
            $q->where('slug', 'matriculado');
        });
    }
}
