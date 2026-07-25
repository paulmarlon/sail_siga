<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Gestion extends Model
{
    use SoftDeletes, HasFactory;
    protected $table = 'gestions';
    protected $fillable = ['nombre'];
    // Una gestión tiene muchos periodos
    public function periodos()
    {
        return $this->hasMany(Periodo::class);
    }
    public function estado()
    {
        return $this->belongsTo(Estado::class, 'estado_id');
    }
}
