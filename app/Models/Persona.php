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

    public function domicilio()
    {
        return $this->belongsTo(Domicilio::class, 'domicilio_id');
    }
}
