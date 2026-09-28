<?php

namespace App\Observers;

use App\Models\MatriculacionMateria;
use App\Services\CalificacionService;

class MatriculacionMateriaObserver
{
    protected CalificacionService $calificacionService;

    public function __construct(CalificacionService $calificacionService)
    {
        $this->calificacionService = $calificacionService;
    }

    /**
     * Handle the MatriculacionMateria "created" event.
     */
    public function created(MatriculacionMateria $matriculacionMateria): void
    {
        // Inicializa automáticamente los parciales según las reglas de la carrera
        $this->calificacionService->inicializarLibretaEstudiante($matriculacionMateria);
    }
}
