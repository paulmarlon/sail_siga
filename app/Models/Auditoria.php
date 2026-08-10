<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Auditoria extends Model
{
    use HasFactory;

    protected $table = 'auditorias';

    // La tabla del DBML solo utiliza created_at
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'auditable_id',
        'auditable_type',
        'accion',
        'valores_anteriores',
        'valores_nuevos',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'valores_anteriores' => 'array',
        'valores_nuevos' => 'array',
    ];

    // Relación polimórfica o inversa con el usuario que ejecutó la acción
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
