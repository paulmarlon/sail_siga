<?php

namespace App\Services;

use App\Models\MatriculacionMateria;
use App\Models\OfertaAcademica;
use App\Models\Pensum;
use App\Models\ConfiguracionParcialMeta;
use App\Models\CalificacionParcial;
use Exception;

class CalificacionService
{
    public function inicializarLibretaEstudiante(MatriculacionMateria $matriculacion): void
    {
        // 1. Obtener la oferta académica directamente
        $oferta = OfertaAcademica::find($matriculacion->oferta_id);

        if (!$oferta || !$oferta->pensum_id) {
            throw new Exception("La matriculación ID {$matriculacion->id} tiene un oferta_id inválido o no tiene pensum asociado.");
        }

        // 2. Obtener el pensum directamente
        $pensum = Pensum::find($oferta->pensum_id);

        if (!$pensum || !$pensum->carrera_id) {
            throw new Exception("El pensum ID {$oferta->pensum_id} no tiene una carrera asociada.");
        }

        $carreraId = $pensum->carrera_id;

        // 3. Buscar las reglas meta para esta carrera
        $metasParciales = ConfiguracionParcialMeta::where('carrera_id', $carreraId)
            ->where('estado_id', 1) // Activo
            ->get();

        foreach ($metasParciales as $metaParcial) {
            // 4. Crear el parcial transaccional con las columnas directas de tu migración
            CalificacionParcial::create([
                'matriculacion_id' => $matriculacion->id,
                'nro_parcial' => $metaParcial->nro_parcial,
                'ponderacion_parcial' => $metaParcial->ponderacion_parcial,
                'tp_nota' => 0.00,
                'exam_nota' => 0.00,
                'metodo_registro' => 'directa',
                'nota_parcial_calculada' => 0.00,
                'estado_id' => 1, // Vigente
            ]);
        }
    }
}
