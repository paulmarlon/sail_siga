@extends('adminlte::page')

@section('title', 'Auditoría y Reportes de Asistencia')

@section('content_header')
    <h1>Auditoría Académica: Control de Asistencias</h1>
@stop

@section('content')
    <!-- Filtro de Búsqueda Global -->
    <div class="card card-outline card-info shadow-sm">
        <div class="card-body py-3">
            <form action="{{ route('admin.asistencias.reporte') }}" method="GET" class="row align-items-end">
                <div class="col-md-9 form-group mb-0">
                    <label class="small text-muted font-weight-bold">SELECCIONAR OFERTA ACADÉMICA A AUDITAR:</label>
                    <select name="oferta_id" class="form-control form-control-sm select2" required>
                        <option value="">-- Seleccione una materia para auditar --</option>
                        @foreach ($ofertas as $of)
                            @php
                                $sigla = $of->pensum->materia->sigla ?? 'S/S';
                                $materia = $of->pensum->materia->nombre ?? 'Desconocida';
                                $grado = $of->pensum->grado->nombre ?? '';
                                $paralelo = $of->paralelo->nombre ?? 'N/A';
                            @endphp
                            <option value="{{ $of->id }}" {{ $ofertaId == $of->id ? 'selected' : '' }}>
                                [{{ $sigla }}] {{ $materia }} — {{ $grado }} | Paralelo:
                                {{ $paralelo }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 form-group mb-0">
                    <button type="submit" class="btn btn-info btn-sm btn-block">
                        <i class="fas fa-chart-bar mr-1"></i> Generar Reporte
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if ($ofertaId)
        @php
            // Calculamos el promedio global de la oferta si hay estudiantes
            $totalEst = $resumenAsistencias->count();
            $promedioGeneral = $totalEst > 0 ? round($resumenAsistencias->avg('porcentaje'), 1) : 0;
        @endphp

        <!-- Widgets Estadísticos Superiores (Estándar de Grandes Sistemas) -->
        <div class="row">
            <div class="col-lg-4 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $totalEst }}</h3>
                        <p>Total Estudiantes Inscritos</p>
                    </div>
                    <div class="icon"><i class="fas fa-user-graduate"></i></div>
                </div>
            </div>
            <div class="col-lg-4 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $promedioGeneral }}%</h3>
                        <p>Asistencia Promedio</p>
                    </div>
                    <div class="icon"><i class="fas fa-percentage"></i></div>
                </div>
            </div>
            <div class="col-lg-4 col-12">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>Ver Historial</h3>
                        <p>Exportar PDF / Excel</p>
                    </div>
                    <div class="icon"><i class="fas fa-file-pdf"></i></div>
                </div>
            </div>
        </div>

        <!-- Tabla de Consolidado por Estudiante -->
        <div class="card card-outline card-secondary shadow-sm">
            <div class="card-header py-2">
                <h3 class="card-title font-weight-bold text-sm m-0">
                    <i class="fas fa-clipboard-list mr-1"></i> Resumen Consolidado de Asistencia por Alumno
                </h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-bordered text-sm mb-0">
                        <thead class="bg-light text-secondary">
                            <tr>
                                <th class="text-center" style="width: 40px;">#</th>
                                <th>Estudiante</th>
                                <th class="text-center" style="width: 100px;">Presentes</th>
                                <th class="text-center" style="width: 100px;">Ausencias</th>
                                <th class="text-center" style="width: 120px;">% Asistencia</th>
                                <th class="text-center" style="width: 120px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($resumenAsistencias as $index => $mat)
                                @php
                                    $p = $mat->estudiante->persona ?? null;
                                    $nombre = $p ? "{$p->ap_paterno} {$p->ap_materno}, {$p->nombres}" : 'S/N';

                                    // Definimos color dinámico del badge según el porcentaje de asistencia
                                    $porc = $mat->porcentaje ?? 0;
                                    $badgeClass =
                                        $porc >= 75
                                            ? 'badge-success'
                                            : ($porc >= 50
                                                ? 'badge-warning'
                                                : 'badge-danger');
                                @endphp
                                <tr>
                                    <td class="text-center font-weight-bold text-muted">{{ $index + 1 }}</td>
                                    <td>
                                        <span class="font-weight-semibold text-dark">{{ $nombre }}</span>
                                        <span class="text-muted d-block" style="font-size: 75%;">CI: {{ $p->ci ?? 'S/C' }}
                                            | RU: {{ $mat->estudiante->registro_universitario ?? 'S/RU' }}</span>
                                    </td>
                                    <td class="text-center text-success font-weight-bold align-middle">
                                        {{ $mat->presentes ?? 0 }}</td>
                                    <td class="text-center text-danger font-weight-bold align-middle">
                                        {{ $mat->ausencias ?? 0 }}</td>
                                    <td class="text-center align-middle">
                                        <span class="badge {{ $badgeClass }}">{{ $porc }} %</span>
                                    </td>
                                    <td class="text-center align-middle">
                                        <button class="btn btn-xs btn-outline-primary" title="Ver kardex detallado">
                                            <i class="fas fa-eye"></i> Detalle
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No hay estudiantes matriculados
                                        en esta oferta académica.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
@stop
