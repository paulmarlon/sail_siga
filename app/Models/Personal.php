<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Personal extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'personals';
    protected $fillable = [
        'persona_id',
        'usuario_id',
        'tipo',
        'profesion',
        'estado_id',
    ];
    public function persona()
    {
        return $this->belongsTo(Persona::class);
    }
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
    public function estado()
    {
        return $this->belongsTo(Estado::class);
    }
}
