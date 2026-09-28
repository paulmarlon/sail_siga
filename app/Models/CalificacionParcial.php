<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalificacionParcial extends Model
{
    use HasFactory;

    protected $table = 'calificacion_parcials';

    protected $fillable = [
        'matriculacion_id',
        'configuracion_parcial_id', // <-- ¡AQUÍ ESTABA FALTANDO!
        'nro_parcial',
        'ponderacion_parcial',
        'metodo_registro',
        'folio_id',
        'nota_parcial_calculada',
        'registrado_por_user_id',
        'estado_id',
    ];

    public function matriculacion()
    {
        return $this->belongsTo(MatriculacionMateria::class, 'matriculacion_id');
    }
    public function detalles()
    {
        return $this->hasMany(CalificacionDetalle::class, 'calificacion_parcial_id');
    }
}
