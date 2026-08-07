<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Estado extends Model
{
    // Laravel por defecto buscaría la tabla 'estados' (en plural).
    // Como tu tabla es 'estado', debemos especificarla:
    protected $table = 'estados';

    // Desactivamos timestamps si tu tabla no tiene created_at/updated_at
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'slug',
        'contexto',
        'permite_login',
        'permite_procesos_academicos',
        'color_hex'
    ];
    public function carreras()
    {
        return $this->hasMany(Carrera::class);
    }
    public function materias()
    {
        return $this->hasMany(Materia::class, 'estado_id');
    }
    public function grados()
    {
        return $this->hasMany(Grado::class);
    }
    public function pensums()
    {
        return $this->hasMany(Pensum::class);
    }
    public function personals()
    {
        return $this->hasMany(Personal::class);
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
    /**
     * Relación con las matriculaciones de materias que tienen este estado.
     */
    public function matriculacionesMaterias()
    {
        return $this->hasMany(MatriculacionMateria::class);
    }
}
