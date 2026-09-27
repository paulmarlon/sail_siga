@extends('adminlte::page')

@section('title', 'Portal Docente - Asignaturas')
@section('css')
    <style>
        .btn-xs {
            padding: 0.1rem 0.3rem !important;
            font-size: 0.7rem !important;
        }

        /* Asegura que los textos largos no rompan la tabla */
        .table td {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Fondo amarillo suave para celdas de exámenes ya calificados (en el TD) */
        .bg-warning-soft {
            background-color: #ffc400 !important;
        }

        /* Estilo personalizado para modalidad A Ciegas (Morado / Purple) */
        .btn-purple {
            background-color: #6f42c1 !important;
            border-color: #6f42c1 !important;
            color: #fff !important;
        }

        .btn-purple:hover {
            background-color: #59339d !important;
            border-color: #59339d !important;
            color: #fff !important;
        }

        /* Estética optimizada para los botones de notas y fechas verticales hacia arriba */
        .eval-link-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1px 2px;
            border-radius: 3px;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .eval-fecha-vertical {
            font-size: 0.58rem;
            letter-spacing: -0.5px;
            font-weight: 600;
            line-height: 1;
            margin-top: 1px;
            writing-mode: horizontal-tb;
            /* Asegura legibilidad vertical compacta */
        }
    </style>
@stop
@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0 text-dark font-weight-bold">
                <i class="fas fa-chalkboard-teacher me-2 text-primary"></i>Portal Docente - Asignaturas Asignadas
            </h1>
            <p class="text-muted mb-0 small">
                Gestione las calificaciones mediante la matriz de evaluaciones optimizada.
            </p>
        </div>
    </div>
@stop

@section('content')
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-3" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm mb-3" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body p-2">
            <table id="tablaOfertasDocente"
                class="table table-bordered table-striped table-hover align-middle w-100 mb-0 small">
                <thead class="thead-dark text-uppercase">
                    <tr>
                        <th style="width: 12%;">Carrera</th>
                        <th style="width: 25%;">Asignatura</th>
                        <th style="width: 18%;">Docente</th>
                        <th class="text-center" style="width: 8%;">T / P</th>
                        <th class="text-center" style="width: 12%;">Gestión</th>
                        <th class="text-center" style="width: 6%;">P1</th>
                        <th class="text-center" style="width: 6%;">P2</th>
                        <th class="text-center" style="width: 6%;">EF</th>
                        <th class="text-center" style="width: 7%;">2T</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ofertasAsignadas as $oferta)
                        @php
                            $exámenes = $oferta->programacionesExamen->keyBy('instancia');
                            $p1 = $exámenes->get('P1');
                            $p2 = $exámenes->get('P2');
                            $ef = $exámenes->get('EF');
                            $si = $exámenes->get('2T');

                            // Usando el atajo limpio de tu modelo:
                            $personaDocente = $oferta->docenteActual?->docente?->persona;
                            $nombreDocente = $personaDocente
                                ? "{$personaDocente->nombres} {$personaDocente->ap_paterno}"
                                : 'Sin Asignar';
                        @endphp
                        <tr>
                            <!-- Carrera (Usando la sigla para ultra compactar) -->
                            <td class="align-middle">
                                <span class="font-weight-bold text-dark"
                                    title="{{ $oferta->pensum->carrera->nombre ?? 'N/A' }}" data-toggle="tooltip">
                                    {{ $oferta->pensum->carrera->sigla ?? 'N/A' }}
                                </span>
                                <span class="text-muted d-block"
                                    style="font-size: 0.7rem;">{{ $oferta->pensum->grado->nombre ?? '' }}</span>
                            </td>

                            <!-- Asignatura (Sigla + Nombre truncado con tooltip) -->
                            <td class="align-middle">
                                <span class="text-primary font-weight-bold">
                                    [{{ $oferta->pensum->materia->sigla ?? '-' }}]
                                </span>
                                <span class="text-dark text-truncate d-inline-block align-bottom" style="max-width: 180px;"
                                    title="{{ $oferta->pensum->materia->nombre ?? 'N/A' }}" data-toggle="tooltip">
                                    {{ $oferta->pensum->materia->nombre ?? 'N/A' }}
                                </span>
                            </td>

                            <!-- Docente Asignado -->
                            <td class="align-middle text-truncate" style="max-width: 130px;" title="{{ $nombreDocente }}"
                                data-toggle="tooltip">
                                <i class="fas fa-user-tie text-muted me-1"></i> {{ $nombreDocente }}
                            </td>

                            <!-- Turno y Paralelo -->
                            <td class="text-center align-middle">
                                <span class="badge badge-light border">{{ $oferta->turno->nombre ?? 'S/T' }}</span>
                                <span class="badge badge-secondary">{{ $oferta->paralelo->nombre ?? 'S/P' }}</span>
                            </td>

                            <!-- Gestión y Periodo -->
                            <td class="text-center align-middle">
                                <span class="badge badge-light border text-truncate" style="max-width: 90px;"
                                    title="{{ $oferta->periodo->gestion->nombre ?? 'S/G' }} ({{ $oferta->periodo->nombre ?? '' }})"
                                    data-toggle="tooltip">
                                    {{ $oferta->periodo->gestion->nombre ?? 'S/G' }}
                                </span>
                            </td>

                            @foreach ([$p1, $p2, $ef, $si] as $eval)
                                @php
                                    $tieneNotasCompletas = false;
                                    $esACiegas = false;

                                    if ($eval) {
                                        $esACiegas = $eval->modalidad === 'a_ciegas';

                                        if ($esACiegas) {
                                            // Lógica para A Ciegas: Validamos a través de los folios
                                            $totalFolios = $eval->relationLoaded('folios')
                                                ? $eval->folios->count()
                                                : $eval->folios()->count();
                                            $foliosCalificados = $eval->relationLoaded('folios')
                                                ? $eval->folios->whereNotNull('nota')->count()
                                                : $eval->folios()->whereNotNull('nota')->count();

                                            $tieneNotasCompletas =
                                                $totalFolios > 0 && $foliosCalificados === $totalFolios;
                                        } else {
                                            // Lógica para Directa o Dictada: Mapeamos la instancia string a entero para 'nro_parcial'
                                            $nroParcialMap = [
                                                'P1' => 1,
                                                'P2' => 2,
                                                'EF' => 3,
                                                '2T' => 3,
                                            ];
                                            $nroParcialInt = $nroParcialMap[$eval->instancia] ?? 1;

                                            $ofertaEval = $eval->ofertaAcademica;
                                            if ($ofertaEval) {
                                                $totalMatriculados = $ofertaEval->relationLoaded('matriculaciones')
                                                    ? $ofertaEval->matriculaciones->count()
                                                    : $ofertaEval->matriculaciones()->count();

                                                $totalCalificados = \App\Models\CalificacionParcial::whereHas(
                                                    'matriculacion',
                                                    function ($q) use ($eval) {
                                                        $q->where('oferta_id', $eval->oferta_id);
                                                    },
                                                )
                                                    ->where('nro_parcial', $nroParcialInt)
                                                    ->whereNotNull('nota_parcial_calculada')
                                                    ->count();

                                                $tieneNotasCompletas =
                                                    $totalMatriculados > 0 && $totalCalificados === $totalMatriculados;
                                            }
                                        }
                                    }
                                @endphp

                                <!-- Celda de nota pintada en amarillo suave (warning-soft) si ya se completó -->
                                <td class="text-center align-middle p-1 {{ $tieneNotasCompletas ? 'bg-warning-soft' : '' }}"
                                    style="width: 75px; height: 45px;">
                                    @if ($eval)
                                        @if ($eval->bloqueado)
                                            <button class="btn btn-xs btn-danger w-100 py-0" title="Bloqueado por Admin"
                                                data-toggle="tooltip" disabled>
                                                <i class="fas fa-lock"></i>
                                            </button>
                                        @else
                                            <!-- Botón principal de notas (Morado si es a_ciegas, Verde/Warning si es directa) -->
                                            <a href="{{ route('docente.programacion.llenar-notas', $eval->id) }}"
                                                class="btn btn-xs {{ $esACiegas ? 'btn-purple' : ($tieneNotasCompletas ? 'btn-warning text-dark font-weight-bold' : 'btn-success') }} eval-link-btn w-100"
                                                title="{{ $esACiegas ? 'Modalidad A Ciegas' : ($tieneNotasCompletas ? 'Notas completadas' : 'Gestionar notas') }}"
                                                data-toggle="tooltip">
                                                <i
                                                    class="fas {{ $esACiegas ? 'fas fa-user-secret' : ($tieneNotasCompletas ? 'fa-check-circle' : 'fa-edit') }}"></i>
                                                <span class="eval-fecha-vertical">
                                                    {{ $eval->fecha_programada ? \Carbon\Carbon::parse($eval->fecha_programada)->format('d/m') : '-' }}
                                                </span>
                                            </a>
                                        @endif
                                    @else
                                        <span class="text-muted" style="font-size: 0.75rem;">-</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop

@section('js')
@section('plugins.Datatables', true)

<script>
    $(document).ready(function() {
        $('#tablaOfertasDocente').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json"
            },
            "responsive": true,
            "autoWidth": false,
            "pageLength": 10,
            "columnDefs": [{
                "orderable": false,
                "targets": [5, 6, 7, 8] // Columnas de P1, P2, EF, 2T sin ordenamiento
            }]
        });

        // Activar tooltips de Bootstrap flotantes
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>
@include('admin.alertas')
@stop
