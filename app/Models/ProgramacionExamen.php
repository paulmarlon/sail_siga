<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProgramacionExamen extends Model
{
    //
    use HasFactory, SoftDeletes;
    protected $table = 'programacion_examens';
    protected $fillable = [
        'oferta_id',
        'instancia',
        'modalidad',
        'tipo_proceso',
        'fecha_programada',
        'responsable_id',
        'bloqueado',
        'observaciones_legales',
    ];
    protected $casts = [
        'fecha_programada' => 'datetime',
        'bloqueado' => 'boolean',
    ];
    public function ofertaAcademica()
    {
        return $this->belongsTo(OfertaAcademica::class, 'oferta_id');
    }
    public function responsable()
    {
        return $this->belongsTo(Personal::class, 'responsable_id');
    }
}
