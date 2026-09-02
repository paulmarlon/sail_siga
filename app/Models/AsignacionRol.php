<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class AsignacionRol extends Model
{
    use HasFactory;

    protected $table = 'asignacion_rols';

    protected $fillable = [
        'persona_id',
        'role_id',
        'entidad_id',
        'entidad_type',
        'estado_id',
    ];

    // Relación con Persona
    public function persona()
    {
        return $this->belongsTo(Persona::class);
    }

    // Relación con el Rol de Spatie
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    // Relación polimórfica para el Contexto (Carrera, Departamento, etc.)
    public function entidad()
    {
        return $this->morphTo();
    }

    // Relación con Estado
    public function estado()
    {
        return $this->belongsTo(Estado::class);
    }
}
