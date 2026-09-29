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

        .bg-soft-success {
            background-color: rgba(40, 167, 69, 0.18) !important;
        }

        .bg-soft-primary {
            background-color: rgba(0, 123, 255, 0.18) !important;
        }

        .bg-soft-warning {
            background-color: rgba(255, 193, 7, 1) !important;
        }

        #tabla-programacion th,
        #tabla-programacion td {
            padding: 0.2rem 0.3rem !important;
            vertical-align: middle !important;
            font-size: 0.75rem;
            line-height: 1.15;
        }

        #tabla-programacion small {
            font-size: 0.65rem !important;
            display: block;
            margin-top: -2px;
        }
    </style>
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fas fa-calendar-alt text-primary mr-2"></i> Programación y Calendario de Exámenes</h1>
            <p class="text-muted mb-0">Control de instancias evaluativas por oferta académica y periodo activo.</p>
        </div>
        <div>
            <!-- Botón de acceso a la Papelera -->
            <a href="{{ route('admin.programacion-examenes.papelera') }}"
                class="btn btn-outline-secondary btn-sm shadow-sm font-weight-bold">
                <i class="fas fa-trash-restore mr-1 text-danger"></i> Ver Papelera
            </a>
        </div>
    </div>
@stop

@section('content')
    @php
        $renderFechaVertical = function (
            $examen,
            $badgeColorPorDefecto = 'badge-secondary',
            $ofertaId = null,
            $instanciaKey = null,
        ) {
            if (!$examen) {
                // Si no hay examen programado y se proporcionó oferta e instancia, mostramos el botón rápido
                if ($ofertaId && $instanciaKey) {
                    $urlCrearIndividual = route('admin.programacion-examenes.create', [
                        'oferta_id' => $ofertaId,
                        'instancia' => $instanciaKey,
                    ]);
                    return '
                    <div class="d-flex flex-column align-items-center justify-content-center" style="height: 45px;">
                        <a href="' .
                        $urlCrearIndividual .
                        '" class="btn btn-xs btn-light border text-muted px-1 py-0 shadow-none" style="font-size: 0.60rem;" title="Programar ' .
                        $instanciaKey .
                        '">
                            <i class="fas fa-plus"></i>
                        </a>
                        <span class="text-muted" style="font-size: 0.55rem;">--/--</span>
                    </div>';
                }
                return '<span class="text-muted" style="font-size: 0.55rem;">--/--</span>';
            }

            $fecha = \Carbon\Carbon::parse($examen->fecha_programada);
            $diaMes = $fecha->format('d-m');

            $modalidad = strtolower($examen->modalidad ?? 'directa');
            $estiloModalidad = 'bg-success text-white';
            $styleExtra = '';

            if ($modalidad === 'a_ciegas') {
                $estiloModalidad = 'text-white';
                $styleExtra = 'background-color: #6f42c1 !important;';
            } elseif ($modalidad === 'dictada') {
                $estiloModalidad = 'bg-warning text-dark';
            }

            $iconoBloqueo = $examen->bloqueado ? '<i class="fas fa-lock text-danger ml-1" title="Bloqueado"></i>' : '';

            $contenidoHtml =
                '
            <div class="d-flex flex-column align-items-center justify-content-center" style="height: 45px;">
                <span class="badge ' .
                $estiloModalidad .
                ' px-1 py-1 text-center" style="font-size: 0.60rem; ' .
                $styleExtra .
                ' writing-mode: vertical-rl; transform: rotate(180deg); display: inline-block; letter-spacing: 0.5px;" title="Modalidad: ' .
                ucfirst(str_replace('_', ' ', $modalidad)) .
                '">' .
                $diaMes .
                '</span>
                ' .
                $iconoBloqueo .
                '
            </div>';

            // Si es A ciegas, redirige a folios; de lo contrario, si hay oferta/instancia, permite ir al formulario de creación/edición
            if ($modalidad === 'a_ciegas') {
                $urlAccion = route('admin.programacion-examenes.folios.index', $examen->id);
                $tituloTooltip = 'Modalidad A Ciegas: Click para ver Folios';
            } elseif ($ofertaId && $instanciaKey) {
                $urlAccion = route('admin.programacion-examenes.create', [
                    'oferta_id' => $ofertaId,
                    'instancia' => $instanciaKey,
                ]);
                $tituloTooltip = "Click para editar / ver {$instanciaKey}";
            } else {
                $urlAccion = '#';
                $tituloTooltip = 'Información de examen';
            }

            return '
            <a href="' .
                $urlAccion .
                '" class="text-decoration-none d-block w-100 h-100" title="' .
                $tituloTooltip .
                '" style="transition: opacity 0.2s;" onmouseover="this.style.opacity=\'0.75\'" onmouseout="this.style.opacity=\'1\'">
                ' .
                $contenidoHtml .
                '
            </a>';
        };
    @endphp

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
                                <select id="filtro-periodo" class="form-control form-control-sm"
                                    style="font-size: 0.75rem;">
                                    <option value="">-- Todos los Periodos --</option>
                                    @foreach ($periodos as $per)
                                        <option value="{{ $per->id }}">{{ $per->nombre_completo ?? $per->nombre }}
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
                    <div class="card-body p-2 card-body-scroll">
                        <table id="tabla-programacion"
                            class="table table-bordered table-striped table-hover text-nowrap w-100 mb-0">
                            <thead class="thead-dark" style="font-size: 0.75rem;">
                                <tr>
                                    <th class="text-center" style="width: 25px;"><i class="fas fa-check-square"></i></th>
                                    <th>Materia / Sigla</th>
                                    <th>Carrera / Periodo / Semestre</th>
                                    <th class="text-center">T / P</th>
                                    <th class="text-center">TP1</th>
                                    <th class="text-center">TP2</th>
                                    <th class="text-center">TP3</th>
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
                                            $tp1 = $exámenes->get('TP1');
                                            $tp2 = $exámenes->get('TP2');
                                            $tp3 = $exámenes->get('TP3');
                                            $p1 = $exámenes->get('P1');
                                            $p2 = $exámenes->get('P2');
                                            $ef = $exámenes->get('EF');
                                            $si = $exámenes->get('2T');

                                            $nombMateria = strtolower($oferta->pensum->materia->nombre ?? '');
                                            $siglaMateria = strtolower($oferta->pensum->materia->sigla ?? '');
                                            $idPeriodo = $oferta->periodo_id ?? '';
                                            $idCarrera = $oferta->pensum->carrera_id ?? '';
                                            $idGrado = $oferta->pensum->grado_id ?? '';
                                            $idTurno = $oferta->turno_id ?? '';
                                            $idParalelo = $oferta->paralelo_id ?? '';

                                            $tieneTP1 = $tp1 ? '1' : '0';
                                            $tieneTP2 = $tp2 ? '1' : '0';
                                            $tieneTP3 = $tp3 ? '1' : '0';
                                            $tieneP1 = $p1 ? '1' : '0';
                                            $tieneP2 = $p2 ? '1' : '0';
                                            $tieneEF = $ef ? '1' : '0';
                                            $tiene2T = $si ? '1' : '0';

                                            $bgTP1 =
                                                $tp1 && ($tp1->folios_count ?? ($tp1->folios->count() ?? 0)) > 0
                                                    ? 'bg-soft-warning'
                                                    : '';
                                            $bgTP2 =
                                                $tp2 && ($tp2->folios_count ?? ($tp2->folios->count() ?? 0)) > 0
                                                    ? 'bg-soft-warning'
                                                    : '';
                                            $bgTP3 =
                                                $tp3 && ($tp3->folios_count ?? ($tp3->folios->count() ?? 0)) > 0
                                                    ? 'bg-soft-warning'
                                                    : '';
                                            $bgP1 =
                                                $p1 && ($p1->folios_count ?? ($p1->folios->count() ?? 0)) > 0
                                                    ? 'bg-soft-warning'
                                                    : '';
                                            $bgP2 =
                                                $p2 && ($p2->folios_count ?? ($p2->folios->count() ?? 0)) > 0
                                                    ? 'bg-soft-warning'
                                                    : '';
                                            $bgEF =
                                                $ef && ($ef->folios_count ?? ($ef->folios->count() ?? 0)) > 0
                                                    ? 'bg-soft-warning'
                                                    : '';
                                            $bg2T =
                                                $si && ($si->folios_count ?? ($si->folios->count() ?? 0)) > 0
                                                    ? 'bg-soft-warning'
                                                    : '';
                                        @endphp
                                        <tr class="oferta-row" data-periodo="{{ $idPeriodo }}"
                                            data-carrera="{{ $idCarrera }}" data-grado="{{ $idGrado }}"
                                            data-turno="{{ $idTurno }}" data-paralelo="{{ $idParalelo }}"
                                            data-tp1="{{ $tieneTP1 }}" data-tp2="{{ $tieneTP2 }}"
                                            data-tp3="{{ $tieneTP3 }}" data-p1="{{ $tieneP1 }}"
                                            data-p2="{{ $tieneP2 }}" data-ef="{{ $tieneEF }}"
                                            data-2t="{{ $tiene2T }}"
                                            data-texto="{{ $nombMateria }} {{ $siglaMateria }}">

                                            <!-- Checkbox -->
                                            <td class="text-center align-middle">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" name="ofertas_ids[]"
                                                        value="{{ $oferta->id }}" id="oferta_chk_{{ $oferta->id }}"
                                                        class="custom-control-input oferta-checkbox">
                                                    <label class="custom-control-label"
                                                        for="oferta_chk_{{ $oferta->id }}"></label>
                                                </div>
                                            </td>

                                            <td class="align-middle">
                                                <span
                                                    class="font-weight-bold text-dark">{{ $oferta->pensum->materia->nombre ?? 'S/N' }}</span><br>
                                                <small
                                                    class="text-muted">{{ $oferta->pensum->materia->sigla ?? 'S/S' }}</small>
                                            </td>

                                            <td class="align-middle">
                                                <span
                                                    class="badge badge-info float-left">{{ $oferta->pensum->carrera->sigla ?? 'S/C' }}
                                                    @if (isset($oferta->periodo))
                                                        {{ $oferta->periodo->nombre }}
                                                    @endif
                                                </span>
                                                <br>
                                                <small
                                                    class="text-muted">{{ $oferta->pensum->grado->nombre ?? 'S/G' }}</small>
                                            </td>

                                            <td class="text-center align-middle">
                                                <span
                                                    class="badge badge-light border">{{ $oferta->turno->nombre ?? 'S/T' }}<br>{{ $oferta->paralelo->nombre ?? 'S/P' }}</span>
                                            </td>
                                            <!-- Columnas TP1, TP2, TP3 -->
                                            <!-- Columna TP1 -->
                                            <td class="text-center align-middle p-0 {{ $bgTP1 }}"
                                                style="height: 45px; line-height: 1;">
                                                {!! $renderFechaVertical($tp1, 'badge-secondary', $oferta->id, 'TP1') !!}
                                                @if ($tp1)
                                                    <div style="margin-top: -2px;">
                                                        <a href="{{ route('admin.programacion-examenes.consolidacion', $tp1->id) }}"
                                                            class="btn btn-xs {{ $tp1->bloqueado ? 'btn-secondary' : 'btn-primary' }} px-1 py-0"
                                                            style="font-size: 0.65rem;" title="Consolidar TP1">
                                                            <i
                                                                class="fas {{ $tp1->bloqueado ? 'fa-lock' : 'fa-clipboard-check' }}"></i>
                                                        </a>
                                                    </div>
                                                @endif
                                            </td>

                                            <!-- Columna TP2 -->
                                            <td class="text-center align-middle p-0 {{ $bgTP2 }}"
                                                style="height: 45px; line-height: 1;">
                                                {!! $renderFechaVertical($tp2, 'badge-secondary', $oferta->id, 'TP2') !!}
                                                @if ($tp2)
                                                    <div style="margin-top: -2px;">
                                                        <a href="{{ route('admin.programacion-examenes.consolidacion', $tp2->id) }}"
                                                            class="btn btn-xs {{ $tp2->bloqueado ? 'btn-secondary' : 'btn-primary' }} px-1 py-0"
                                                            style="font-size: 0.65rem;" title="Consolidar TP2">
                                                            <i
                                                                class="fas {{ $tp2->bloqueado ? 'fa-lock' : 'fa-clipboard-check' }}"></i>
                                                        </a>
                                                    </div>
                                                @endif
                                            </td>

                                            <!-- Columna TP3 -->
                                            <td class="text-center align-middle p-0 {{ $bgTP3 }}"
                                                style="height: 45px; line-height: 1;">
                                                {!! $renderFechaVertical($tp3, 'badge-secondary', $oferta->id, 'TP3') !!}
                                                @if ($tp3)
                                                    <div style="margin-top: -2px;">
                                                        <a href="{{ route('admin.programacion-examenes.consolidacion', $tp3->id) }}"
                                                            class="btn btn-xs {{ $tp3->bloqueado ? 'btn-secondary' : 'btn-primary' }} px-1 py-0"
                                                            style="font-size: 0.65rem;" title="Consolidar TP3">
                                                            <i
                                                                class="fas {{ $tp3->bloqueado ? 'fa-lock' : 'fa-clipboard-check' }}"></i>
                                                        </a>
                                                    </div>
                                                @endif
                                            </td>

                                            <!-- Columna P1 -->
                                            <td class="text-center align-middle p-0 {{ $bgP1 }}"
                                                style="height: 45px; line-height: 1;">
                                                {!! $renderFechaVertical($p1, 'badge-success', $oferta->id, 'P1') !!}
                                                @if ($p1)
                                                    <div style="margin-top: -2px;">
                                                        <a href="{{ route('admin.programacion-examenes.consolidacion', $p1->id) }}"
                                                            class="btn btn-xs {{ $p1->bloqueado ? 'btn-secondary' : 'btn-primary' }} px-1 py-0"
                                                            style="font-size: 0.65rem;" title="Consolidar P1">
                                                            <i
                                                                class="fas {{ $p1->bloqueado ? 'fa-lock' : 'fa-clipboard-check' }}"></i>
                                                        </a>
                                                    </div>
                                                @endif
                                            </td>

                                            <!-- Columna P2 -->
                                            <td class="text-center align-middle p-0 {{ $bgP2 }}"
                                                style="height: 45px; line-height: 1;">
                                                {!! $renderFechaVertical($p2, 'badge-success', $oferta->id, 'P2') !!}
                                                @if ($p2)
                                                    <div style="margin-top: -2px;">
                                                        <a href="{{ route('admin.programacion-examenes.consolidacion', $p2->id) }}"
                                                            class="btn btn-xs {{ $p2->bloqueado ? 'btn-secondary' : 'btn-primary' }} px-1 py-0"
                                                            style="font-size: 0.65rem;" title="Consolidar P2">
                                                            <i
                                                                class="fas {{ $p2->bloqueado ? 'fa-lock' : 'fa-clipboard-check' }}"></i>
                                                        </a>
                                                    </div>
                                                @endif
                                            </td>

                                            <!-- Columna EF -->
                                            <td class="text-center align-middle p-0 {{ $bgEF }}"
                                                style="height: 45px; line-height: 1;">
                                                {!! $renderFechaVertical($ef, 'badge-primary', $oferta->id, 'EF') !!}
                                                @if ($ef)
                                                    <div style="margin-top: -2px;">
                                                        <a href="{{ route('admin.programacion-examenes.consolidacion', $ef->id) }}"
                                                            class="btn btn-xs {{ $ef->bloqueado ? 'btn-secondary' : 'btn-primary' }} px-1 py-0"
                                                            style="font-size: 0.65rem;" title="Consolidar EF">
                                                            <i
                                                                class="fas {{ $ef->bloqueado ? 'fa-lock' : 'fa-clipboard-check' }}"></i>
                                                        </a>
                                                    </div>
                                                @endif
                                            </td>

                                            <!-- Columna 2T (Asegúrate de cambiar $si por tu variable correcta de 2T, ej: $t2 o $segundaInstancia) -->
                                            <td class="text-center align-middle p-0 {{ $bg2T }}"
                                                style="height: 45px; line-height: 1;">
                                                {!! $renderFechaVertical($si, 'badge-warning', $oferta->id, '2T') !!}
                                                @if ($si)
                                                    <div style="margin-top: -2px;">
                                                        <a href="{{ route('admin.programacion-examenes.consolidacion', $si->id) }}"
                                                            class="btn btn-xs {{ $si->bloqueado ? 'btn-secondary' : 'btn-primary' }} px-1 py-0"
                                                            style="font-size: 0.65rem;" title="Consolidar 2T">
                                                            <i
                                                                class="fas {{ $si->bloqueado ? 'fa-lock' : 'fa-clipboard-check' }}"></i>
                                                        </a>
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="text-center align-middle">
                                                <a href="{{ route('admin.programacion-examenes.create', ['oferta_id' => $oferta->id]) }}"
                                                    class="btn btn-info btn-xs px-1" title="Gestionar individual">
                                                    <i class="fas fa-edit"></i>
                                                </a>
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
                        <div class="flex-grow-1 overflow-hidden d-flex flex-column">
                            <div id="sin-seleccion" class="text-muted text-center py-4">
                                <i class="fas fa-hand-pointer fa-2x mb-2 text-secondary"></i>
                                <p class="small mb-0">Marca materias para configurar el lote.</p>
                            </div>
                            <div id="lista-seleccionadas-container" class="flex-grow-1 overflow-auto pr-1 d-none">
                                <!-- Se llena dinámicamente con JavaScript en tiempo real -->
                            </div>
                        </div>

                        <!-- Botones de Acción Masiva -->
                        <div class="border-top pt-2 mt-2">
                            <div class="btn-group-vertical w-100">
                                <!-- Botón 1: Dispara el Modal de Configuración Masiva -->
                                <button type="button" id="btn-abrir-modal-lote"
                                    class="btn btn-primary btn-sm font-weight-bold py-1 mb-1 shadow-sm action-btn" disabled
                                    style="font-size: 0.75rem;" data-toggle="modal" data-target="#modalConfigurarLote">
                                    <i class="fas fa-calendar-plus mr-1"></i> Programar Lote (<span
                                        id="contador-lote">0</span>)
                                </button>

                                <!-- Botón 2: Edición Masiva Alternativa -->
                                <button type="button" id="btn-editar-bloque"
                                    class="btn btn-success btn-sm font-weight-bold py-1 mb-1 shadow-sm action-btn" disabled
                                    style="font-size: 0.75rem;" data-toggle="modal" data-target="#modalEditarLote">
                                    <i class="fas fa-edit mr-1"></i> Editar (<span id="contador-lote-edit">0</span>)
                                </button>

                                <!-- Botón 3: Eliminar Lote -->
                                <button type="button" id="btn-tercer-bloque"
                                    class="btn btn-danger btn-sm font-weight-bold py-1 shadow-sm action-btn" disabled
                                    style="font-size: 0.75rem;" data-toggle="modal" data-target="#modalEliminarMasivo">
                                    <i class="fas fa-trash-alt mr-1"></i> Eliminar Lote (<span
                                        id="contador-lote-tercer">0</span>)
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- ================= MODAL DE CONFIGURACIÓN MASIVA ================= -->
        <div class="modal fade" id="modalConfigurarLote" tabindex="-1" role="dialog"
            aria-labelledby="modalConfigurarLoteLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary py-2">
                        <h5 class="modal-title font-weight-bold text-white" id="modalConfigurarLoteLabel"
                            style="font-size: 0.95rem;">
                            <i class="fas fa-cogs mr-1"></i> Configuración Previa de Instancias para el Lote
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body bg-light">
                        <p class="small text-muted mb-3">
                            Seleccione las instancias evaluativas que desea habilitar y configure las observaciones globales
                            para las materias marcadas.
                        </p>

                        <!-- Select de Instancia Sugerida -->
                        <div class="form-group mb-3">
                            <label class="small font-weight-bold text-dark mb-1">Instancia Sugerida para el Lote:</label>
                            <select name="instancia_sugerida" id="instancia_sugerida_lote"
                                class="form-control form-control-sm" required>
                                <option value="">-- Seleccione la instancia --</option>
                                <option value="TP1">Trabajo Práctico 1 (TP1)</option>
                                <option value="TP2">Trabajo Práctico 2 (TP2)</option>
                                <option value="TP3">Trabajo Práctico 3 (TP3)</option>
                                <option value="P1">Primer Parcial (P1)</option>
                                <option value="P2">Segundo Parcial (P2)</option>
                                <option value="EF">Examen Final (EF)</option>
                                <option value="2T">Segunda Instancia / Turno (2T)</option>
                            </select>
                        </div>

                        <!-- Campo Observaciones -->
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold text-dark mb-1">Observaciones Globales /
                                Comentarios:</label>
                            <textarea name="observaciones_sugeridas" id="observaciones_sugeridas" class="form-control form-control-sm"
                                rows="3" placeholder="Ingrese alguna instrucción general para este lote..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-white py-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                        <button type="button" id="btn-ejecutar-programacion-lote"
                            class="btn btn-primary btn-sm font-weight-bold">
                            <i class="fas fa-arrow-right mr-1"></i> Continuar al Formulario
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= MODAL DE SELECCIÓN PARA EDICIÓN ================= -->
        <div class="modal fade" id="modalEditarLote" tabindex="-1" role="dialog"
            aria-labelledby="modalEditarLoteLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-success py-2">
                        <h5 class="modal-title font-weight-bold text-white" id="modalEditarLoteLabel"
                            style="font-size: 0.95rem;">
                            <i class="fas fa-edit mr-1"></i> Seleccionar Instancia a Editar
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body bg-light">
                        <p class="small text-muted mb-3">
                            Indique qué instancia evaluativa desea modificar de las materias seleccionadas:
                        </p>
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold text-dark mb-1">Instancia:</label>
                            <select name="instancia_sugerida" id="instancia_sugerida_edicion"
                                class="form-control form-control-sm" required>
                                <option value="">-- Seleccione la instancia --</option>
                                <option value="TP1">Trabajo Práctico 1 (TP1)</option>
                                <option value="TP2">Trabajo Práctico 2 (TP2)</option>
                                <option value="TP3">Trabajo Práctico 3 (TP3)</option>
                                <option value="P1">Primer Parcial (P1)</option>
                                <option value="P2">Segundo Parcial (P2)</option>
                                <option value="EF">Examen Final (EF)</option>
                                <option value="2T">Segunda Instancia / Turno (2T)</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer bg-white py-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                        <button type="button" id="btn-ejecutar-edicion-lote"
                            class="btn btn-success btn-sm font-weight-bold">
                            <i class="fas fa-arrow-right mr-1"></i> Continuar
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </form>

    <!-- Modal de Confirmación de Eliminación Masiva (Independiente) -->
    <div class="modal fade" id="modalEliminarMasivo" tabindex="-1" role="dialog"
        aria-labelledby="modalEliminarMasivoLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form action="{{ route('admin.programacion-examenes.destroy-masivo') }}" method="POST"
                    id="formEliminarMasivo">
                    @csrf
                    @method('DELETE')

                    <div class="modal-header bg-danger py-2 px-3">
                        <h6 class="modal-title font-weight-bold text-white" id="modalEliminarMasivoLabel">
                            <i class="fas fa-exclamation-triangle mr-1"></i> Confirmar Eliminación por Lote
                        </h6>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body py-3">
                        <p class="mb-2 text-center">¿Está seguro de enviar a la papelera los exámenes seleccionados?</p>

                        <!-- Selector de Instancia a Eliminar de forma Masiva -->
                        <div class="form-group mb-3">
                            <label class="small font-weight-bold text-dark mb-1">Instancia a Eliminar:</label>
                            <select name="instancia_a_eliminar" id="instancia_a_eliminar"
                                class="form-control form-control-sm" required>
                                <option value="">-- Seleccionar Instancia --</option>
                                <option value="TODAS">Todas las instancias (P1, P2, EF, 2T)</option>
                                <option value="P1">Primer Parcial (P1)</option>
                                <option value="P2">Segundo Parcial (P2)</option>
                                <option value="EF">Examen Final (EF)</option>
                                <option value="2T">Segunda Instancia / Turno (2T)</option>
                            </select>
                        </div>

                        <div class="alert alert-warning py-2 px-3 mb-0 text-center">
                            <i class="fas fa-info-circle mr-1"></i> Se procesarán <strong
                                id="modal-cantidad-lote">0</strong> registros seleccionados.
                        </div>
                    </div>

                    <div class="modal-footer bg-light py-2 px-3">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                            <i class="fas fa-times mr-1"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-danger btn-sm font-weight-bold px-3 shadow-sm">
                            <i class="fas fa-trash-alt mr-1"></i> Sí, eliminar lote
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(function() {
            // =========================================================================
            // 0. RESTAURAR FILTROS DESDE EL LOCALSTORAGE AL CARGAR LA PÁGINA
            // =========================================================================
            var pId = localStorage.getItem('filtro_periodo');
            var cId = localStorage.getItem('filtro_carrera');
            var gId = localStorage.getItem('filtro_grado');
            var tId = localStorage.getItem('filtro_turno');
            var paId = localStorage.getItem('filtro_paralelo');
            var txt = localStorage.getItem('filtro_busqueda');

            let hayFiltrosActivos = false;

            if (pId) {
                $('#filtro-periodo').val(pId);
                hayFiltrosActivos = true;
            }
            if (cId) {
                $('#filtro-carrera').val(cId);
                hayFiltrosActivos = true;
            }
            if (gId) {
                $('#filtro-grado').val(gId);
                hayFiltrosActivos = true;
            }
            if (tId) {
                $('#filtro-turno').val(tId);
                hayFiltrosActivos = true;
            }
            if (paId) {
                $('#filtro-paralelo').val(paId);
                hayFiltrosActivos = true;
            }
            if (txt) {
                $('#filtro-busqueda').val(txt);
                hayFiltrosActivos = true;
            }

            // Si hay filtros guardados, abrimos el panel automáticamente y filtramos la tabla
            if (hayFiltrosActivos) {
                var panelFiltros = $('#panel-filtros');
                panelFiltros.removeClass('d-none').addClass('col-md-3 px-1');
                panelFiltros.find('.contenido-lateral').show();
                $('#btn-toggle-filtros').find('.txt-btn-filtros').text('Ocultar Filtros');
                $('#btn-toggle-filtros').removeClass('btn-primary').addClass('btn-outline-primary');
                recalcularAnchoTabla();
                aplicarFiltrosDinamicos(); // Aplica el ocultamiento de filas
            }

            // =========================================================================
            // 1. CONTROL DE PANELES LATERALES (FILTROS Y LOTE)
            // =========================================================================
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

            // =========================================================================
            // 2. ACCIONES DE LOS MODALES MASIVOS (PROGRAMACIÓN Y EDICIÓN)
            // =========================================================================
            $('#btn-ejecutar-programacion-lote').on('click', function(e) {
                e.preventDefault();

                var instancia = $('#instancia_sugerida_lote').val();
                var observaciones = $('#observaciones_sugeridas').val();

                if (!instancia) {
                    alert('Por favor, seleccione una instancia sugerida.');
                    return;
                }

                if ($('#input-instancia-hidden').length === 0) {
                    $('#form-masivo').append(
                        '<input type="hidden" name="instancia_sugerida" id="input-instancia-hidden">');
                }
                $('#input-instancia-hidden').val(instancia);

                if ($('#input-observaciones-hidden').length === 0) {
                    $('#form-masivo').append(
                        '<input type="hidden" name="observaciones_sugeridas" id="input-observaciones-hidden">'
                    );
                }
                $('#input-observaciones-hidden').val(observaciones);

                $('#form-masivo').attr('action', "{{ route('admin.programacion-examenes.create') }}");
                $('#form-masivo').attr('method', 'GET');
                $('#modalConfigurarLote').modal('hide');
                $('#form-masivo').submit();
            });

            $('#btn-ejecutar-edicion-lote').on('click', function(e) {
                e.preventDefault();
                var instanciaSeleccionada = $('#instancia_sugerida_edicion').val();

                if (!instanciaSeleccionada) {
                    alert('Por favor, seleccione una instancia para editar.');
                    return;
                }

                if ($('#input-instancia-editar-hidden').length === 0) {
                    $('#form-masivo').append(
                        '<input type="hidden" name="instancia_a_editar" id="input-instancia-editar-hidden" value="' +
                        instanciaSeleccionada + '">');
                } else {
                    $('#input-instancia-editar-hidden').val(instanciaSeleccionada);
                }

                $('#form-masivo').attr('action', "{{ route('admin.programacion-examenes.edit-masivo') }}");
                $('#form-masivo').attr('method', 'GET');
                $('#modalEditarLote').modal('hide');
                $('#form-masivo').submit();
            });

            // =========================================================================
            // 3. ELIMINACIÓN MASIVA (POST / DELETE independiente)
            // =========================================================================
            $('#btn-tercer-bloque').on('click', function() {
                var seleccionadosCount = $('.oferta-checkbox:checked').length;
                $('#modal-cantidad-lote').text(seleccionadosCount);
            });

            $('#formEliminarMasivo').on('submit', function(e) {
                $(this).find('input[name="ofertas_ids[]"]').remove();

                $('.oferta-checkbox:checked').each(function() {
                    var ofertaId = $(this).val();
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'ofertas_ids[]',
                        value: ofertaId
                    }).appendTo('#formEliminarMasivo');
                });
            });

            // =========================================================================
            // 4. FILTRADO DINÁMICO Y GUARDADO EN LOCALSTORAGE
            // =========================================================================
            function aplicarFiltrosDinamicos() {
                var pId = $('#filtro-periodo').val();
                var cId = $('#filtro-carrera').val();
                var gId = $('#filtro-grado').val();
                var tId = $('#filtro-turno').val();
                var paId = $('#filtro-paralelo').val();
                var txt = $('#filtro-busqueda').val().toLowerCase().trim();

                // Guardar estado actual en localStorage para que persista al cambiar de vista y volver
                localStorage.setItem('filtro_periodo', pId);
                localStorage.setItem('filtro_carrera', cId);
                localStorage.setItem('filtro_grado', gId);
                localStorage.setItem('filtro_turno', tId);
                localStorage.setItem('filtro_paralelo', paId);
                localStorage.setItem('filtro_busqueda', $('#filtro-busqueda')
            .val()); // Guardar con mayúsculas/minúsculas originales

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
            }

            $('#filtro-periodo, #filtro-carrera, #filtro-grado, #filtro-turno, #filtro-paralelo')
                .on('change', aplicarFiltrosDinamicos);
            $('#filtro-busqueda').on('keyup', aplicarFiltrosDinamicos);

            $('#btn-limpiar-filtros').on('click', function() {
                $('#filtro-periodo, #filtro-carrera, #filtro-grado, #filtro-turno, #filtro-paralelo').val(
                    '');
                $('#filtro-busqueda').val('');

                // Limpiar localStorage
                localStorage.removeItem('filtro_periodo');
                localStorage.removeItem('filtro_carrera');
                localStorage.removeItem('filtro_grado');
                localStorage.removeItem('filtro_turno');
                localStorage.removeItem('filtro_paralelo');
                localStorage.removeItem('filtro_busqueda');

                $('.oferta-row').show();
            });

            // =========================================================================
            // 5. CONTROL INTELIGENTE DE INSTANCIAS REGISTRADAS EN MODALES
            // =========================================================================
            $('#btn-abrir-modal-lote').on('click', function() {
                $('#instancia_sugerida_lote option').prop('disabled', false);

                $('.oferta-checkbox:checked').each(function() {
                    var row = $(this).closest('tr');

                    if (row.data('tp1') == 1) $('#instancia_sugerida_lote option[value="TP1"]')
                        .prop('disabled', true);
                    if (row.data('tp2') == 1) $('#instancia_sugerida_lote option[value="TP2"]')
                        .prop('disabled', true);
                    if (row.data('tp3') == 1) $('#instancia_sugerida_lote option[value="TP3"]')
                        .prop('disabled', true);
                    if (row.data('p1') == 1) $('#instancia_sugerida_lote option[value="P1"]').prop(
                        'disabled', true);
                    if (row.data('p2') == 1) $('#instancia_sugerida_lote option[value="P2"]').prop(
                        'disabled', true);
                    if (row.data('ef') == 1) $('#instancia_sugerida_lote option[value="EF"]').prop(
                        'disabled', true);
                    if (row.data('2t') == 1) $('#instancia_sugerida_lote option[value="2T"]').prop(
                        'disabled', true);
                });

                $('#instancia_sugerida_lote').val($('#instancia_sugerida_lote option:enabled:first').val());
            });

            // =========================================================================
            // 6. SINCRONIZACIÓN DE SELECCIONADOS Y CONTADORES
            // =========================================================================
            function actualizarContadorYLista() {
                var contenedor = $('#lista-seleccionadas-container');
                contenedor.empty();

                var totalChecked = $('.oferta-checkbox:checked').length;
                $('#contador-lote, #contador-lote-edit, #contador-lote-tercer').text(totalChecked);

                if (totalChecked > 0) {
                    $('#sin-seleccion').addClass('d-none');
                    contenedor.removeClass('d-none');
                    $('.action-btn').prop('disabled', false);

                    var htmlAcumulado = '';
                    $('.oferta-checkbox:checked').each(function() {
                        var chk = $(this);
                        var row = chk.closest('tr');
                        var id = chk.val();
                        var materiaTexto = row.find('td:eq(1)').find('span').text();
                        var siglaTexto = row.find('td:eq(1)').find('small').text();
                        var carreraTexto = row.find('td:eq(2)').find('span').text();
                        var turnoParaleloTexto = row.find('td:eq(3)').text().trim().replace(/\s+/g, ' ');

                        htmlAcumulado += `
                            <div class="p-1 mb-1 border rounded bg-white shadow-sm d-flex justify-content-between align-items-center item-seleccionado" data-id="${id}" style="font-size: 0.72rem;">
                                <div>
                                    <strong class="text-dark">${materiaTexto}</strong><br>
                                    <span class="text-muted" style="font-size: 0.65rem;">
                                        ${siglaTexto} | ${carreraTexto} <br>
                                        <span class="badge badge-light border px-1">${turnoParaleloTexto}</span>
                                    </span>
                                </div>
                                <button type="button" class="btn btn-xs text-danger quitar-item-btn" data-id="${id}">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        `;
                    });
                    contenedor.html(htmlAcumulado);
                } else {
                    $('#sin-seleccion').removeClass('d-none');
                    contenedor.addClass('d-none');
                    $('.action-btn').prop('disabled', true);
                }
            }

            $(document).on('change', '.oferta-checkbox', function() {
                actualizarContadorYLista();
            });

            $(document).on('click', '.quitar-item-btn', function() {
                var id = $(this).data('id');
                $('#oferta_chk_' + id).prop('checked', false).trigger('change');
            });

            $('#btn-limpiar-seleccion').on('click', function() {
                $('.oferta-checkbox').prop('checked', false).trigger('change');
                $('#seleccionar-todos-visibles').prop('checked', false);
            });

            $('#seleccionar-todos-visibles').on('change', function() {
                var isChecked = $(this).is(':checked');
                $('.oferta-row:visible').find('.oferta-checkbox').prop('checked', isChecked);
                actualizarContadorYLista();
            });
        });
    </script>
@endsection
