<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OfertaAcademica extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'oferta_academicas';

    protected $fillable = [
        'periodo_id',
        'paralelo_id',
        'turno_id',
        'pensum_id',
        'cupo_maximo',
        'estado_id',
    ];

    // Relaciones
    public function periodo()
    {
        return $this->belongsTo(Periodo::class);
    }

    public function paralelo()
    {
        return $this->belongsTo(Paralelo::class);
    }

    public function turno()
    {
        return $this->belongsTo(Turno::class);
    }

    public function pensum()
    {
        return $this->belongsTo(Pensum::class);
    }

    public function estado()
    {
        return $this->belongsTo(Estado::class);
    }
    // Agrega esta relación en tu modelo OfertaAcademica existente
    public function historialDocentes()
    {
        return $this->hasMany(OfertaDocenteHistorial::class, 'oferta_id');
    }

    // Atajo directo para obtener al docente que está dictando la materia AHORA MISMO
    public function docenteActual()
    {
        return $this->hasOne(OfertaDocenteHistorial::class, 'oferta_id')
            ->whereNull('fecha_fin')
            ->latest(); // O el vigente
    }
}
