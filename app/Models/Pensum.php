<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pensum extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'carrera_id',
        'materia_id',
        'grado_id',
        'es_obligatoria',
        'estado_id'
    ];

    public function carrera()
    {
        return $this->belongsTo(Carrera::class);
    }
    public function materia()
    {
        return $this->belongsTo(Materia::class);
    }
    public function grado()
    {
        return $this->belongsTo(Grado::class);
    }
    public function estado()
    {
        return $this->belongsTo(Estado::class);
    }
}
