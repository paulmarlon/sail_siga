<?php

namespace App\Observers;

use App\Models\MatriculacionMateria;

class MatriculacionMateriaObserver
{


    /**
     * Handle the MatriculacionMateria "created" event.
     */
    public function created(MatriculacionMateria $matriculacionMateria): void
    {
        // Inicializa automáticamente los parciales según las reglas de la carrera
    }
}
