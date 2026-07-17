<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configuracion extends Model
{
    protected $table = 'configuracions';

    protected $fillable = [
        'nombre_institucion',
        'sigla_institucion',
        'nit',
        'telefono',
        'email_contacto',
        'web',
        'logo_path',
        'divisa',
        'domicilio_id',
        'gestion_actual_id',
    ];

    // Relación inversa 1:1 con Domicilio
    public function domicilio()
    {
        return $this->belongsTo(Domicilio::class, 'domicilio_id');
    }
}
