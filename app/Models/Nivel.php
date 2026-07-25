<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Nivel extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'nivels';
    protected $fillable = [
        'nombre'
    ];
    /**
     * Un nivel tiene muchas carreras.
     */
    public function carreras()
    {
        return $this->hasMany(Carrera::class);
    }
    public function grados()
    {
        return $this->hasMany(Grado::class);
    }
}
