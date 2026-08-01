<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class EstudiantePpff extends Pivot
{
    protected $table = 'estudiante_ppff';

    protected $fillable = [
        'estudiante_id',
        'ppff_id',
        'parentesco',
        'es_tutor_principal',
    ];

    protected $casts = [
        'es_tutor_principal' => 'boolean',
    ];
}
