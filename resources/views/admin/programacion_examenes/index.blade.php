@extends('adminlte::page')

@section('title', 'Gestión de Exámenes')

@section('css')
    <style>
        .card-scroll {
            height: 75vh;
            display: flex;
            flex-direction: column;
        }

        .card-body-scroll {
            flex: 1;
            overflow-y: auto;
        }

        #tabla-programacion th,
        #tabla-programacion td {
            padding: 0.30rem 0.4rem !important;
            vertical-align: middle !important;
            font-size: 0.78rem;
        }

        /* Transición suave para el colapso lateral */
        .col-transicion {
            transition: all 0.3s ease-in-out;
        }

        /* Estilo para rotar el texto de la fecha verticalmente hacia arriba */
        .fecha-vertical {
            writing-mode: vertical-lr;
            transform: rotate(180deg);
            text-orientation: mixed;
            font-size: 0.7rem;
            white-space: nowrap;
            display: inline-block;
            padding: 2px 0;
        }

        /* Asegurar que las celdas de las instancias tengan espacio para la rotación */
        .columna-instancia {
            text-align: center;
            vertical-align: middle !important;
            height: 60px;
        }
    </style>
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fas fa-calendar-alt text-primary mr-2"></i> Programación y Calendario de Exámenes</h1>
            <p class="text-muted mb-0">Control de instancias evaluativas por oferta académica y periodo activo.</p>
        </div>
        <div class="d-flex align-items-center">
            <!-- BOTÓN PRINCIPAL DE CREACIÓN MASIVA EN LA CABECERA -->
            <button type="submit" form="form-masivo" id="btn-header-crear"
                class="btn btn-success btn-sm shadow-sm font-weight-bold mr-2 action-btn"
                data-action="{{ route('admin.programacion-examenes.create') }}" data-method="GET">
                <i class="fas fa-plus-circle mr-1"></i> Programar Seleccionados
            </button>
            <!-- Botón de acceso a la Papelera -->
            <a href="{{ route('admin.programacion-examenes.papelera') }}"
                class="btn btn-outline-secondary btn-sm shadow-sm font-weight-bold">
                <i class="fas fa-trash-restore mr-1 text-danger"></i> Ver Papelera
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

    <!-- FORMULARIO ENVOLVENTE GENERAL -->
    <form action="{{ route('admin.programacion-examenes.create') }}" method="GET" id="form-masivo">
        @csrf
        <!-- Contenedor dinámico para manejar method spoofing (como DELETE o PUT) cuando se requiera -->
        <div id="method-spoofing-container"></div>

        <!-- ================= BARRA ESTRATÉGICA DE CONTROL DE PANELES ================= -->
        <div class="row mb-2">
            <div class="col-12 d-flex justify-content-between align-items-center bg-light p-2 rounded border shadow-sm">
                <div>
                    <button type="button" id="btn-toggle-filtros"
                        class="btn btn-outline-primary btn-xs font-weight-bold shadow-sm">
                        <i class="fas fa-filter mr-1"></i> <span class="txt-btn-filtros">Ocultar Filtros</span>
                    </button>
                </div>
                <div class="small text-muted font-weight-bold">
                    <i class="fas fa-columns mr-1"></i> Paneles Laterales
                </div>
                <div>
                    <button type="button" id="btn-toggle-lote"
                        class="btn btn-outline-warning btn-xs font-weight-bold shadow-sm">
                        <span class="txt-btn-lote">Ocultar Lote</span> <i class="fas fa-tasks ml-1"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="row">

            <!-- ================= COLUMNA 1: FILTROS DINÁMICOS (IZQUIERDA) ================= -->
            <div class="col-md-3 px-1 col-transicion" id="panel-filtros">
                <div class="card card-primary card-outline h-100 mb-0 shadow-sm d-flex flex-column">
                    <div class="card-header bg-white py-2 px-2 d-flex justify-content-between align-items-center">
                        <h6 class="card-title text-dark font-weight-bold mb-0" style="font-size: 0.85rem;">
                            <i class="fas fa-filter mr-1 text-primary"></i> 1. Filtros
                        </h6>
                    </div>
                    <div class="card-body p-2 d-flex flex-column justify-content-between contenido-lateral">
                        <div>
                            <!-- Filtro por Periodo Académico -->
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold text-secondary mb-1">Periodo Académico:</label>
                                <select id="filtro-periodo" name="periodo_id" class="form-control form-control-sm"
                                    style="font-size: 0.75rem;">
                                    <option value="">-- Todos los Periodos --</option>
                                    @foreach ($periodos as $per)
                                        <option value="{{ $per->id }}"
                                            {{ isset($periodoId) && $periodoId == $per->id ? 'selected' : '' }}>
                                            {{ $per->nombre }} - Gestión {{ optional($per->gestion)->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Filtro por Carrera -->
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold text-secondary mb-1">Carrera:</label>
                                <select id="filtro-carrera" class="form-control form-control-sm"
                                    style="font-size: 0.75rem;">
                                    <option value="">-- Todas las Carreras --</option>
                                    @foreach ($carreras as $car)
                                        <option value="{{ $car->id }}">{{ $car->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Filtro por Grado / Semestre -->
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold text-secondary mb-1">Grado / Semestre:</label>
                                <select id="filtro-grado" class="form-control form-control-sm" style="font-size: 0.75rem;">
                                    <option value="">-- Todos los Grados --</option>
                                    @foreach ($grados as $gra)
                                        <option value="{{ $gra->id }}">{{ $gra->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Turno y Paralelo en Fila -->
                            <div class="form-row mb-2">
                                <div class="col-6">
                                    <label class="small font-weight-bold text-secondary mb-1">Turno:</label>
                                    <select id="filtro-turno" class="form-control form-control-sm"
                                        style="font-size: 0.72rem;">
                                        <option value="">-- Todos --</option>
                                        @foreach ($turnos as $tur)
                                            <option value="{{ $tur->id }}">{{ $tur->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="small font-weight-bold text-secondary mb-1">Paralelo:</label>
                                    <select id="filtro-paralelo" class="form-control form-control-sm"
                                        style="font-size: 0.72rem;">
                                        <option value="">-- Todos --</option>
                                        @foreach ($paralelos as $par)
                                            <option value="{{ $par->id }}">{{ $par->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Buscador por Texto -->
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold text-secondary mb-1">Materia (Nombre / Sigla):</label>
                                <input type="text" id="filtro-busqueda" class="form-control form-control-sm"
                                    placeholder="Ej. MAT-101..." style="font-size: 0.75rem;">
                            </div>
                        </div>

                        <!-- Botón Limpiar Filtros -->
                        <div>
                            <button type="button" id="btn-limpiar-filtros"
                                class="btn btn-xs btn-outline-secondary btn-block" style="font-size: 0.75rem;">
                                <i class="fas fa-sync-alt mr-1"></i> Limpiar Filtros
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            @php
                $obtenerClaseModalidad = function ($modalidad) {
                    switch (strtolower($modalidad ?? '')) {
                        case 'directa':
                            return 'badge-success';
                        case 'a_ciegas':
                            return 'badge-secondary';
                        case 'dictada':
                            return 'badge-warning';
                        default:
                            return 'badge-info';
                    }
                };
            @endphp

            <!-- ================= COLUMNA 2: TABLA DE OFERTAS ACADÉMICAS (CENTRAL) ================= -->
            <div class="col-md-6 px-1 col-transicion" id="panel-tabla">
                <div class="card card-success card-outline card-scroll shadow-sm mb-0">
                    <div class="card-header bg-white py-2 px-2 d-flex justify-content-between align-items-center">
                        <h6 class="card-title text-dark font-weight-bold mb-0" style="font-size: 0.85rem;">
                            <i class="fas fa-list mr-1 text-success"></i> 2. Oferta Académica Disponible
                        </h6>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="seleccionar-todos-visibles">
                            <label class="custom-control-label small font-weight-bold text-dark cursor-pointer"
                                for="seleccionar-todos-visibles">Seleccionar Visibles</label>
                        </div>
                    </div>
                    <div class="card-body p-2 card-body-scroll"
                        style="max-height: none !important; height: auto !important; overflow-y: visible !important;">
                        <table id="tabla-programacion"
                            class="table table-bordered table-striped table-hover text-nowrap w-100 mb-0">
                            <thead class="thead-dark" style="font-size: 0.75rem;">
                                <tr>
                                    <th class="text-center" style="width: 25px;"><i class="fas fa-check-square"></i></th>
                                    <th class="text-center" style="width: 40px;">N°</th>
                                    <th>Materia / Sigla</th>
                                    <th>Carrera / Semestre / Periodo</th>
                                    <th class="text-center">T / P</th>
                                    <th class="text-center">P1</th>
                                    <th class="text-center">P2</th>
                                    <th class="text-center">EF</th>
                                    <th class="text-center">2T</th>
                                    <th class="text-center" style="width: 60px;">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if (isset($listaOfertas))
                                    @foreach ($listaOfertas as $oferta)
                                        @php
                                            $exámenes = $oferta->programacionesExamen->keyBy('instancia');
                                            $p1 = $exámenes->get('P1');
                                            $p2 = $exámenes->get('P2');
                                            $ef = $exámenes->get('EF');
                                            $si = $exámenes->get('2T');

                                            $tieneFoliosP1 = $p1 && $p1->foliosExamen && $p1->foliosExamen->count() > 0;
                                            $tieneFoliosP2 = $p2 && $p2->foliosExamen && $p2->foliosExamen->count() > 0;
                                            $tieneFoliosEF = $ef && $ef->foliosExamen && $ef->foliosExamen->count() > 0;
                                            $tieneFoliosSI = $si && $si->foliosExamen && $si->foliosExamen->count() > 0;

                                            $nombMateria = strtolower($oferta->pensum->materia->nombre ?? '');
                                            $siglaMateria = strtolower($oferta->pensum->materia->sigla ?? '');
                                            $idPeriodo = $oferta->periodo_id ?? '';
                                            $idCarrera = $oferta->pensum->carrera_id ?? '';
                                            $idGrado = $oferta->pensum->grado_id ?? '';
                                            $idTurno = $oferta->turno_id ?? '';
                                            $idParalelo = $oferta->paralelo_id ?? '';

                                            $tieneP1 = $p1 ? '1' : '0';
                                            $tieneP2 = $p2 ? '1' : '0';
                                            $tieneEF = $ef ? '1' : '0';
                                            $tiene2T = $si ? '1' : '0';

                                            $fechaP1 = $p1
                                                ? \Carbon\Carbon::parse($p1->fecha_programada)->format('d-m')
                                                : null;
                                            $claseP1 = $p1
                                                ? $obtenerClaseModalidad($p1->modalidad)
                                                : 'badge-light text-muted border';
                                            $estiloP1 =
                                                $p1 && strtolower($p1->modalidad) === 'a_ciegas'
                                                    ? 'background-color: #6f42c1; color: #fff;'
                                                    : '';

                                            $fechaP2 = $p2
                                                ? \Carbon\Carbon::parse($p2->fecha_programada)->format('d-m')
                                                : null;
                                            $claseP2 = $p2
                                                ? $obtenerClaseModalidad($p2->modalidad)
                                                : 'badge-light text-muted border';
                                            $estiloP2 =
                                                $p2 && strtolower($p2->modalidad) === 'a_ciegas'
                                                    ? 'background-color: #6f42c1; color: #fff;'
                                                    : '';

                                            $fechaEF = $ef
                                                ? \Carbon\Carbon::parse($ef->fecha_programada)->format('d-m')
                                                : null;
                                            $claseEF = $ef
                                                ? $obtenerClaseModalidad($ef->modalidad)
                                                : 'badge-light text-muted border';
                                            $estiloEF =
                                                $ef && strtolower($ef->modalidad) === 'a_ciegas'
                                                    ? 'background-color: #6f42c1; color: #fff;'
                                                    : '';

                                            $fechaSI = $si
                                                ? \Carbon\Carbon::parse($si->fecha_programada)->format('d-m')
                                                : null;
                                            $claseSI = $si
                                                ? $obtenerClaseModalidad($si->modalidad)
                                                : 'badge-light text-muted border';
                                            $estiloSI =
                                                $si && strtolower($si->modalidad) === 'a_ciegas'
                                                    ? 'background-color: #6f42c1; color: #fff;'
                                                    : '';
                                        @endphp
                                        <tr class="oferta-row" data-periodo="{{ $idPeriodo }}"
                                            data-carrera="{{ $idCarrera }}" data-grado="{{ $idGrado }}"
                                            data-turno="{{ $idTurno }}" data-paralelo="{{ $idParalelo }}"
                                            data-p1="{{ $tieneP1 }}" data-p2="{{ $tieneP2 }}"
                                            data-ef="{{ $tieneEF }}" data-2t="{{ $tiene2T }}"
                                            data-texto="{{ $nombMateria }} {{ $siglaMateria }}">

                                            <!-- Checkbox -->
                                            <td class="text-center">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" name="ofertas_ids[]"
                                                        value="{{ $oferta->id }}" id="oferta_chk_{{ $oferta->id }}"
                                                        class="custom-control-input oferta-checkbox">
                                                    <label class="custom-control-label"
                                                        for="oferta_chk_{{ $oferta->id }}"></label>
                                                </div>
                                            </td>

                                            <!-- NÚMERO DE FILA -->
                                            <td class="text-center text-muted font-weight-bold row-index"
                                                style="font-size: 0.75rem;">
                                                {{ $loop->iteration }}
                                            </td>

                                            <td>
                                                <span
                                                    class="font-weight-bold text-dark">{{ $oferta->pensum->materia->nombre ?? 'S/N' }}</span><br>
                                                <small
                                                    class="text-muted">{{ $oferta->pensum->materia->sigla ?? 'S/S' }}</small>
                                            </td>

                                            <td>
                                                <span
                                                    class="text-secondary font-weight-bold">{{ $oferta->pensum->carrera->nombre ?? 'S/C' }}</span><br>
                                                <small
                                                    class="text-muted">{{ $oferta->pensum->grado->nombre ?? 'S/G' }}</small>
                                                @if (isset($oferta->periodo))
                                                    <span class="badge badge-info float-right" style="font-size: 0.65rem;"
                                                        title="Periodo Académico">
                                                        {{ $oferta->periodo->nombre }}
                                                    </span>
                                                @endif
                                            </td>

                                            <td class="text-center">
                                                <span
                                                    class="badge badge-light border">{{ $oferta->turno->nombre ?? 'S/T' }}</span>
                                                <span
                                                    class="badge badge-light border">{{ $oferta->paralelo->nombre ?? 'S/P' }}</span>
                                            </td>

                                            <!-- P1 -->
                                            <td
                                                class="columna-instancia text-center {{ $tieneFoliosP1 ? 'table-success' : '' }}">
                                                @if ($p1)
                                                    @php
                                                        $esCiegaODictadaP1 = in_array(
                                                            strtolower($p1->modalidad ?? ''),
                                                            ['a_ciegas', 'dictada'],
                                                        );
                                                        $rutaP1 = $esCiegaODictadaP1
                                                            ? route('admin.folio-examens.plantilla', $p1->id)
                                                            : '#';
                                                        $tituloP1 =
                                                            strtolower($p1->modalidad ?? '') === 'a_ciegas'
                                                                ? 'Foliar a ciegas'
                                                                : (strtolower($p1->modalidad ?? '') === 'dictada'
                                                                    ? 'Volcado por dictado'
                                                                    : 'Examen programado');
                                                    @endphp
                                                    <a href="{{ $rutaP1 }}"
                                                        class="badge {{ $claseP1 }} p-1 d-inline-block shadow-sm text-decoration-none"
                                                        style="{{ $estiloP1 }}" title="{{ $tituloP1 }}">
                                                        <span class="fecha-vertical">{{ $fechaP1 }}</span>
                                                        @if (strtolower($p1->modalidad ?? '') === 'a_ciegas')
                                                            <i class="fas fa-barcode d-block mt-1"
                                                                style="font-size: 0.55rem;"></i>
                                                        @elseif(strtolower($p1->modalidad ?? '') === 'dictada')
                                                            <i class="fas fa-pen-nib d-block mt-1"
                                                                style="font-size: 0.55rem;"></i>
                                                        @endif
                                                    </a>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>

                                            <!-- P2 -->
                                            <td
                                                class="columna-instancia text-center {{ $tieneFoliosP2 ? 'table-success' : '' }}">
                                                @if ($p2)
                                                    @php
                                                        $esCiegaODictadaP2 = in_array(
                                                            strtolower($p2->modalidad ?? ''),
                                                            ['a_ciegas', 'dictada'],
                                                        );
                                                        $rutaP2 = $esCiegaODictadaP2
                                                            ? route('admin.folio-examens.plantilla', $p2->id)
                                                            : '#';
                                                        $tituloP2 =
                                                            strtolower($p2->modalidad ?? '') === 'a_ciegas'
                                                                ? 'Foliar a ciegas'
                                                                : (strtolower($p2->modalidad ?? '') === 'dictada'
                                                                    ? 'Volcado por dictado'
                                                                    : 'Examen programado');
                                                    @endphp
                                                    <a href="{{ $rutaP2 }}"
                                                        class="badge {{ $claseP2 }} p-1 d-inline-block shadow-sm text-decoration-none"
                                                        style="{{ $estiloP2 }}" title="{{ $tituloP2 }}">
                                                        <span class="fecha-vertical">{{ $fechaP2 }}</span>
                                                        @if (strtolower($p2->modalidad ?? '') === 'a_ciegas')
                                                            <i class="fas fa-barcode d-block mt-1"
                                                                style="font-size: 0.55rem;"></i>
                                                        @elseif(strtolower($p2->modalidad ?? '') === 'dictada')
                                                            <i class="fas fa-pen-nib d-block mt-1"
                                                                style="font-size: 0.55rem;"></i>
                                                        @endif
                                                    </a>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>

                                            <!-- EF -->
                                            <td
                                                class="columna-instancia text-center {{ $tieneFoliosEF ? 'table-success' : '' }}">
                                                @if ($ef)
                                                    @php
                                                        $esCiegaODictadaEF = in_array(
                                                            strtolower($ef->modalidad ?? ''),
                                                            ['a_ciegas', 'dictada'],
                                                        );
                                                        $rutaEF = $esCiegaODictadaEF
                                                            ? route('admin.folio-examens.plantilla', $ef->id)
                                                            : '#';
                                                        $tituloEF =
                                                            strtolower($ef->modalidad ?? '') === 'a_ciegas'
                                                                ? 'Foliar a ciegas'
                                                                : (strtolower($ef->modalidad ?? '') === 'dictada'
                                                                    ? 'Volcado por dictado'
                                                                    : 'Examen programado');
                                                    @endphp
                                                    <a href="{{ $rutaEF }}"
                                                        class="badge {{ $claseEF }} p-1 d-inline-block shadow-sm text-decoration-none"
                                                        style="{{ $estiloEF }}" title="{{ $tituloEF }}">
                                                        <span class="fecha-vertical">{{ $fechaEF }}</span>
                                                        @if (strtolower($ef->modalidad ?? '') === 'a_ciegas')
                                                            <i class="fas fa-barcode d-block mt-1"
                                                                style="font-size: 0.55rem;"></i>
                                                        @elseif(strtolower($ef->modalidad ?? '') === 'dictada')
                                                            <i class="fas fa-pen-nib d-block mt-1"
                                                                style="font-size: 0.55rem;"></i>
                                                        @endif
                                                    </a>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>

                                            <!-- 2T -->
                                            <td
                                                class="columna-instancia text-center {{ $tieneFoliosSI ? 'table-success' : '' }}">
                                                @if ($si)
                                                    @php
                                                        $esCiegaODictadaSI = in_array(
                                                            strtolower($si->modalidad ?? ''),
                                                            ['a_ciegas', 'dictada'],
                                                        );
                                                        $rutaSI = $esCiegaODictadaSI
                                                            ? route('admin.folio-examens.plantilla', $si->id)
                                                            : '#';
                                                        $tituloSI =
                                                            strtolower($si->modalidad ?? '') === 'a_ciegas'
                                                                ? 'Foliar a ciegas'
                                                                : (strtolower($si->modalidad ?? '') === 'dictada'
                                                                    ? 'Volcado por dictado'
                                                                    : 'Examen programado');
                                                    @endphp
                                                    <a href="{{ $rutaSI }}"
                                                        class="badge {{ $claseSI }} p-1 d-inline-block shadow-sm text-decoration-none"
                                                        style="{{ $estiloSI }}" title="{{ $tituloSI }}">
                                                        <span class="fecha-vertical">{{ $fechaSI }}</span>
                                                        @if (strtolower($si->modalidad ?? '') === 'a_ciegas')
                                                            <i class="fas fa-barcode d-block mt-1"
                                                                style="font-size: 0.55rem;"></i>
                                                        @elseif(strtolower($si->modalidad ?? '') === 'dictada')
                                                            <i class="fas fa-pen-nib d-block mt-1"
                                                                style="font-size: 0.55rem;"></i>
                                                        @endif
                                                    </a>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>

                                            <td class="text-center">
                                                <div class="btn-group">
                                                    <!-- Botón principal de editar -->
                                                    <a href="{{ route('admin.programacion-examenes.create', ['oferta_id' => $oferta->id]) }}"
                                                        class="btn btn-info btn-xs px-1" title="Gestionar / Programar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>

                                                    <!-- Botón toggle dropdown -->
                                                    <button type="button"
                                                        class="btn btn-info btn-xs dropdown-toggle dropdown-icon px-1"
                                                        data-toggle="dropdown" aria-expanded="false">
                                                        <span class="sr-only">Toggle Dropdown</span>
                                                    </button>

                                                    <!-- MENÚ DESPLEGABLE CORPORATIVO -->
                                                    <div class="dropdown-menu dropdown-menu-right shadow"
                                                        style="font-size: 0.75rem;">
                                                        <h6
                                                            class="dropdown-header font-weight-bold text-secondary py-1 mb-1">
                                                            <i class="fas fa-list-ol mr-1"></i> Ir a Foliación / Plantilla
                                                        </h6>
                                                        <div class="dropdown-divider my-1"></div>

                                                        <!-- 📊 Planilla Docente / Estación por cada instancia existente -->
                                                        @foreach ([['instancia' => 'P1', 'obj' => $p1], ['instancia' => 'P2', 'obj' => $p2], ['instancia' => 'EF', 'obj' => $ef], ['instancia' => '2T', 'obj' => $si]] as $item)
                                                            @if ($item['obj'])
                                                                <a href="{{ route('admin.programacion-examenes.examenes-calificados', $item['obj']->id) }}"
                                                                    class="dropdown-item py-1 font-weight-bold text-primary">
                                                                    <i class="fas fa-clipboard-list mr-1"></i>
                                                                    Ver Planilla Calificada ({{ $item['instancia'] }})
                                                                </a>
                                                            @endif
                                                        @endforeach

                                                        <div class="dropdown-divider my-1"></div>

                                                        <!-- 🛡️ Control y Cierre (Sellar / Desbloquear) -->
                                                        <h6 class="dropdown-header font-weight-bold text-danger py-0 mb-1">
                                                            <i class="fas fa-lock mr-1"></i> Control y Cierre
                                                        </h6>

                                                        @foreach ([['instancia' => 'P1', 'obj' => $p1], ['instancia' => 'P2', 'obj' => $p2], ['instancia' => 'EF', 'obj' => $ef], ['instancia' => '2T', 'obj' => $si]] as $item)
                                                            @if ($item['obj'])
                                                                @if (!$item['obj']->bloqueado)
                                                                    <!-- Botón para Sellar -->
                                                                    <form
                                                                        action="{{ route('admin.programacion-examenes.sellar', $item['obj']->id) }}"
                                                                        method="POST"
                                                                        onsubmit="return confirm('¿Estás seguro de sellar la instancia {{ $item['instancia'] }}? El docente ya no podrá modificar notas.');">
                                                                        @csrf
                                                                        @method('PATCH')
                                                                        <button type="submit"
                                                                            class="dropdown-item py-1 text-danger font-weight-bold">
                                                                            <i class="fas fa-lock mr-1"></i> Sellar
                                                                            {{ $item['instancia'] }}
                                                                        </button>
                                                                    </form>
                                                                @else
                                                                    <!-- 🔓 Botón para Desbloquear -->
                                                                    <form
                                                                        action="{{ route('admin.programacion-examenes.desbloquear', $item['obj']->id) }}"
                                                                        method="POST"
                                                                        onsubmit="return confirm('¿Estás seguro de desbloquear la instancia {{ $item['instancia'] }}? El docente volverá a tener acceso para modificar notas.');">
                                                                        @csrf
                                                                        @method('PATCH')
                                                                        <button type="submit"
                                                                            class="dropdown-item py-1 text-success font-weight-bold">
                                                                            <i class="fas fa-lock-open mr-1"></i>
                                                                            Desbloquear {{ $item['instancia'] }}
                                                                        </button>
                                                                    </form>
                                                                @endif
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ================= COLUMNA 3: SELECCIONADOS Y ACCIÓN MASIVA (DERECHA) ================= -->
            <div class="col-md-3 px-1 col-transicion" id="panel-seleccion">
                <div class="card card-warning card-outline card-scroll shadow-sm mb-0 d-flex flex-column">
                    <div class="card-header bg-white py-2 px-2 d-flex justify-content-between align-items-center">
                        <h6 class="card-title text-dark font-weight-bold mb-0" style="font-size: 0.85rem;">
                            3. Lote
                        </h6>
                        <button type="button" id="btn-limpiar-seleccion" class="btn btn-xs btn-outline-secondary"
                            style="font-size: 0.60rem;">Limpiar</button>
                    </div>
                    <div
                        class="card-body p-2 card-body-scroll d-flex flex-column justify-content-between contenido-lateral">

                        <!-- Lista dinámica de seleccionadas -->
                        <div class="flex-grow-1 overflow-hidden" style="max-height: 250px; overflow-y: auto !important;">
                            <ul id="lista-seleccionados-resumen" class="list-group list-group-flush small">
                                <li class="list-group-item text-muted text-center py-3">No hay elementos seleccionados</li>
                            </ul>
                        </div>

                        <!-- 🔘 BOTONES DE ACCIÓN MASIVA (Programar, Actualizar y Borrar) -->
                        <div class="mt-2 pt-2 border-top">
                            <div class="d-flex flex-column" style="gap: 4px;">
                                <!-- Botón 1: Programar / Añadir -->
                                <button type="button" id="btn-procesar-bloque"
                                    class="action-btn btn btn-primary btn-sm font-weight-bold text-left py-1 px-2"
                                    data-action="{{ route('admin.programacion-examenes.create') }}" data-method="GET"
                                    disabled style="font-size: 0.75rem;">
                                    <i class="fas fa-calendar-plus mr-1"></i> Programar Lote
                                </button>

                                <!-- Botón 2: Actualizar / Editar -->
                                <button type="button" id="btn-editar-bloque"
                                    class="action-btn btn btn-success btn-sm font-weight-bold text-left py-1 px-2"
                                    data-action="{{ route('admin.programacion-examenes.create') }}" data-method="GET"
                                    disabled style="font-size: 0.75rem;">
                                    <i class="fas fa-tasks mr-1"></i> Actualizar Lote
                                </button>

                                <!-- Botón 3: Borrar / Eliminar -->
                                <button type="button" id="btn-tercer-bloque"
                                    class="action-btn btn btn-danger btn-sm font-weight-bold text-left py-1 px-2"
                                    data-action="{{ route('admin.programacion-examenes.create') }}" data-method="DELETE"
                                    disabled style="font-size: 0.75rem;">
                                    <i class="fas fa-trash-alt mr-1"></i> Borrar Lote
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div> <!-- Cierre del row principal -->
    </form> <!-- Cierre del form masivo -->


    <!-- ================================================================= -->
    <!-- MODAL PARA SELECCIONAR INSTANCIA E INSTRUCTIVA                    -->
    <!-- ================================================================= -->
    <div class="modal fade" id="modalSeleccionarInstancia" tabindex="-1" role="dialog"
        aria-labelledby="modalInstanciaLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content shadow-sm">
                <div class="modal-header bg-success text-white py-2" id="modal-header-container">
                    <h6 class="modal-title font-weight-bold" id="modalInstanciaLabel" style="font-size: 0.9rem;">
                        <i class="fas fa-tasks mr-1"></i> Seleccionar Instancia
                    </h6>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body py-3">
                    <p class="text-muted small mb-2" id="modal-texto-instruccion">¿Qué instancia evaluativa deseas
                        modificar en este lote?</p>

                    <!-- Select de Instancia -->
                    <div class="form-group mb-2">
                        <label for="select-instancia-modal"
                            class="small font-weight-bold text-dark mb-1">Instancia:</label>
                        <select id="select-instancia-modal" class="form-control form-control-sm" required
                            style="font-size: 0.8rem;">
                            <option value="">-- Selecciona Instancia --</option>
                            <option value="P1">Primer Parcial (P1)</option>
                            <option value="P2">Segundo Parcial (P2)</option>
                            <option value="EF">Examen Final (EF)</option>
                            <option value="2T">Segunda Instancia (2T)</option>
                        </select>
                    </div>

                    <!-- 📝 Campo para la Instructiva / Observación -->
                    <div class="form-group mb-0" id="contenedor-instructiva-modal">
                        <label for="input-instructiva-modal" class="small font-weight-bold text-dark mb-1">Instructiva /
                            Memorándum:</label>
                        <input type="text" id="input-instructiva-modal" class="form-control form-control-sm"
                            placeholder="Ej: Cite N° 045/2026" style="font-size: 0.8rem;">
                    </div>

                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-xs" data-dismiss="modal">Cancelar</button>
                    <button type="button" id="btn-confirmar-edicion-masiva"
                        class="btn btn-success btn-xs font-weight-bold">
                        <i class="fas fa-arrow-right mr-1"></i> Continuar
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop


@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let botonPresionado = null;

            // 1. Control del panel izquierdo (Filtros)
            $('#btn-toggle-filtros').on('click', function() {
                var panelFiltros = $('#panel-filtros');
                var contenido = panelFiltros.find('.contenido-lateral');
                var txtSpan = $(this).find('.txt-btn-filtros');
                var estaOculto = panelFiltros.hasClass('d-none');

                if (!estaOculto) {
                    contenido.hide();
                    panelFiltros.removeClass('col-md-3 px-1').addClass('d-none');
                    txtSpan.text('Mostrar Filtros');
                    $(this).removeClass('btn-outline-primary').addClass('btn-primary');
                } else {
                    panelFiltros.removeClass('d-none').addClass('col-md-3 px-1');
                    contenido.show();
                    txtSpan.text('Ocultar Filtros');
                    $(this).removeClass('btn-primary').addClass('btn-outline-primary');
                }
                recalcularAnchoTabla();
            });

            // 2. Control del panel derecho (Lote)
            $('#btn-toggle-lote').on('click', function() {
                var panelLote = $('#panel-seleccion');
                var contenido = panelLote.find('.contenido-lateral');
                var txtSpan = $(this).find('.txt-btn-lote');
                var estaOculto = panelLote.hasClass('d-none');

                if (!estaOculto) {
                    contenido.hide();
                    panelLote.removeClass('col-md-3 px-1').addClass('d-none');
                    txtSpan.text('Mostrar Lote');
                    $(this).removeClass('btn-outline-warning').addClass('btn-warning');
                } else {
                    panelLote.removeClass('d-none').addClass('col-md-3 px-1');
                    contenido.show();
                    txtSpan.text('Ocultar Lote');
                    $(this).removeClass('btn-warning').addClass('btn-outline-warning');
                }
                recalcularAnchoTabla();
            });

            function recalcularAnchoTabla() {
                var anchoCentral = 12;
                if (!$('#panel-filtros').hasClass('d-none')) anchoCentral -= 3;
                if (!$('#panel-seleccion').hasClass('d-none')) anchoCentral -= 3;
                $('#panel-tabla').removeClass('col-md-5 col-md-6 col-md-7 col-md-9 col-md-10 col-md-12')
                    .addClass('col-md-' + anchoCentral);
            }

            // --- 4. CAPTURAR ACCIONES DE LOTE Y ABRIR MODAL ---
            $(document).on('click', '.action-btn', function(e) {
                var btnId = $(this).attr('id');

                if (btnId === 'btn-header-crear') {
                    return;
                }

                if ($('.oferta-checkbox:checked').length === 0) {
                    e.preventDefault();
                    alert('Por favor, selecciona al menos una oferta académica del lote.');
                    return;
                }

                if (btnId === 'btn-procesar-bloque' || btnId === 'btn-editar-bloque' || btnId ===
                    'btn-tercer-bloque') {
                    e.preventDefault();
                    botonPresionado = $(this);

                    // Limpiar campos del modal previamente
                    $('#select-instancia-modal').val('');
                    $('#input-instructiva-modal').val('');

                    if (btnId === 'btn-tercer-bloque') {
                        // Eliminar
                        botonPresionado.attr('data-action',
                            "{{ route('admin.programacion-examenes.destroy-lote') }}");
                        botonPresionado.attr('data-method', 'DELETE');

                        $('#modal-header-container').removeClass('bg-success bg-primary').addClass(
                            'bg-danger');
                        $('#modalInstanciaLabel').html(
                            '<i class="fas fa-trash-alt mr-1"></i> Seleccionar Instancia a Eliminar');
                        $('#modal-texto-instruccion').text(
                            '¿Qué instancia evaluativa deseas eliminar en este lote?');
                        $('#btn-confirmar-edicion-masiva').removeClass('btn-success btn-primary').addClass(
                            'btn-danger');
                        $('#contenedor-instructiva-modal').hide();
                    } else if (btnId === 'btn-procesar-bloque') {
                        // Programar / Añadir
                        botonPresionado.attr('data-action',
                            "{{ route('admin.programacion-examenes.store-lote') }}");
                        botonPresionado.attr('data-method', 'POST');

                        $('#modal-header-container').removeClass('bg-success bg-danger').addClass(
                            'bg-primary');
                        $('#modalInstanciaLabel').html(
                            '<i class="fas fa-calendar-plus mr-1"></i> Programar Lote de Exámenes');
                        $('#modal-texto-instruccion').text(
                            'Indica la instancia y la instructiva de apertura:');
                        $('#btn-confirmar-edicion-masiva').removeClass('btn-success bg-danger').addClass(
                            'btn-primary');
                        $('#contenedor-instructiva-modal').show();
                    } else if (btnId === 'btn-editar-bloque') {
                        // Editar
                        botonPresionado.attr('data-action',
                            "{{ route('admin.programacion-examenes.update-lote') }}");
                        botonPresionado.attr('data-method', 'PUT');

                        $('#modal-header-container').removeClass('bg-danger bg-primary').addClass(
                            'bg-success');
                        $('#modalInstanciaLabel').html(
                            '<i class="fas fa-tasks mr-1"></i> Modificar Lote de Exámenes');
                        $('#modal-texto-instruccion').text(
                            'Indica la instancia y la instructiva de modificación:');
                        $('#btn-confirmar-edicion-masiva').removeClass('btn-danger bg-primary').addClass(
                            'btn-success');
                        $('#contenedor-instructiva-modal').show();
                    }

                    $('#modalSeleccionarInstancia').modal('show');
                }
            });

            // --- 5. AL CONFIRMAR DENTRO DEL MODAL ---
            $('#btn-confirmar-edicion-masiva').on('click', function() {
                let instanciaElegida = $('#select-instancia-modal').val();
                let instructivaTexto = $('#input-instructiva-modal').val();

                if (!instanciaElegida) {
                    alert('Por favor, selecciona una instancia evaluativa.');
                    return;
                }

                if (botonPresionado.attr('id') === 'btn-tercer-bloque') {
                    if (!confirm(
                            '¿Estás totalmente seguro de eliminar las instancias seleccionadas de este lote? Esta acción no se puede deshacer.'
                            )) {
                        return;
                    }
                }

                $('#modalSeleccionarInstancia').modal('hide');

                var targetAction = botonPresionado.data('action');
                var targetMethod = botonPresionado.data('method') || 'POST';

                $('#form-masivo').attr('action', targetAction);
                $('#form-masivo').attr('method', 'POST');

                $('#method-spoofing-container').empty();
                if (targetMethod.toUpperCase() !== 'POST') {
                    $('#method-spoofing-container').html(
                        `<input type="hidden" name="_method" value="${targetMethod}">`
                    );
                }

                $('#form-masivo').find('input[name="instancia_filtro"]').remove();
                $('#form-masivo').find('input[name="instructiva"]').remove();

                $('#form-masivo').append(
                    `<input type="hidden" name="instancia_filtro" value="${instanciaElegida}">`
                );
                $('#form-masivo').append(
                    `<input type="hidden" name="instructiva" value="${instructivaTexto}">`
                );

                $('#form-masivo').submit();
            });

            // --- 6. FILTRADO DINÁMICO ---
            function aplicarFiltrosDinamicos() {
                var pId = $('#filtro-periodo').val();
                var cId = $('#filtro-carrera').val();
                var gId = $('#filtro-grado').val();
                var tId = $('#filtro-turno').val();
                var paId = $('#filtro-paralelo').val();
                var txt = $('#filtro-busqueda').val().toLowerCase().trim();

                $('.oferta-row').each(function() {
                    var row = $(this);
                    var match = true;
                    if (pId && row.data('periodo') != pId) match = false;
                    if (cId && row.data('carrera') != cId) match = false;
                    if (gId && row.data('grado') != gId) match = false;
                    if (tId && row.data('turno') != tId) match = false;
                    if (paId && row.data('paralelo') != paId) match = false;
                    if (txt && row.data('texto').indexOf(txt) === -1) match = false;
                    row.toggle(match);
                });
                actualizarNumeracionVisible();
            }

            $('#filtro-periodo, #filtro-carrera, #filtro-grado, #filtro-turno, #filtro-paralelo, #filtro-instancia')
                .on('change', aplicarFiltrosDinamicos);
            $('#filtro-busqueda').on('keyup', aplicarFiltrosDinamicos);

            $('#btn-limpiar-filtros').on('click', function() {
                $('#filtro-periodo, #filtro-carrera, #filtro-grado, #filtro-turno, #filtro-paralelo, #filtro-instancia')
                    .val('');
                $('#filtro-busqueda').val('');
                $('.oferta-row').show();
                actualizarNumeracionVisible();
            });

            // --- 7. SINCRONIZACIÓN DE SELECCIONADOS ---
            function actualizarContadorYLista() {
                var contenedor = $('#lista-seleccionados-resumen');
                contenedor.empty();
                var totalChecked = $('.oferta-checkbox:checked').length;

                if (totalChecked > 0) {
                    $('#btn-ejecutar-lote').prop('disabled', false);
                    $('.action-btn').prop('disabled', false);

                    $('.oferta-checkbox:checked').each(function() {
                        var chk = $(this);
                        var row = chk.closest('tr');
                        var id = chk.val();
                        var materiaTexto = row.find('td:nth-child(3) span').text();
                        var siglaTexto = row.find('td:nth-child(3) small').text();

                        contenedor.append(`
                            <li class="list-group-item d-flex justify-content-between align-items-center py-1 px-2" data-id="${id}">
                                <span><strong>${siglaTexto}</strong> - ${materiaTexto}</span>
                                <button type="button" class="btn btn-xs text-danger quitar-item-btn p-0" data-id="${id}">
                                    <i class="fas fa-times"></i>
                                </button>
                            </li>
                        `);
                    });
                } else {
                    contenedor.html(
                        '<li class="list-group-item text-muted text-center py-3">No hay elementos seleccionados</li>'
                    );
                    $('#btn-ejecutar-lote').prop('disabled', true);
                    $('.action-btn').prop('disabled', true);
                }
            }

            $(document).on('change', '.oferta-checkbox', actualizarContadorYLista);
            $(document).on('click', '.quitar-item-btn', function() {
                $('#oferta_chk_' + $(this).data('id')).prop('checked', false).trigger('change');
            });
            $('#btn-limpiar-seleccion').on('click', function() {
                $('.oferta-checkbox').prop('checked', false).trigger('change');
                $('#seleccionar-todos-visibles').prop('checked', false);
            });
            $('#seleccionar-todos-visibles').on('change', function() {
                $('.oferta-row:visible').find('.oferta-checkbox').prop('checked', this.checked);
                actualizarContadorYLista();
            });

            actualizarNumeracionVisible();
            actualizarContadorYLista();
        });

        function actualizarNumeracionVisible() {
            let contador = 1;
            document.querySelectorAll('#tabla-programacion tbody tr.oferta-row').forEach(row => {
                if (row.style.display !== 'none') {
                    const indexCell = row.querySelector('.row-index');
                    if (indexCell) {
                        indexCell.textContent = contador++;
                    }
                }
            });
        }
    </script>
@endsection
