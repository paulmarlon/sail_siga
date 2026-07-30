<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfertaDocenteHistorial extends Model
{
    use HasFactory;

    protected $table = 'oferta_docente_historials';

    protected $fillable = [
        'oferta_id',
        'docente_id',
        'contrato_id',
        'fecha_inicio',
        'fecha_fin',
        'motivo_cambio',
        'estado_id',
        'registrado_por_user_id',
    ];

    public function ofertaAcademica()
    {
        return $this->belongsTo(OfertaAcademica::class, 'oferta_id');
    }

    public function docente()
    {
        return $this->belongsTo(Personal::class, 'docente_id');
    }

    public function estado()
    {
        return $this->belongsTo(Estado::class, 'estado_id');
    }

    public function registradoPor()
    {
        return $this->belongsTo(User::class, 'registrado_por_user_id');
    }
}
