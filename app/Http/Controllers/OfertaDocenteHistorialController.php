<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OfertaDocenteHistorial;
use App\Models\OfertaAcademica;
use App\Models\Personal;
use App\Models\Estado;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

class OfertaDocenteHistorialController extends Controller
{
    /**
     * Muestra el detalle de la oferta académica, el docente vigente y su historial completo.
     */
    public function show(OfertaAcademica $oferta)
    {
        $oferta->load([
            'periodo.gestion',
            'paralelo',
            'turno',
            'pensum.materia',
            'pensum.carrera',
            'historialDocentes.docente.persona',
            'historialDocentes.estado',
            'historialDocentes.registradoPor'
        ]);

        // Usamos whereIn para filtrar por múltiples tipos válidos
        $docentesDisponibles = Personal::with('persona')
            ->whereIn('tipo', ['docente', 'planta'])
            ->get();

        return view('admin.oferta_academica.docentes', compact('oferta', 'docentesDisponibles'));
    }

    /**
     * Asigna un nuevo docente o realiza un cambio cerrando el anterior contrato vigente.
     */
    public function store(Request $request, OfertaAcademica $oferta)
    {
        $request->validate([
            'docente_id' => 'required|exists:personals,id',
            'fecha_inicio' => 'required|date',
            'contrato_id' => 'nullable|string|max:50',
            'motivo_cambio' => 'nullable|string|max:255',
        ]);

        $ofertaId = $oferta->id;

        DB::transaction(function () use ($request, $ofertaId) {

            $estadoVigenteId = Estado::where('slug', 'vigente')->value('id');
            $estadoConcluidoId = Estado::where('slug', 'concluido')->value('id');

            // 1. Cierra lógicamente el contrato del docente anterior si existía uno activo
            OfertaDocenteHistorial::where('oferta_id', $ofertaId)
                ->whereNull('fecha_fin')
                ->update([
                    'fecha_fin' => now(),
                    'estado_id' => $estadoConcluidoId ?? $estadoVigenteId
                ]);

            // 2. Registra el nuevo periodo/historial para el docente actual
            OfertaDocenteHistorial::create([
                'oferta_id' => $ofertaId,
                'docente_id' => $request->docente_id,
                'contrato_id' => $request->contrato_id,
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_fin' => null,
                'motivo_cambio' => $request->motivo_cambio ?? 'Asignación de cátedra inicial',
                'estado_id' => $estadoVigenteId,
                'registrado_por_user_id' => \Illuminate\Support\Facades\Auth::id(),
            ]);
        });

        // Usamos la Facade URL que Intelephense tiene perfectamente tipada
        $previousUrl = URL::previous();

        $query = parse_url($previousUrl, PHP_URL_QUERY);
        $previousQuery = is_string($query) ? $query : '';

        $redirectUrl = route('admin.oferta-academica.index') . ($previousQuery ? '?' . $previousQuery : '');

        return redirect($redirectUrl)
            ->with('mensaje', 'Docente asignado correctamente a la oferta académica.')
            ->with('icono', 'success');
    }

    /**
     * Concluye anticipadamente una asignación de historial docente.
     */
    public function concluir(OfertaDocenteHistorial $historial)
    {
        return DB::transaction(function () use ($historial) {
            $estadoConcluidoId = Estado::where('slug', 'concluido')->value('id');

            $historial->update([
                'fecha_fin' => now(),
                'estado_id' => $estadoConcluidoId ?? $historial->estado_id
            ]);

            return redirect()->back()
                ->with('mensaje', 'La asignación del docente ha sido concluida formalmente.')
                ->with('icono', 'info');
        });
    }
}
