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
    ];

    protected $casts = [
        'nota' => 'decimal:2',
    ];

    // Relaciones
    public function programacionExamen()
    {
        return $this->belongsTo(ProgramacionExamen::class, 'programacion_id');
    }

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class, 'estudiante_id');
    }
}
