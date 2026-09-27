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
        'nro_parcial',
        'ponderacion_parcial',
        'tp_nota',
        'exam_nota',
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

    public function folio()
    {
        return $this->belongsTo(FolioExamen::class, 'folio_id');
    }

    public function registradoPor()
    {
        return $this->belongsTo(User::class, 'registrado_por_user_id');
    }
}
