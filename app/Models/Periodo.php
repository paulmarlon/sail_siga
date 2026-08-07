<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Periodo extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['nombre', 'gestion_id', 'fecha_inicio', 'fecha_fin', 'estado_id'];

    public function gestion()
    {
        return $this->belongsTo(Gestion::class, 'gestion_id');
    }
    public function estado()
    {
        return $this->belongsTo(Estado::class, 'estado_id');
    }
    // Relación con Oferta Académica
    public function ofertasAcademicas()
    {
        return $this->hasMany(OfertaAcademica::class);
    }
    public function inscripcionesCarrera(): HasMany
    {
        return $this->hasMany(InscripcionCarrera::class);
    }
}
