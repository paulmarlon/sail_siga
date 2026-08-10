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

                            <!-- Instancia de Examen -->
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold text-secondary mb-1">Instancia de Examen:</label>
                                <select id="filtro-instancia" class="form-control form-control-sm"
                                    style="font-size: 0.75rem;">
                                    <option value="">-- Todas las Instancias --</option>
                                    <option value="P1">Primer Parcial</option>
                                    <option value="P2">Segundo Parcial</option>
                                    <option value="EF">Examen Final</option>
                                    <option value="2T">Segunda Instancia</option>
                                </select>
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
                                    <th class="text-center" style="width: 25px;"><i class="fas fa-check-square"></i>
                                    </th>
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

                                            <!-- Instancias badges -->
                                            <td class="text-center"><span
                                                    class="badge {{ $p1 ? 'badge-success' : 'badge-light text-muted border' }} px-1">{{ $p1 ? 'Sí' : '-' }}</span>
                                            </td>
                                            <td class="text-center"><span
                                                    class="badge {{ $p2 ? 'badge-success' : 'badge-light text-muted border' }} px-1">{{ $p2 ? 'Sí' : '-' }}</span>
                                            </td>
                                            <td class="text-center"><span
                                                    class="badge {{ $ef ? 'badge-primary' : 'badge-light text-muted border' }} px-1">{{ $ef ? 'Sí' : '-' }}</span>
                                            </td>
                                            <td class="text-center"><span
                                                    class="badge {{ $si ? 'badge-warning' : 'badge-light text-muted border' }} px-1">{{ $si ? 'Sí' : '-' }}</span>
                                            </td>

                                            <td class="text-center">
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
                                <!-- Botón 1: Programación Masiva -->
                                <button type="submit" id="btn-procesar-bloque"
                                    class="btn btn-primary btn-sm font-weight-bold py-1 mb-1 shadow-sm action-btn" disabled
                                    style="font-size: 0.75rem;"
                                    data-action="{{ route('admin.programacion-examenes.create') }}" data-method="GET">
                                    <i class="fas fa-calendar-plus mr-1"></i> Programar Lote (<span
                                        id="contador-lote">0</span>)
                                </button>

                                <!-- Botón 2: Edición Masiva Alternativa -->
                                <button type="submit" id="btn-editar-bloque"
                                    class="btn btn-success btn-sm font-weight-bold py-1 mb-1 shadow-sm action-btn" disabled
                                    style="font-size: 0.75rem;"
                                    data-action="{{ route('admin.programacion-examenes.edit-masivo') }}"
                                    data-method="GET">
                                    <i class="fas fa-edit mr-1"></i> Editar Lote (<span id="contador-lote-edit">0</span>)
                                </button>

                                <!-- ================= TERCER BOTÓN (Ej. Eliminar / Reporte en Lote) ================= -->
                                <button type="submit" id="btn-tercer-bloque"
                                    class="btn btn-danger btn-sm font-weight-bold py-1 shadow-sm action-btn" disabled
                                    style="font-size: 0.75rem;"
                                    data-action="{{ route('admin.programacion-examenes.destroy-masivo') }}"
                                    data-method="POST">
                                    <i class="fas fa-trash-alt mr-1"></i> Eliminar Lote (<span
                                        id="contador-lote-tercer">0</span>)
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </form>
@stop

@section('js')
    <script>
        $(function() {
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

            // 3. Función exclusiva para adaptar el ancho de la tabla central
            function recalcularAnchoTabla() {
                var anchoCentral = 12;

                if (!$('#panel-filtros').hasClass('d-none')) {
                    anchoCentral -= 3;
                }

                if (!$('#panel-seleccion').hasClass('d-none')) {
                    anchoCentral -= 3;
                }

                $('#panel-tabla').removeClass('col-md-5 col-md-6 col-md-7 col-md-9 col-md-10 col-md-12')
                    .addClass('col-md-' + anchoCentral);
            }

            // Capturar la acción del botón presionado para configurar el formulario dinámicamente al enviar
            $('.action-btn').on('click', function(e) {
                var targetAction = $(this).data('action');
                var targetMethod = $(this).data('method');

                $('#form-masivo').attr('action', targetAction);
                $('#form-masivo').attr('method', targetMethod);
            });

            // FILTRADO DINÁMICO EN TIEMPO REAL
            function aplicarFiltrosDinamicos() {
                var pId = $('#filtro-periodo').val();
                var cId = $('#filtro-carrera').val();
                var gId = $('#filtro-grado').val();
                var tId = $('#filtro-turno').val();
                var paId = $('#filtro-paralelo').val();
                var inst = $('#filtro-instancia').val();
                var txt = $('#filtro-busqueda').val().toLowerCase().trim();

                $('.oferta-row').each(function() {
                    var row = $(this);
                    var match = true;

                    if (pId && row.data('periodo') != pId) match = false;
                    if (cId && row.data('carrera') != cId) match = false;
                    if (gId && row.data('grado') != gId) match = false;
                    if (tId && row.data('turno') != tId) match = false;
                    if (paId && row.data('paralelo') != paId) match = false;

                    if (inst) {
                        if (inst === 'P1' && row.data('p1') != 1) match = false;
                        if (inst === 'P2' && row.data('p2') != 1) match = false;
                        if (inst === 'EF' && row.data('ef') != 1) match = false;
                        if (inst === '2T' && row.data('2t') != 1) match = false;
                    }

                    if (txt && row.data('texto').indexOf(txt) === -1) match = false;

                    if (match) {
                        row.show();
                    } else {
                        row.hide();
                    }
                });
            }

            $('#filtro-periodo, #filtro-carrera, #filtro-grado, #filtro-turno, #filtro-paralelo, #filtro-instancia')
                .on('change', aplicarFiltrosDinamicos);
            $('#filtro-busqueda').on('keyup', aplicarFiltrosDinamicos);

            $('#btn-limpiar-filtros').on('click', function() {
                $('#filtro-periodo, #filtro-carrera, #filtro-grado, #filtro-turno, #filtro-paralelo, #filtro-instancia')
                    .val('');
                $('#filtro-busqueda').val('');
                $('.oferta-row').show();
            });

            // SINCRONIZACIÓN DE SELECCIONADOS
            function actualizarContadorYLista() {
                var contenedor = $('#lista-seleccionadas-container');
                contenedor.empty();

                var totalChecked = $('.oferta-checkbox:checked').length;
                // Actualizamos los contadores de todos los botones de acción masiva
                $('#contador-lote, #contador-lote-edit, #contador-lote-tercer').text(totalChecked);

                if (totalChecked > 0) {
                    $('#sin-seleccion').addClass('d-none');
                    contenedor.removeClass('d-none');
                    $('.action-btn').prop('disabled', false);

                    $('.oferta-checkbox:checked').each(function() {
                        var chk = $(this);
                        var row = chk.closest('tr');
                        var id = chk.val();
                        var materiaTexto = row.find('td:eq(1)').find('span').text();
                        var siglaTexto = row.find('td:eq(1)').find('small').text();
                        var carreraTexto = row.find('td:eq(2)').find('span').text();
                        var turnoParaleloTexto = row.find('td:eq(3)').text().trim().replace(/\s+/g, ' ');

                        var itemHtml = `
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
                        contenedor.append(itemHtml);
                    });
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
@stop
