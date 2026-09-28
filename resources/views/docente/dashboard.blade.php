@extends('adminlte::page')

@section('title', 'Portal Docente - Asignaturas')
@section('css')
    <style>
        .btn-xs {
            padding: 0.1rem 0.3rem !important;
            font-size: 0.7rem !important;
        }

        .table td {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .bg-warning-soft {
            background-color: #ffc400 !important;
        }

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

    {{-- Definimos de forma ordenada las columnas institucionales permitidas (soporta múltiples TP dinámicamente) --}}
    @php
        $columnasEvaluacion = ['P1', 'P2', 'P3', 'EF', '2T', 'TP1', 'TP2', 'TP3'];
        $totalColumnasEval = count($columnasEvaluacion);
    @endphp

    <div class="card shadow-sm border-0">
        <div class="card-body p-2">
            <table id="tablaOfertasDocente"
                class="table table-bordered table-striped table-hover align-middle w-100 mb-0 small">
                <thead class="thead-dark text-uppercase">
                    <tr>
                        <th style="width: 11%;">Carrera</th>
                        <th style="width: 23%;">Asignatura</th>
                        <th style="width: 16%;">Docente</th>
                        <th class="text-center" style="width: 7%;">T / P</th>
                        <th class="text-center" style="width: 11%;">Gestión</th>

                        {{-- Cabeceras dinámicas basadas en el array de columnas --}}
                        @foreach ($columnasEvaluacion as $instanciaCol)
                            <th class="text-center" style="width: 5%;">{{ $instanciaCol }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($ofertasAsignadas as $oferta)
                        @php
                            // Indexamos las programaciones por su instancia en mayúsculas de manera segura
                            $exámenes = $oferta->programacionesExamen->keyBy(function ($item) {
                                return strtoupper(trim($item->instancia));
                            });

                            $personaDocente = $oferta->docenteActual?->docente?->persona;
                            $nombreDocente = $personaDocente
                                ? "{$personaDocente->nombres} {$personaDocente->ap_paterno}"
                                : 'Sin Asignar';
                        @endphp
                        <tr>
                            <!-- Carrera -->
                            <td class="align-middle">
                                <span class="font-weight-bold text-dark"
                                    title="{{ $oferta->pensum->carrera->nombre ?? 'N/A' }}" data-toggle="tooltip">
                                    {{ $oferta->pensum->carrera->sigla ?? 'N/A' }}
                                </span>
                                <span class="text-muted d-block"
                                    style="font-size: 0.7rem;">{{ $oferta->pensum->grado->nombre ?? '' }}</span>
                            </td>

                            <!-- Asignatura -->
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

                            {{-- Iteramos dinámicamente sobre cada columna de evaluación institucional --}}
                            @foreach ($columnasEvaluacion as $instanciaCol)
                                @php
                                    $eval = $exámenes->get($instanciaCol);
                                    $tieneNotasCompletas = false;
                                    $esACiegas = false;

                                    if ($eval) {
                                        $esACiegas = $eval->modalidad === 'a_ciegas';

                                        if ($esACiegas) {
                                            $totalFolios = $eval->relationLoaded('folios')
                                                ? $eval->folios->count()
                                                : $eval->folios()->count();
                                            $foliosCalificados = $eval->relationLoaded('folios')
                                                ? $eval->folios->whereNotNull('nota')->count()
                                                : $eval->folios()->whereNotNull('nota')->count();

                                            $tieneNotasCompletas =
                                                $totalFolios > 0 && $foliosCalificados === $totalFolios;
                                        } else {
                                            // Mapeo inteligente del número de parcial según la instancia actual
                                            $nroParcialInt = 1;
                                            if (str_contains($instanciaCol, 'P2')) {
                                                $nroParcialInt = 2;
                                            }
                                            if (
                                                str_contains($instanciaCol, 'P3') ||
                                                str_contains($instanciaCol, 'EF') ||
                                                str_contains($instanciaCol, '2T')
                                            ) {
                                                $nroParcialInt = 3;
                                            }
                                            if (str_contains($instanciaCol, 'TP')) {
                                                preg_match('/\d+/', $instanciaCol, $matches);
                                                $nroParcialInt = isset($matches[0]) ? (int) $matches[0] : 1;
                                            }

                                            $nroParcialInt = $eval->nro_parcial ?? $nroParcialInt;

                                            $ofertaEval = $eval->ofertaAcademica;
                                            if ($ofertaEval) {
                                                $totalMatriculados = $ofertaEval->relationLoaded('matriculaciones')
                                                    ? $ofertaEval->matriculaciones->count()
                                                    : $ofertaEval->matriculaciones()->count();

                                                $tipoCompBusqueda = str_contains($instanciaCol, 'TP')
                                                    ? 'trabajo_practico'
                                                    : 'examen';

                                                $totalCalificados = \App\Models\CalificacionDetalle::whereHas(
                                                    'calificacionParcial.matriculacion',
                                                    function ($q) use ($eval) {
                                                        $q->where('oferta_id', $eval->oferta_id);
                                                    },
                                                )
                                                    ->whereHas('calificacionParcial', function ($q) use (
                                                        $nroParcialInt,
                                                    ) {
                                                        $q->where('nro_parcial', $nroParcialInt);
                                                    })
                                                    ->where('tipo_componente', $tipoCompBusqueda)
                                                    ->whereNotNull('nota')
                                                    ->count();

                                                $tieneNotasCompletas =
                                                    $totalMatriculados > 0 && $totalCalificados === $totalMatriculados;
                                            }
                                        }
                                    }
                                @endphp

                                <td class="text-center align-middle p-1 {{ $tieneNotasCompletas ? 'bg-warning-soft' : '' }}"
                                    style="height: 45px;">
                                    @if ($eval)
                                        @if ($eval->bloqueado)
                                            <button class="btn btn-xs btn-danger w-100 py-0" title="Bloqueado por Admin"
                                                data-toggle="tooltip" disabled>
                                                <i class="fas fa-lock"></i>
                                            </button>
                                        @else
                                            <a href="{{ route('docente.programacion.llenar-notas', $eval->id) }}"
                                                class="btn btn-xs {{ $esACiegas ? 'btn-purple' : ($tieneNotasCompletas ? 'btn-warning text-dark font-weight-bold' : 'btn-success') }} eval-link-btn w-100"
                                                title="{{ $instanciaCol }}: {{ $esACiegas ? 'Modalidad A Ciegas' : ($tieneNotasCompletas ? 'Notas completadas' : 'Gestionar notas') }}"
                                                data-toggle="tooltip">
                                                <i
                                                    class="fas {{ $esACiegas ? 'fas fa-user-secret' : ($tieneNotasCompletas ? 'fas fa-check-circle' : 'fas fa-edit') }}"></i>
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
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@stop

@section('js')
@section('plugins.Datatables', true)

<script>
    $(document).ready(function() {
        if ($.fn.DataTable.isDataTable('#tablaOfertasDocente')) {
            $('#tablaOfertasDocente').DataTable().destroy();
        }

        // Generamos dinámicamente los índices de las columnas de evaluación (comienzan a partir de la columna 5)
        let totalColsEval = @json($totalColumnasEval);
        let nonOrderableTargets = [];
        for (let i = 5; i < 5 + totalColsEval; i++) {
            nonOrderableTargets.push(i);
        }

        $('#tablaOfertasDocente').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json",
                "emptyTable": "No se encontraron ofertas académicas asignadas."
            },
            "responsive": true,
            "autoWidth": false,
            "pageLength": 10,
            "columnDefs": [{
                "orderable": false,
                "targets": nonOrderableTargets
            }]
        });

        $('[data-toggle="tooltip"]').tooltip();
    });
</script>
@include('admin.alertas')
@stop
