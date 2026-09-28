<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProgramacionExamen;
use App\Models\FolioExamen;
use App\Models\MatriculacionMateria;
use App\Models\CalificacionParcial;
use App\Models\ConfiguracionParcialMeta;
use App\Models\ConfiguracionComponenteMeta;
use App\Models\CalificacionDetalle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminCalificacionController extends Controller
{
    /**
     * Consolida oficialmente las notas de los folios de una programación de examen
     * hacia las tablas transaccionales de calificaciones (calificacion_parcials y calificacion_detalles).
     */
    public function consolidarNotasFolio(Request $request, ProgramacionExamen $programacion)
    {
        if ($programacion->bloqueado) {
            return response()->json([
                'success' => false,
                'message' => 'Esta programación de examen está BLOQUEADA por la administración. No se puede consolidar.'
            ], 403);
        }

        $userId = Auth::id();
        $ofertaId = $programacion->oferta_id;

        // Lectura segura del número de parcial
        $nroParcial = filter_var($programacion->instancia, FILTER_SANITIZE_NUMBER_INT);
        if (!$nroParcial) {
            // Mapeo por si la instancia viene en texto
            $instancia = strtoupper(trim($programacion->instancia));
            $nroParcial = match (true) {
                str_contains($instancia, '1') => 1,
                str_contains($instancia, '2') => 2,
                str_contains($instancia, '3') => 3,
                default => 1
            };
        }

        try {
            DB::beginTransaction();
            $estadoVigenteId = DB::table('estados')
                ->where('slug', 'vigente')
                ->where('contexto', 'academico')
                ->value('id') ?? 1;

            $folios = FolioExamen::where('programacion_id', $programacion->id)
                ->whereNotNull('nota')
                ->get();

            if ($folios->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron notas registradas en los folios para consolidar.'
                ], 422);
            }

            // 1. Obtener la carrera de forma segura (con respaldo directo a BD si la relación falla)
            $carreraId = null;
            if ($programacion->oferta && $programacion->oferta->pensum) {
                $carreraId = $programacion->oferta->pensum->carrera_id;
            } else {
                // Respaldo directo consultando la BD si la relación de Laravel no está cargada
                $carreraId = DB::table('oferta_academicas')
                    ->join('pensums', 'oferta_academicas.pensum_id', '=', 'pensums.id')
                    ->where('oferta_academicas.id', $ofertaId)
                    ->value('pensums.carrera_id');
            }

            // Si aún así no encuentra carrera, por defecto asumimos la carrera 1 (Formación Básica) que nos comentaste
            if (!$carreraId) {
                $carreraId = 1;
            }

            // 2. Buscar la configuración meta del parcial usando la carrera obtenida
            $metaParcial = ConfiguracionParcialMeta::where('nro_parcial', $nroParcial)
                ->where(function ($query) use ($carreraId) {
                    $query->where('carrera_id', $carreraId)
                        ->orWhereNull('carrera_id');
                })
                ->orderBy('carrera_id', 'desc')
                ->first();

            if (!$metaParcial) {
                return response()->json([
                    'success' => false,
                    'message' => "No existe una configuración de ponderación institucional (Parcial {$nroParcial}) definida para la carrera ID {$carreraId}."
                ], 422);
            }

            $ponderacionParcialOficial = $metaParcial->ponderacion_parcial;
            $configuracionParcialId = $metaParcial->id;

            // 3. Buscar la ponderación del componente 'examen' en la tabla base
            $metaComponente = ConfiguracionComponenteMeta::where('configuracion_parcial_id', $configuracionParcialId)
                ->where('tipo_componente', 'examen')
                ->first();

            if (!$metaComponente) {
                return response()->json([
                    'success' => false,
                    'message' => "La configuración del parcial no tiene definido el componente 'examen' en las tablas base."
                ], 422);
            }

            $ponderacionComponenteOficial = $metaComponente->ponderacion_componente;

            $registrosProcesados = 0;

            foreach ($folios as $folio) {
                $matriculacion = MatriculacionMateria::where('estudiante_id', $folio->estudiante_id)
                    ->where('oferta_id', $ofertaId)
                    ->first();

                if (!$matriculacion) continue;

                // 4. Cabecera vinculada
                $calificacionParcial = CalificacionParcial::updateOrCreate(
                    [
                        'matriculacion_id'          => $matriculacion->id,
                        'nro_parcial'               => $nroParcial,
                    ],
                    [
                        'configuracion_parcial_id'  => $configuracionParcialId,
                        'ponderacion_parcial'       => $ponderacionParcialOficial,
                        'nota_parcial_calculada'    => 0.00,
                        'estado_id'                 => $estadoVigenteId,
                    ]
                );

                // 5. Detalle del componente examen
                CalificacionDetalle::updateOrCreate(
                    [
                        'calificacion_parcial_id'   => $calificacionParcial->id,
                        'folio_id'                  => $folio->id,
                    ],
                    [
                        'tipo_componente'           => 'examen',
                        'ponderacion_componente'    => $ponderacionComponenteOficial,
                        'nota'                      => $folio->nota,
                        'modalidad_origen'          => $programacion->modalidad ?? 'a_ciegas',
                        'estado_id'                 => $estadoVigenteId,
                        'registrado_por_user_id'    => $userId,
                        'observacion'               => $folio->observacion ?? 'Consolidado desde folio: ' . $folio->codigo_folio,
                    ]
                );

                // 6. Recalcular la nota parcial de la cabecera
                $detalles = CalificacionDetalle::where('calificacion_parcial_id', $calificacionParcial->id)->get();

                $sumaParcial = 0;
                foreach ($detalles as $det) {
                    $sumaParcial += ($det->nota * ($det->ponderacion_componente / 100));
                }

                $calificacionParcial->nota_parcial_calculada = round($sumaParcial, 2);
                $calificacionParcial->save();

                // 7. Actualizar estado del folio
                $folio->update(['estado_folio' => 'Consolidado']);

                $registrosProcesados++;
            }

            // Bloquear programación tras consolidar con éxito
            $programacion->update(['bloqueado' => true]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "¡Consolidación exitosa! Se procesaron y calcularon las notas para {$registrosProcesados} estudiantes."
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error crítico al consolidar las notas: ' . $e->getMessage()
            ], 500);
        }
    }
}
