<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FolioExamen extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'folio_examens';

    protected $fillable = [
        'programacion_id',
        'estudiante_id',
        'codigo_folio',
        'estado_folio',
        'nota',
        'observacion',
        'registrado_por_user_id', // <--- Añadido para el Admin (Foliación / Planta)
        'calificado_por_user_id', // <--- Añadido para el Docente o Admin que califica
    ];

    protected $casts = [
        'nota' => 'decimal:2',
    ];

    /**
     * Relación con la programación del examen.
     */
    public function programacionExamen()
    {
        return $this->belongsTo(ProgramacionExamen::class, 'programacion_id');
    }

    /**
     * Relación con el estudiante propietario del folio.
     */
    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class, 'estudiante_id');
    }
    public function calificacionDetalle()
    {
        return $this->hasOne(CalificacionDetalle::class, 'folio_id');
    }
}
