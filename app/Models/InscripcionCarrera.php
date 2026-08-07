<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InscripcionCarrera extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'inscripcion_carreras';

    protected $fillable = [
        'estudiante_id',
        'carrera_id',
        'periodo_id',
        'fecha_inscripcion',
        'es_especialidad_activa',
        'registrado_por_user_id',
        'estado_id',
    ];

    protected $casts = [
        'fecha_inscripcion' => 'date',
        'es_especialidad_activa' => 'boolean',
    ];

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class);
    }

    public function carrera(): BelongsTo
    {
        return $this->belongsTo(Carrera::class);
    }

    public function periodo(): BelongsTo
    {
        return $this->belongsTo(Periodo::class);
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por_user_id');
    }

    public function estado(): BelongsTo
    {
        return $this->belongsTo(Estado::class);
    }
}
