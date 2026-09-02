<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Asistencia extends Model
{
    use HasFactory;

    protected $table = 'asistencias';

    protected $fillable = [
        'matriculacion_id',
        'fecha',
        'estado_id',
        'observacion',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    /**
     * Relación con la matriculación a la materia.
     */
    public function matriculacionMateria(): BelongsTo
    {
        return $this->belongsTo(MatriculacionMateria::class, 'matriculacion_id');
    }

    /**
     * Relación con el estado de la asistencia.
     */
    public function estado(): BelongsTo
    {
        return $this->belongsTo(Estado::class, 'estado_id');
    }
}
