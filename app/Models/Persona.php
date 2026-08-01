<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Persona extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ci',
        'nombres',
        'ap_paterno',
        'ap_materno',
        'fecha_nacimiento',
        'sexo',
        'celular',
        'email_personal',
        'foto_path',
        'domicilio_id'
    ];

    // Relación con domicilios
    public function domicilio()
    {
        return $this->belongsTo(Domicilio::class, 'domicilio_id');
    }

    // Una persona puede tener un usuario del sistema (relación 1 a 1 inversa)
    public function user()
    {
        return $this->hasOne(User::class, 'persona_id');
    }

    // Una persona puede ser parte del personal (docente/administrativo)
    public function personal()
    {
        return $this->hasOne(Personal::class, 'persona_id');
    }

    // Una persona puede ser un estudiante registrado (relación 1 a 1 inversa)
    public function estudiante()
    {
        return $this->hasOne(Estudiante::class, 'persona_id');
    }

    // Si esta persona actúa como Apoderado (PPFF), aquí obtenemos los estudiantes a su cargo
    public function estudiantesACargo()
    {
        return $this->belongsToMany(Estudiante::class, 'estudiante_ppff', 'ppff_persona_id', 'estudiante_id')
            ->withPivot('parentesco', 'es_tutor_principal')
            ->withTimestamps();
    }
}
