<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConfiguracionParcialMeta extends Model
{
    protected $table = 'configuracion_parcial_meta';

    protected $fillable = [
        'carrera_id',
        'nro_parcial',
        'nombre',
        'ponderacion_parcial',
        'estado_id'
    ];

    public function componentes(): HasMany
    {
        return $this->hasMany(ConfiguracionComponenteMeta::class, 'configuracion_parcial_id');
    }

    public function carrera(): BelongsTo
    {
        return $this->belongsTo(Carrera::class);
    }
}
