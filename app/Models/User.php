<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles; // Si usas Spatie para roles y permisos

#[Fillable(['persona_id', 'email', 'password'])] // Cambiamos name por persona_id
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * Un usuario pertenece a una persona biográfica.
     */
    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'persona_id');
    }

    /**
     * Accesor para que Auth::user()->name siga funcionando en tu dashboard
     */
    public function getNameAttribute(): string
    {
        return $this->persona
            ? "{$this->persona->nombres} {$this->persona->ap_paterno}"
            : 'Sin nombre';
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function personal(): HasOne
    {
        return $this->hasOne(Personal::class, 'usuario_id');
    }
    // Agrega esta relación en tu modelo User existente
    public function historialesDocentesRegistrados()
    {
        return $this->hasMany(OfertaDocenteHistorial::class, 'registrado_por_user_id');
    }
}
