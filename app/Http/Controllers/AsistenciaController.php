<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\OfertaAcademica;
use App\Models\MatriculacionMateria;
use App\Models\Asistencia;
use App\Models\Estado;
use App\Models\Personal;

class AsistenciaController extends Controller
{
    public function index(Request $request)
    {
        // Usamos Auth::user() de forma segura
        $user = Auth::user();

        $ofertas = collect();
        $personal = null;

        if ($user && $user->persona_id) {
            // Encontramos el registro de 'personal' correspondiente al usuario logueado
            $personal = Personal::where('persona_id', $user->persona_id)->first();
        }

        // Si es docente (o personal registrado), filtramos solo las ofertas asignadas a él
        if ($personal) {
            $ofertas = OfertaAcademica::whereHas('historialDocentes', function ($query) use ($personal) {
                $query->where('docente_id', $personal->id)
                    ->whereNull('deleted_at');
            })
                ->with([
                    'pensum.materia',
                    'pensum.grado',
                    'paralelo',
                    'turno',
                    'periodo'
                ])
                ->get();
        } else {
            // Opcional: Si el usuario es administrador general y quieres que vea todo:
            // $ofertas = OfertaAcademica::with(['pensum.materia', 'pensum.grado', 'paralelo', 'turno', 'periodo'])->get();
        }

        // Obtenemos los estados
        $estados = Estado::all();

        $ofertaId = $request->input('oferta_id');
        $fecha = $request->input('fecha', date('Y-m-d'));

        $matriculados = collect();

        // Si seleccionó una oferta y fecha, buscamos los estudiantes matriculados
        // Si seleccionó una oferta y fecha, buscamos los estudiantes matriculados ordenados alfabéticamente
        if ($ofertaId) {
            $matriculados = MatriculacionMateria::with([
                'estudiante.persona',
                'asistencias' => function ($query) use ($fecha) {
                    $query->where('fecha', $fecha);
                }
            ])
                ->from('matriculacion_materias')
                ->where('oferta_id', $ofertaId)
                ->join('estudiantes', 'matriculacion_materias.estudiante_id', '=', 'estudiantes.id')
                ->join('personas', 'estudiantes.persona_id', '=', 'personas.id') // Cambiado a plural: 'personas'
                ->orderBy('personas.ap_paterno', 'asc')
                ->orderBy('personas.ap_materno', 'asc')
                ->orderBy('personas.nombres', 'asc')
                ->select('matriculacion_materias.*')
                ->get();
        }

        return view('admin.asistencias.index', compact('ofertas', 'estados', 'matriculados', 'ofertaId', 'fecha'));
    }

    public function storeMasiva(Request $request)
    {
        $request->validate([
            'oferta_id' => 'required|exists:oferta_academicas,id',
            'fecha' => 'required|date',
            'asistencias' => 'required|array',
        ]);

        $fecha = $request->input('fecha');
        $asistencias = $request->input('asistencias', []);

        foreach ($asistencias as $item) {
            Asistencia::updateOrCreate(
                [
                    'matriculacion_id' => $item['matriculacion_id'],
                    'fecha' => $fecha,
                ],
                [
                    'estado_id' => $item['estado_id'],
                    'observacion' => $item['observacion'] ?? null,
                ]
            );
        }

        return redirect()->route('admin.asistencias.index', [
            'oferta_id' => $request->input('oferta_id'),
            'fecha' => $fecha
        ])->with('success', '¡Asistencia registrada y guardada exitosamente!');
    }
    public function reporteAdmin(Request $request)
    {
        $ofertaId = $request->input('oferta_id');
        $ofertas = \App\Models\OfertaAcademica::with(['pensum.materia', 'pensum.grado', 'paralelo', 'turno'])->get();

        $resumenAsistencias = collect();
        $totalEstudiantes = 0;

        if ($ofertaId) {
            $resumenAsistencias = \App\Models\MatriculacionMateria::with(['estudiante.persona'])
                ->where('oferta_id', $ofertaId)
                ->join('estudiantes', 'matriculacion_materias.estudiante_id', '=', 'estudiantes.id')
                ->join('personas', 'estudiantes.persona_id', '=', 'personas.id')
                ->orderBy('personas.ap_paterno', 'asc')
                ->orderBy('personas.ap_materno', 'asc')
                ->orderBy('personas.nombres', 'asc')
                ->select('matriculacion_materias.*')
                ->get();

            foreach ($resumenAsistencias as $mat) {
                // Supongamos que el estado_id 1 es Presente (ajústalo si tu ID es otro)
                $asistenciasCount = \App\Models\Asistencia::where('matriculacion_id', $mat->id)
                    ->where('estado_id', 1)
                    ->count();

                // Contamos como ausencia cualquier registro donde el estado_id NO sea 1 (ej: 2 = Ausente, 3 = Licencia, etc.)
                $faltasCount = \App\Models\Asistencia::where('matriculacion_id', $mat->id)
                    ->where('estado_id', '!=', 1)
                    ->count();

                $totalClases = $asistenciasCount + $faltasCount;
                $porcentaje = $totalClases > 0 ? round(($asistenciasCount / $totalClases) * 100, 1) : 0;

                $mat->presentes = $asistenciasCount;
                $mat->ausencias = $faltasCount;
                $mat->porcentaje = $porcentaje;
            }

            $totalEstudiantes = $resumenAsistencias->count();
        }

        return view('admin.asistencias.reporte', compact('ofertas', 'ofertaId', 'resumenAsistencias', 'totalEstudiantes'));
    }
}
