<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalificacionDetalle extends Model
{
    protected $table = 'calificacion_detalles'; // O el nombre exacto de tu tabla de detalles

    protected $fillable = [
        'calificacion_parcial_id',
        'tipo_componente',
        'ponderacion_componente',
        'nota',
        'modalidad_origen',
        'folio_id',
        'estado_id',
        'registrado_por_user_id',
        'observacion',
    ];
    public function calificacionParcial()
    {
        return $this->belongsTo(CalificacionParcial::class, 'calificacion_parcial_id');
    }
}
