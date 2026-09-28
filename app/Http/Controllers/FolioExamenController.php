<?php

namespace App\Http\Controllers;

use App\Models\ProgramacionExamen;
use App\Models\Estudiante;
use App\Models\FolioExamen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class FolioExamenController extends Controller
{
    /**
     * Muestra la lista de folios correspondientes a una programación de examen específica.
     */
    public function index(int $programacionId)
    {
        $programacionExamen = ProgramacionExamen::with([
            'ofertaAcademica.pensum.materia',
            'ofertaAcademica.turno',
            'ofertaAcademica.paralelo'
        ])->findOrFail($programacionId);

        if ($programacionExamen->modalidad !== 'a_ciegas') {
            return redirect()->route('admin.programacion-examenes.index')
                ->with('error', 'Esta programación no corresponde a la modalidad a ciegas.');
        }

        // Usando programacion_id como dicta tu esquema DBML
        $folios = FolioExamen::where('programacion_id', $programacionId)->get();

        return view('admin.folios-examenes.index', compact('programacionExamen', 'folios'));
    }

    /**
     * Muestra la interfaz de plantilla/grilla para tabular los folios del examen.
     */
    public function plantilla(int $programacionId)
    {
        $programacion = ProgramacionExamen::with([
            'ofertaAcademica.pensum.materia',
            'ofertaAcademica.paralelo',
            'ofertaAcademica.periodo.gestion',
            'ofertaAcademica.ofertaDocenteHistorial.docente.persona'
        ])->findOrFail($programacionId);

        $estudiantesParalelo = Estudiante::whereHas('matriculacionesMaterias', function ($query) use ($programacion) {
            $query->where('oferta_id', $programacion->oferta_id);
        })->with('persona')->get()
            ->map(function ($estudiante) {
                return [
                    'id' => $estudiante->id,
                    'ru' => $estudiante->registro_universitario,
                    'nombres' => $estudiante->persona->nombres ?? '',
                    'apellidos' => trim(($estudiante->persona->ap_paterno ?? '') . ' ' . ($estudiante->persona->ap_materno ?? ''))
                ];
            });

        // Usando programacion_id de tu esquema DBML
        $foliosRegistrados = FolioExamen::where('programacion_id', $programacionId)->get();
        $foliosAsignados = [];

        foreach ($foliosRegistrados as $folio) {
            $posicion = (int) preg_replace('/[^0-9]/', '', $folio->codigo_folio);

            if ($posicion > 0) {
                $foliosAsignados[$posicion] = $folio;
            }
        }

        $totalCasillas = $estudiantesParalelo->count();

        return view('admin.folio_examens.plantilla', compact(
            'programacion',
            'estudiantesParalelo',
            'foliosAsignados',
            'totalCasillas'
        ));
    }
    public function vistaConsolidacion(ProgramacionExamen $programacion)
    {
        // Cargar los folios con los datos del estudiante y su persona biográfica
        $folios = $programacion->folios()->with('estudiante.persona')->get();

        // Retornar la vista Blade que creamos antes
        return view('admin.programacion_examenes.consolidar', compact('programacion', 'folios'));
    }
    public function generarMasivo(Request $request, int $programacionId)
    {
        try {
            $programacion = ProgramacionExamen::findOrFail($programacionId);

            if ($programacion->bloqueado) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta programación de examen está bloqueada. No se pueden modificar folios.'
                ], 403);
            }

            $foliosData = $request->input('folios', []); // [posicion => registro_universitario]
            $userId = Auth::id(); // Usuario administrador actual

            DB::transaction(function () use ($foliosData, $programacionId, $userId) {
                foreach ($foliosData as $posicion => $ru) {
                    $codigoFolio = 'FOLIO-' . str_pad($posicion, 5, '0', STR_PAD_LEFT);

                    if (!empty($ru)) {
                        $estudiante = Estudiante::where('registro_universitario', $ru)->first();

                        if ($estudiante) {
                            FolioExamen::updateOrCreate(
                                [
                                    'programacion_id' => $programacionId,
                                    'codigo_folio'    => $codigoFolio
                                ],
                                [
                                    'estudiante_id'          => $estudiante->id,
                                    'estado_folio'           => 'Foliado_Y_Separado',
                                    'registrado_por_user_id' => $userId // Auditoría de quién generó el folio
                                ]
                            );
                        }
                    } else {
                        // Si se vació la casilla en la grilla, eliminamos el folio previo
                        FolioExamen::where('programacion_id', $programacionId)
                            ->where('codigo_folio', $codigoFolio)
                            ->delete();
                    }
                }
            });

            return response()->json([
                'success' => true,
                'message' => '¡Foliación guardada masivamente con éxito!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $programacionExamenId = $request->get('programacion_examen_id');
        $programacionExamen = ProgramacionExamen::findOrFail($programacionExamenId);
        return view('admin.folios-examenes.create', compact('programacionExamen'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'programacion_id' => 'required|exists:programacion_examenes,id',
        ]);

        $data = $request->all();
        if (isset($data['programacion_examen_id']) && !isset($data['programacion_id'])) {
            $data['programacion_id'] = $data['programacion_examen_id'];
        }

        // Asignamos el usuario administrador que creó el registro
        $data['registrado_por_user_id'] = Auth::id();

        FolioExamen::create($data);

        return redirect()->route('admin.programacion-examenes.folios.index', $data['programacion_id'])
            ->with('success', 'Folio registrado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(FolioExamen $folioExamen)
    {
        return view('admin.folios-examenes.show', compact('folioExamen'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FolioExamen $folioExamen)
    {
        return view('admin.folios-examenes.edit', compact('folioExamen'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FolioExamen $folioExamen)
    {
        $request->validate([
            // Reglas de actualización
        ]);

        $folioExamen->update($request->all());

        return redirect()->route('admin.programacion-examenes.folios.index', $folioExamen->programacion_id)
            ->with('success', 'Folio actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FolioExamen $folioExamen)
    {
        $examenId = $folioExamen->programacion_id;
        $folioExamen->delete();

        return redirect()->route('admin.programacion-examenes.folios.index', $examenId)
            ->with('success', 'Folio eliminado correctamente.');
    }

    /**
     * Guarda masivamente la asignación de folios y estudiantes para el examen.
     */
}
