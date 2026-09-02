@extends('adminlte::page')

@section('title', 'Gestión de Folios y Tacos (A Ciegas)')

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

        #tabla-folios th,
        #tabla-folios td {
            padding: 0.35rem 0.5rem !important;
            vertical-align: middle !important;
            font-size: 0.78rem;
        }

        .col-transicion {
            transition: all 0.3s ease-in-out;
        }
    </style>
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fas fa-file-invoice text-primary mr-2"></i> Gestión de Folios y Tacos (Exámenes a Ciegas)</h1>
            <p class="text-muted mb-0">Control de codificación por examen programado y asignación de folios estudiantiles.
            </p>
        </div>
        <div>
            <a href="{{ route('admin.folio-examens.papelera') }}"
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
    <form action="" method="GET" id="form-masivo">
        @csrf
        <div id="method-spoofing-container"></div>

        <!-- BARRA ESTRATÉGICA DE CONTROL DE PANELES -->
        <div class="row mb-2">
            <div class="col-12 d-flex justify-content-between align-items-center bg-light p-2 rounded border shadow-sm">
                <div>
                    <button type="button" id="btn-toggle-filtros"
                        class="btn btn-outline-primary btn-xs font-weight-bold shadow-sm">
                        <i class="fas fa-filter mr-1"></i> <span class="txt-btn-filtros">Ocultar Filtros</span>
                    </button>
                </div>
                <div class="small text-muted font-weight-bold">
                    <i class="fas fa-columns mr-1"></i> Paneles Laterales (Folios)
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
                    <div class="card-header bg-white py-2 px-2">
                        <h6 class="card-title text-dark font-weight-bold mb-0" style="font-size: 0.85rem;">
                            <i class="fas fa-filter mr-1 text-primary"></i> 1. Filtros
                        </h6>
                    </div>
                    <div class="card-body p-2 d-flex flex-column justify-content-between contenido-lateral">
                        <div>
                            <!-- Periodo -->
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

                            <!-- Carrera -->
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

                            <!-- Grado -->
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold text-secondary mb-1">Grado / Semestre:</label>
                                <select id="filtro-grado" class="form-control form-control-sm" style="font-size: 0.75rem;">
                                    <option value="">-- Todos los Grados --</option>
                                    @foreach ($grados as $gra)
                                        <option value="{{ $gra->id }}">{{ $gra->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Turno y Paralelo -->
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

                            <!-- Instancia Evaluativa -->
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold text-secondary mb-1">Instancia de Examen:</label>
                                <select id="filtro-instancia" class="form-control form-control-sm"
                                    style="font-size: 0.75rem;">
                                    <option value="">-- Todas las Instancias --</option>
                                    <option value="P1">Primer Parcial (P1)</option>
                                    <option value="P2">Segundo Parcial (P2)</option>
                                    <option value="EF">Examen Final (EF)</option>
                                    <option value="2T">Segunda Instancia (2T)</option>
                                </select>
                            </div>

                            <!-- Buscador -->
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold text-secondary mb-1">Materia (Nombre / Sigla):</label>
                                <input type="text" id="filtro-busqueda" class="form-control form-control-sm"
                                    placeholder="Ej. MAT-101..." style="font-size: 0.75rem;">
                            </div>
                        </div>

                        <div>
                            <button type="button" id="btn-limpiar-filtros"
                                class="btn btn-xs btn-outline-secondary btn-block" style="font-size: 0.75rem;">
                                <i class="fas fa-sync-alt mr-1"></i> Limpiar Filtros
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ================= COLUMNA 2: TABLA CENTRAL (EXÁMENES / OFERTAS) ================= -->
            <div class="col-md-6 px-1 col-transicion" id="panel-tabla">
                <div class="card card-success card-outline card-scroll shadow-sm mb-0">
                    <div class="card-header bg-white py-2 px-2 d-flex justify-content-between align-items-center">
                        <h6 class="card-title text-dark font-weight-bold mb-0" style="font-size: 0.85rem;">
                            <i class="fas fa-list mr-1 text-success"></i> 2. Exámenes Programados (Clic para Gestionar
                            Folios)
                        </h6>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="seleccionar-todos-visibles">
                            <label class="custom-control-label small font-weight-bold text-dark cursor-pointer"
                                for="seleccionar-todos-visibles">Seleccionar Visibles</label>
                        </div>
                    </div>
                    <div class="card-body p-2 card-body-scroll">
                        <table id="tabla-folios"
                            class="table table-bordered table-striped table-hover text-nowrap w-100 mb-0">
                            <thead class="thead-dark" style="font-size: 0.75rem;">
                                <tr>
                                    <th class="text-center" style="width: 25px;"><i class="fas fa-check-square"></i></th>
                                    <th>Materia / Sigla</th>
                                    <th>Instancia & Modalidad</th>
                                    <th>Carrera / Semestre / Periodo</th>
                                    <th class="text-center">T / P</th>
                                    <th class="text-center">Folios Gen.</th>
                                    <th class="text-center" style="width: 70px;">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if (isset($listaProgramaciones))
                                    @foreach ($listaProgramaciones as $prog)
                                        @php
                                            $oferta = $prog->ofertaAcademica;
                                            $nombMateria = strtolower($oferta->pensum->materia->nombre ?? '');
                                            $siglaMateria = strtolower($oferta->pensum->materia->sigla ?? '');
                                            $idPeriodo = $oferta->periodo_id ?? '';
                                            $idCarrera = $oferta->pensum->carrera_id ?? '';
                                            $idGrado = $oferta->pensum->grado_id ?? '';
                                            $idTurno = $oferta->turno_id ?? '';
                                            $idParalelo = $oferta->paralelo_id ?? '';
                                            $instancia = $prog->instancia ?? '';

                                            $totalFolios = $prog->foliosExamen->count();
                                        @endphp
                                        <tr class="oferta-row" data-periodo="{{ $idPeriodo }}"
                                            data-carrera="{{ $idCarrera }}" data-grado="{{ $idGrado }}"
                                            data-turno="{{ $idTurno }}" data-paralelo="{{ $idParalelo }}"
                                            data-instancia="{{ $instancia }}"
                                            data-texto="{{ $nombMateria }} {{ $siglaMateria }}">

                                            <!-- Checkbox de Lote -->
                                            <td class="text-center">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" name="programacion_ids[]"
                                                        value="{{ $prog->id }}" id="prog_chk_{{ $prog->id }}"
                                                        class="custom-control-input oferta-checkbox">
                                                    <label class="custom-control-label"
                                                        for="prog_chk_{{ $prog->id }}"></label>
                                                </div>
                                            </td>

                                            <!-- Materia -->
                                            <td>
                                                <span
                                                    class="font-weight-bold text-dark">{{ $oferta->pensum->materia->nombre ?? 'S/N' }}</span><br>
                                                <small
                                                    class="text-muted">{{ $oferta->pensum->materia->sigla ?? 'S/S' }}</small>
                                            </td>

                                            <!-- Instancia y Modalidad -->
                                            <td>
                                                <span class="badge badge-primary px-1">{{ $prog->instancia }}</span>
                                                <span
                                                    class="badge badge-secondary px-1">{{ ucfirst($prog->modalidad) }}</span><br>
                                                <small
                                                    class="text-muted">{{ \Carbon\Carbon::parse($prog->fecha_programada)->format('d/m/Y') }}</small>
                                            </td>

                                            <!-- Carrera / Grado / Periodo -->
                                            <td>
                                                <span
                                                    class="text-secondary font-weight-bold">{{ $oferta->pensum->carrera->nombre ?? 'S/C' }}</span><br>
                                                <small
                                                    class="text-muted">{{ $oferta->pensum->grado->nombre ?? 'S/G' }}</small>
                                                @if (isset($oferta->periodo))
                                                    <span class="badge badge-info float-right" style="font-size: 0.65rem;"
                                                        title="Periodo">
                                                        {{ $oferta->periodo->nombre }}
                                                    </span>
                                                @endif
                                            </td>

                                            <!-- Turno / Paralelo -->
                                            <td class="text-center">
                                                <span
                                                    class="badge badge-light border">{{ $oferta->turno->nombre ?? 'S/T' }}</span>
                                                <span
                                                    class="badge badge-light border">{{ $oferta->paralelo->nombre ?? 'S/P' }}</span>
                                            </td>

                                            <!-- Total Folios Generados -->
                                            <td class="text-center">
                                                <span
                                                    class="badge {{ $totalFolios > 0 ? 'badge-success' : 'badge-warning' }} px-2"
                                                    style="font-size: 0.75rem;">
                                                    {{ $totalFolios }} generados
                                                </span>
                                            </td>

                                            <!-- Acción: Enlace a Plantilla de Foliación -->
                                            <td class="text-center">
                                                <a href="{{ route('admin.folio-examens.plantilla', $prog->id) }}"
                                                    class="btn btn-info btn-xs px-2 font-weight-bold"
                                                    title="Gestionar Plantilla de Folios y Notas">
                                                    <i class="fas fa-file-invoice mr-1"></i> Folear
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

            <!-- ================= COLUMNA 3: PANEL DE LOTE Y ACCIONES (DERECHA) ================= -->
            <div class="col-md-3 px-1 col-transicion" id="panel-seleccion">
                <div class="card card-warning card-outline card-scroll shadow-sm mb-0 d-flex flex-column">
                    <div class="card-header bg-white py-2 px-2 d-flex justify-content-between align-items-center">
                        <h6 class="card-title text-dark font-weight-bold mb-0" style="font-size: 0.85rem;">
                            3. Lote de Folios
                        </h6>
                        <button type="button" id="btn-limpiar-seleccion" class="btn btn-xs btn-outline-secondary"
                            style="font-size: 0.60rem;">Limpiar</button>
                    </div>
                    <div
                        class="card-body p-2 card-body-scroll d-flex flex-column justify-content-between contenido-lateral">

                        <div class="flex-grow-1 overflow-hidden d-flex flex-column">
                            <div id="sin-seleccion" class="text-muted text-center py-4">
                                <i class="fas fa-hand-pointer fa-2x mb-2 text-secondary"></i>
                                <p class="small mb-0">Selecciona exámenes para operaciones en lote.</p>
                            </div>
                            <div id="lista-seleccionadas-container" class="flex-grow-1 overflow-auto pr-1 d-none">
                                <!-- Contenido dinámico por JS -->
                            </div>
                        </div>

                        <!-- Botones de Acción Masiva -->
                        <div class="border-top pt-2 mt-2">
                            <div class="btn-group-vertical w-100">
                                <button type="button" id="btn-generar-masivo-lote"
                                    class="btn btn-primary btn-sm font-weight-bold py-1 mb-1 shadow-sm action-btn" disabled
                                    style="font-size: 0.75rem;" data-toggle="modal" data-target="#modalGeneracionMasiva">
                                    <i class="fas fa-cogs mr-1"></i> Generar Tacos Masivo (<span
                                        id="contador-lote">0</span>)
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </form>

    <!-- Modal para Generación Masiva de Tacos -->
    <div class="modal fade" id="modalGeneracionMasiva" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content shadow-sm">
                <div class="modal-header bg-primary text-white py-2">
                    <h6 class="modal-title font-weight-bold" style="font-size: 0.9rem;">
                        <i class="fas fa-cogs mr-1"></i> Generar Tacos y Folios
                    </h6>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body py-3">
                    <p class="text-muted small mb-2">Se generarán los tacos físicos y códigos únicos para los estudiantes
                        matriculados en las programaciones seleccionadas.</p>
                    <div class="form-group mb-0">
                        <label class="small font-weight-bold text-secondary">Prefijo del Código:</label>
                        <input type="text" id="modal-prefijo-input" class="form-control form-control-sm"
                            value="FOL" placeholder="Ej. FOL">
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-xs" data-dismiss="modal">Cancelar</button>
                    <button type="button" id="btn-ejecutar-generacion-masiva"
                        class="btn btn-primary btn-xs font-weight-bold">
                        <i class="fas fa-check mr-1"></i> Procesar
                    </button>
                </div>
            </div>
        </div>
    </div>
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

            function recalcularAnchoTabla() {
                var anchoCentral = 12;
                if (!$('#panel-filtros').hasClass('d-none')) anchoCentral -= 3;
                if (!$('#panel-seleccion').hasClass('d-none')) anchoCentral -= 3;
                $('#panel-tabla').removeClass('col-md-6 col-md-9 col-md-12').addClass('col-md-' + anchoCentral);
            }

            // Filtrado dinámico en tiempo real
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
                    if (inst && row.data('instancia') != inst) match = false;
                    if (txt && row.data('texto').indexOf(txt) === -1) match = false;

                    if (match) row.show();
                    else row.hide();
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

            // Sincronización de selección de lote
            function actualizarContadorYLista() {
                var contenedor = $('#lista-seleccionadas-container');
                contenedor.empty();

                var totalChecked = $('.oferta-checkbox:checked').length;
                $('#contador-lote').text(totalChecked);

                if (totalChecked > 0) {
                    $('#sin-seleccion').addClass('d-none');
                    contenedor.removeClass('d-none');
                    $('.action-btn').prop('disabled', false);

                    $('.oferta-checkbox:checked').each(function() {
                        var chk = $(this);
                        var row = chk.closest('tr');
                        var id = chk.val();
                        var materia = row.find('td:eq(1)').find('span').text();
                        var sigla = row.find('td:eq(1)').find('small').text();
                        var instancia = row.find('td:eq(2)').find('.badge-primary').text();

                        var itemHtml = `
                            <div class="p-1 mb-1 border rounded bg-white shadow-sm d-flex justify-content-between align-items-center item-seleccionado" data-id="${id}" style="font-size: 0.72rem;">
                                <div>
                                    <strong class="text-dark">${materia} (${instancia})</strong><br>
                                    <span class="text-muted" style="font-size: 0.65rem;">${sigla}</span>
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

            $(document).on('change', '.oferta-checkbox', actualizarContadorYLista);

            $(document).on('click', '.quitar-item-btn', function() {
                var id = $(this).data('id');
                $('#prog_chk_' + id).prop('checked', false).trigger('change');
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

            // Acción de generación masiva mediante AJAX o Formulario dinámico por cada ítem seleccionado
            $('#btn-ejecutar-generacion-masiva').on('click', function() {
                var prefijo = $('#modal-prefijo-input').val();
                var ids = $('.oferta-checkbox:checked').map(function() {
                    return $(this).val();
                }).get();

                if (ids.length === 0) {
                    alert('No hay elementos seleccionados.');
                    return;
                }

                // Disparamos secuencialmente o mediante un formulario dinámico el envío para cada programación seleccionada
                // (O enviamos por lotes al endpoint generar-masivo)
                $('#modalGeneracionMasiva').modal('hide');

                // Creamos un form temporal para procesar el primero o iterar
                var form = $(
                    '<form action="{{ route('admin.folio-examens.generar-masivo') }}" method="POST"></form>'
                    );
                form.append('@csrf');
                form.append('<input type="hidden" name="prefijo" value="' + prefijo + '">');
                // Tomamos por defecto el primero o adaptamos según requerimiento de lote múltiple
                form.append('<input type="hidden" name="programacion_id" value="' + ids[0] + '">');

                $('body').append(form);
                form.submit();
            });
        });
    </script>
@endsection
