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
        $renderFechaVertical = function ($examen, $badgeColorPorDefecto) {
            if (!$examen) {
                return '<span class="text-muted" style="font-size: 0.65rem;">--/--</span>';
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

            if ($modalidad === 'a_ciegas') {
                $urlFolios = route('admin.programacion-examenes.folios.index', $examen->id);
                return '
                <a href="' .
                    $urlFolios .
                    '" class="text-decoration-none d-block w-100 h-100" title="Modalidad A Ciegas: Click para ver Folios" style="transition: opacity 0.2s;" onmouseover="this.style.opacity=\'0.75\'" onmouseout="this.style.opacity=\'1\'">
                    ' .
                    $contenidoHtml .
                    '
                </a>';
            }

            return $contenidoHtml;
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

        <!-- Tus paneles de filtros y tabla -->
        <div class="row">
            ...
        </div>

        <!-- ================= MODAL DE CONFIGURACIÓN MASIVA ================= -->
        <div class="modal fade" id="modalConfigurarLote" ...>
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary py-2">
                        ...
                    </div>
                    <div class="modal-body bg-light">
                        <!-- Tu select de instancia_sugerida -->
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
                    </div>
                    <div class="modal-footer">
                        <!-- Botón submit que envía todo el formulario por GET -->
                        <button type="submit" class="btn btn-primary btn-sm">Continuar a Programación Masiva</button>
                    </div>
                </div>
            </div>
        </div>

    </form> <!-- Cierre del formulario principal que abarca el modal -->

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

            // A. Continuar al formulario de programación por lote (GET)
            $('#btn-ejecutar-programacion-lote').on('click', function(e) {
                e.preventDefault();
                $('#form-masivo').attr('action', "{{ route('admin.programacion-examenes.create') }}");
                $('#form-masivo').attr('method', 'GET');
                $('#modalConfigurarLote').modal('hide');
                $('#form-masivo').submit();
            });

            // B. Continuar a la edición masiva (GET con instancia corregida)
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

            // Inyectar los checkboxes seleccionados al formulario del modal de eliminación específica
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
            // 4. FILTRADO DINÁMICO EN TIEMPO REAL
            // =========================================================================
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
            }

            $('#filtro-periodo, #filtro-carrera, #filtro-grado, #filtro-turno, #filtro-paralelo')
                .on('change', aplicarFiltrosDinamicos);
            $('#filtro-busqueda').on('keyup', aplicarFiltrosDinamicos);

            $('#btn-limpiar-filtros').on('click', function() {
                $('#filtro-periodo, #filtro-carrera, #filtro-grado, #filtro-turno, #filtro-paralelo').val(
                    '');
                $('#filtro-busqueda').val('');
                $('.oferta-row').show();
            });

            // =========================================================================
            // 5. SINCRONIZACIÓN DE SELECCIONADOS Y CONTADORES
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
