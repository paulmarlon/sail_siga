<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConfiguracionComponenteMeta extends Model
{
    protected $table = 'configuracion_componente_meta';

    protected $fillable = [
        'configuracion_parcial_id',
        'tipo_componente',
        'ponderacion_componente',
        'es_obligatorio'
    ];

    public function parcialMeta(): BelongsTo
    {
        return $this->belongsTo(ConfiguracionParcialMeta::class, 'configuracion_parcial_id');
    }
}
