@extends('adminlte::page')

@section('title', 'Matriculación de Materias')

@section('css')
    <style>
        #tabla-matriculaciones th,
        #tabla-matriculaciones td {
            padding: 0.35rem 0.5rem !important;
            /* Más compacto e interlineado ajustado */
            vertical-align: middle !important;
            font-size: 0.82rem;
        }
    </style>
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fas fa-book-open text-primary mr-2"></i> Gestión de Matriculación de Materias</h1>
            <p class="text-muted mb-0">Control académico de asignación de oferta estudiantil por periodo.</p>
        </div>
        <div>
            <a href="{{ route('admin.matriculacion-materias.papelera') }}" class="btn btn-secondary btn-sm mr-2">
                <i class="fas fa-trash-restore mr-1"></i> Papelera
            </a>
            <a href="{{ route('admin.matriculacion-materias.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus-circle mr-1"></i> Nueva Matriculación
            </a>
        </div>
    </div>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="icon fas fa-check"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="icon fas fa-ban"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- BARRA DE FILTRADO MÚLTIPLE -->
    <div class="card card-outline card-primary shadow-sm mb-3">
        <div class="card-header py-2">
            <h3 class="card-title font-weight-bold" style="font-size: 0.9rem;">
                <i class="fas fa-filter mr-1 text-primary"></i> Filtros de Búsqueda Avanzada
            </h3>
        </div>
        <div class="card-body py-2 bg-light">
            <form method="GET" action="{{ route('admin.matriculacion-materias.index') }}" id="form-filtros">
                <div class="form-row align-items-end">

                    <!-- Filtro por Carrera -->
                    <div class="col-md-3 mb-2">
                        <label class="small font-weight-bold text-secondary mb-1">Carrera:</label>
                        <select name="carrera_id" id="carrera_id" class="form-control form-control-sm">
                            <option value="">-- Todas las Carreras --</option>
                            @foreach ($carreras as $car)
                                <option value="{{ $car->id }}"
                                    {{ request('carrera_id') == $car->id ? 'selected' : '' }}>
                                    {{ $car->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filtro por Grado / Semestre -->
                    <div class="col-md-2 mb-2">
                        <label class="small font-weight-bold text-secondary mb-1">Grado / Semestre:</label>
                        <select name="grado_id" id="grado_id" class="form-control form-control-sm">
                            <option value="">-- Todos --</option>
                            @foreach ($grados as $gra)
                                <option value="{{ $gra->id }}" {{ request('grado_id') == $gra->id ? 'selected' : '' }}>
                                    {{ $gra->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filtro por Periodo Académico -->
                    <div class="col-md-3 mb-2">
                        <label class="small font-weight-bold text-secondary mb-1">Periodo Académico:</label>
                        <select name="periodo_id" id="periodo_id" class="form-control form-control-sm">
                            <option value="">-- Seleccione Periodo --</option>
                            @foreach ($periodos as $per)
                                <option value="{{ $per->id }}"
                                    {{ request('periodo_id') == $per->id ? 'selected' : '' }}>
                                    {{ $per->nombre_completo ?? $per->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filtro por Turno -->
                    <div class="col-md-2 mb-2">
                        <label class="small font-weight-bold text-secondary mb-1">Turno:</label>
                        <select name="turno_id" id="turno_id" class="form-control form-control-sm">
                            <option value="">-- Todos --</option>
                            @foreach ($turnos as $tur)
                                <option value="{{ $tur->id }}"
                                    {{ request('turno_id') == $tur->id ? 'selected' : '' }}>
                                    {{ $tur->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filtro por Paralelo -->
                    <div class="col-md-2 mb-2">
                        <label class="small font-weight-bold text-secondary mb-1">Paralelo:</label>
                        <select name="paralelo_id" id="paralelo_id" class="form-control form-control-sm">
                            <option value="">-- Todos --</option>
                            @foreach ($paralelos as $par)
                                <option value="{{ $par->id }}"
                                    {{ request('paralelo_id') == $par->id ? 'selected' : '' }}>
                                    {{ $par->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filtro por Texto libre (Estudiante / CI / RU) -->
                    <div class="col-md-6 mb-2">
                        <label class="small font-weight-bold text-secondary mb-1">Estudiante (Nombre / CI / RU):</label>
                        <input type="text" name="busqueda" id="busqueda" class="form-control form-control-sm"
                            value="{{ request('busqueda') }}" placeholder="Ej. Juan Pérez, CI o Registro Universitario...">
                    </div>

                    <!-- Botones de Acción de Filtro -->
                    <div class="col-md-4 mb-2 d-flex">
                        <button type="submit" class="btn btn-primary btn-sm btn-block font-weight-bold mr-1">
                            <i class="fas fa-search mr-1"></i> Filtrar
                        </button>
                        <a href="{{ route('admin.matriculacion-materias.index') }}" class="btn btn-secondary btn-sm"
                            title="Limpiar filtros">
                            <i class="fas fa-sync-alt"></i> Limpiar
                        </a>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <!-- TABLA DE RESULTADOS -->
    @php
        $hayFiltrosActivos =
            !empty(request('periodo_id')) ||
            !empty(request('carrera_id')) ||
            !empty(request('grado_id')) ||
            !empty(request('turno_id')) ||
            !empty(request('paralelo_id')) ||
            !empty(request('busqueda'));
    @endphp

    <div id="card-resultados" class="card card-outline card-success shadow-sm {{ $hayFiltrosActivos ? '' : 'd-none' }}">
        <div class="card-header py-2">
            <h3 class="card-title font-weight-bold" style="font-size: 0.95rem;">
                Listado de Estudiantes Matriculados ({{ isset($matriculaciones) ? $matriculaciones->count() : 0 }}
                registros)
            </h3>
        </div>
        <div class="card-body p-2">
            <table id="tabla-matriculaciones"
                class="table table-bordered table-striped table-hover dt-responsive nowrap text-nowrap" style="width:100%">
                <thead class="thead-dark">
                    <tr>
                        <th class="notexport" style="width: 35px;">ID</th>
                        <th>Estudiante (CI / RU)</th>
                        <th>Carrera / Semestre</th>
                        <th class="text-center">Turno / Paralelo</th>
                        <th>Periodo Académico</th>
                        <th class="text-center">Materias</th>
                        <th class="notexport text-center" style="width: 100px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @if (isset($matriculaciones))
                        @foreach ($matriculaciones as $mat)
                            @php
                                $periodoObj = $mat->oferta->periodo ?? null;
                                $carreraNombre = $mat->oferta->pensum->carrera->nombre ?? 'S/C';
                                $gradoNombre = $mat->oferta->pensum->grado->nombre ?? 'S/G';
                                $turnoNombre = $mat->oferta->turno->nombre ?? 'S/T';
                                $paraleloNombre = $mat->oferta->paralelo->nombre ?? 'S/P';
                            @endphp
                            <tr>
                                <td class="font-weight-bold text-center">{{ $mat->estudiante_id }}</td>
                                <td>
                                    <span class="font-weight-bold text-dark">
                                        {{ $mat->estudiante->persona->ap_paterno ?? '' }}
                                        {{ $mat->estudiante->persona->ap_materno ?? '' }}
                                        {{ $mat->estudiante->persona->nombres ?? '' }}
                                    </span><br>
                                    <small class="text-muted">
                                        CI: {{ $mat->estudiante->persona->ci ?? 'S/C' }} | RU:
                                        {{ $mat->estudiante->registro_universitario ?? 'S/RU' }}
                                    </small>
                                </td>
                                <td>
                                    <span class="font-weight-bold text-secondary">{{ $carreraNombre }}</span><br>
                                    <small class="text-muted"><i
                                            class="fas fa-graduation-cap mr-1"></i>{{ $gradoNombre }}</small>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-light border px-1">{{ $turnoNombre }}</span>
                                    <span class="badge badge-light border px-1">P: {{ $paraleloNombre }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-info px-2 py-1">
                                        {{ $periodoObj->nombre ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-success px-2 py-1" style="font-size: 0.8rem;">
                                        {{ $mat->total_materias }} Mat.
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <!-- Botón Ver Carga -->
                                        <a href="{{ route('admin.matriculacion-materias.show', [$mat->estudiante_id, $periodoObj->id ?? 0]) }}"
                                            class="btn btn-default btn-xs px-2" title="Ver Carga Académica">
                                            <i class="fas fa-eye text-primary"></i>
                                        </a>
                                        <!-- Botón Editar Grupo -->
                                        <a href="{{ route('admin.matriculacion-materias.edit-group', [
                                            'periodo_id' => $periodoObj->id ?? null,
                                            'carrera_id' => $mat->oferta->pensum->carrera_id ?? null,
                                            'grado_id' => $mat->oferta->pensum->grado_id ?? null,
                                            'turno_id' => $mat->oferta->turno_id ?? null,
                                            'paralelo_id' => $mat->oferta->paralelo_id ?? null,
                                        ]) }}"
                                            class="btn btn-warning btn-xs px-2"
                                            title="Editar grupo completo con filtros similares">
                                            <i class="fas fa-users-cog text-dark"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(function() {
            var table = $('#tabla-matriculaciones').DataTable({
                responsive: true,
                autoWidth: false,
                pageLength: 10,
                order: [],
                columnDefs: [{
                    // Columna 0 (ID): La convertimos en un contador correlativo visual
                    targets: 0,
                    render: function(data, type, row, meta) {
                        return meta.row + 1 + meta.settings._iDisplayStart;
                    }
                }],
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "Todos"]
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                },
                dom: '<"row mx-0 border-bottom py-2"<"col-md-4"B><"col-md-3"l><"col-md-5"f>>rt<"row mx-0 pt-2 align-items-center"<"col-md-6"i><"col-md-6 d-flex justify-content-end"p>>',
                buttons: [{
                        extend: 'copy',
                        text: '<i class="fas fa-copy"></i>',
                        className: 'btn btn-secondary btn-sm btn-flat',
                        titleAttr: 'Copiar',
                        exportOptions: {
                            columns: ':not(.notexport)'
                        }
                    },
                    {
                        extend: 'excel',
                        text: '<i class="fas fa-file-excel"></i>',
                        className: 'btn btn-success btn-sm btn-flat',
                        titleAttr: 'Excel',
                        exportOptions: {
                            columns: ':not(.notexport)'
                        }
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="fas fa-file-pdf"></i>',
                        className: 'btn btn-danger btn-sm btn-flat',
                        titleAttr: 'PDF',
                        exportOptions: {
                            columns: ':not(.notexport)'
                        }
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print"></i>',
                        className: 'btn btn-info btn-sm btn-flat',
                        titleAttr: 'Imprimir',
                        exportOptions: {
                            columns: ':not(.notexport)'
                        }
                    },
                    {
                        extend: 'colvis',
                        text: '<i class="fas fa-columns"></i>',
                        className: 'btn btn-dark btn-sm btn-flat',
                        titleAttr: 'Columnas'
                    }
                ],
                initComplete: function() {
                    $('.dataTables_paginate ul.pagination').addClass('pagination-sm');
                }
            });

            // Validación al enviar el formulario para exigir al menos un filtro activo
            $('#form-filtros').on('submit', function(e) {
                var periodo = $('#periodo_id').val();
                var carrera = $('#carrera_id').val();
                var grado = $('#grado_id').val();
                var turno = $('#turno_id').val();
                var paralelo = $('#paralelo_id').val();
                var busqueda = $('#busqueda').val().trim();

                if (!periodo && !carrera && !grado && !turno && !paralelo && !busqueda) {
                    e.preventDefault();
                    alert('Por favor, selecciona al menos un filtro o ingresa un criterio de búsqueda.');
                    $('#periodo_id').focus();
                }
            });
        });
    </script>
@stop
