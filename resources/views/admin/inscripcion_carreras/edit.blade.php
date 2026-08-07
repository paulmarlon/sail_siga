@extends('adminlte::page')

@section('title', 'Editar Inscripción Masiva | SIG@')

{{-- Activamos los plugins nativos de AdminLTE solo para esta vista sin tocar la configuración global --}}
@section('plugins.Select2', true)
@section('plugins.Sweetalert2', true)

@section('css')
    <style>
        .inscripcion-main-wrapper {
            display: flex;
            flex-direction: column;
            height: calc(100vh - 140px);
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            overflow: hidden;
        }

        .inscripcion-layout {
            display: flex;
            flex-direction: row;
            flex-grow: 1;
            overflow: hidden;
            width: 100%;
            position: relative;
        }

        .columna-seleccion {
            width: 360px;
            min-width: 360px;
            background: #f8f9fa;
            border-right: 1px solid #ddd;
            display: flex;
            flex-direction: column;
            padding: 10px;
        }

        .columna-arrastre {
            flex: 1;
            background: #f4f6f9;
            border-right: 1px solid #ddd;
            display: flex;
            flex-direction: column;
            padding: 10px;
            overflow-y: auto;
        }

        .columna-opciones {
            width: 350px;
            min-width: 350px;
            background: #ffffff;
            display: flex;
            flex-direction: column;
            padding: 15px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 10;
        }

        .columna-opciones.collapsed {
            width: 0;
            min-width: 0;
            overflow: hidden;
            border-left: none;
            padding: 0;
            opacity: 0;
        }

        .lista-destino-arrastre {
            flex-grow: 1;
            min-height: 300px;
            background: #fff;
            border: 2px dashed #ffc107;
            border-radius: 5px;
            padding: 10px;
            overflow-y: auto;
        }

        .estudiante-item-catalogo,
        .estudiante-item-seleccionado {
            background: white;
            border-radius: 3px;
            padding: 8px 10px;
            margin-bottom: 6px;
            font-size: 0.8rem;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .estudiante-item-catalogo {
            border-left: 4px solid #17a2b8;
        }

        .estudiante-item-seleccionado {
            border-left: 4px solid #ffc107;
        }

        .select2-container--bootstrap4 .select2-selection {
            min-height: calc(1.5em + 0.75rem + 2px);
        }
    </style>
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center py-1">
        <h1 class="h4 mb-0"><i class="fas fa-edit mr-2 text-warning"></i> Editar <b>Inscripción Masiva</b></h1>
        <div class="btn-group">
            <button type="button" class="btn btn-sm btn-outline-warning font-weight-bold mr-2" data-toggle="modal"
                data-target="#modalPegarRUs">
                <i class="fas fa-file-excel mr-1"></i> Pegar Lista de RUs (Excel)
            </button>
            <button id="btn-toggle-opciones" class="btn btn-sm btn-info" title="Mostrar/Ocultar Panel de Opciones">
                <i class="fas fa-columns"></i> <span id="toggle-text">Opciones</span>
            </button>
            <a href="{{ route('admin.inscripcion-carreras.index') }}" class="btn btn-sm btn-secondary ml-2">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="inscripcion-main-wrapper">
        {{-- Nota: Ajusta la ruta update según tu controlador (ej: route('admin.inscripcion-carreras.update', $loteId) o similar) --}}
        <form action="{{ route('admin.inscripcion-carreras.update', $inscripcionCarrera->id ?? 0) }}" method="POST"
            id="form-inscripcion" class="d-flex flex-column flex-grow-1">
            @csrf
            @method('PUT')

            <div class="inscripcion-layout">

                <!-- ================= COLUMNA 1: CATÁLOGO DE ESTUDIANTES ================= -->
                <div class="columna-seleccion">
                    <div class="card card-primary card-outline h-100 mb-0 shadow-sm d-flex flex-column">
                        <div class="card-header bg-white py-1 px-2 d-flex justify-content-between align-items-center">
                            <h6 class="card-title text-dark font-weight-bold mb-0" style="font-size: 0.85rem;">
                                <i class="fas fa-search mr-1 text-primary"></i> 1. Catálogo Disponible
                            </h6>
                            <span class="badge badge-primary" id="contador-catalogo">{{ count($estudiantes) }}</span>
                        </div>
                        <div class="card-body d-flex flex-column p-2 flex-grow-1 overflow-hidden">
                            <div class="mb-1">
                                <input type="text" id="filtrarCatalogo" class="form-control form-control-sm py-1"
                                    placeholder="Buscar por CI, Nombre o RU..." style="height: calc(1.5em + 0.5rem + 2px);">
                            </div>
                            <div id="catalogo-origen" class="flex-grow-1 overflow-auto pr-1"
                                style="max-height: calc(100vh - 210px);">
                                @foreach ($estudiantes as $estudiante)
                                    @php
                                        // Verificamos si este estudiante ya está seleccionado en la edición actual
                                        $estaSeleccionado = in_array(
                                            $estudiante->id,
                                            $estudiantesSeleccionadosIds ?? [],
                                        );
                                    @endphp
                                    <div class="estudiante-item-catalogo py-1 px-2 mb-1" data-id="{{ $estudiante->id }}"
                                        data-ru="{{ trim($estudiante->registro_universitario) }}"
                                        data-ci="{{ trim($estudiante->persona->ci) }}"
                                        data-texto="{{ $estudiante->persona->ap_paterno }} {{ $estudiante->persona->ap_materno }} {{ $estudiante->persona->nombres }} - CI: {{ $estudiante->persona->ci }} (RU: {{ $estudiante->registro_universitario }})"
                                        style="{{ $estaSeleccionado ? 'display: none;' : '' }}">
                                        <span class="font-weight-bold text-dark text-truncate mr-2"
                                            style="font-size: 0.72rem; line-height: 1.1;">
                                            {{ $estudiante->persona->ap_paterno }} {{ $estudiante->persona->ap_materno }}
                                            {{ $estudiante->persona->nombres }}
                                            <br>
                                            <small class="text-muted">CI: {{ $estudiante->persona->ci }} | RU:
                                                {{ $estudiante->registro_universitario }}</small>
                                        </span>
                                        <button type="button"
                                            class="btn btn-xs btn-outline-success btn-agregar-estudiante p-0 px-1"
                                            title="Añadir estudiante">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ================= COLUMNA 2: ZONA ACTIVA DE INSCRIPCIÓN ================= -->
                <div class="columna-arrastre">
                    <div class="card card-warning card-outline h-100 mb-0 shadow-sm d-flex flex-column">
                        <div class="card-header bg-white py-1 px-2 d-flex justify-content-between align-items-center">
                            <h6 class="card-title text-dark font-weight-bold mb-0" style="font-size: 0.85rem;">
                                <i class="fas fa-users mr-1 text-warning"></i> 2. Estudiantes Inscritos en el Lote
                            </h6>
                            <span class="badge badge-warning text-dark" id="contador-seleccionados">0 sel.</span>
                        </div>
                        <div class="card-body d-flex flex-column p-2 flex-grow-1">
                            <p class="text-muted small mb-1" style="font-size: 0.72rem; line-height: 1.2;"><i
                                    class="fas fa-info-circle"></i> Modifica la lista agregando o quitando estudiantes según
                                sea necesario.</p>

                            <div id="zona-grabacion" class="lista-destino-arrastre p-1 overflow-auto"
                                style="max-height: calc(100vh - 210px);">
                                <div id="vacio-mensaje" class="text-center text-muted py-5 small" style="display: none;">
                                    <i class="fas fa-hand-pointer fa-2x mb-2 text-muted opacity-50"></i><br>
                                    No hay estudiantes en este lote.
                                </div>

                                {{-- Precarga dinámica de estudiantes ya seleccionados previamente --}}
                                @foreach ($estudiantes as $estudiante)
                                    @if (in_array($estudiante->id, $estudiantesSeleccionadosIds ?? []))
                                        <div class="estudiante-item-seleccionado py-1 px-2 mb-1"
                                            data-id="{{ $estudiante->id }}">
                                            <input type="hidden" name="estudiante_id[]" value="{{ $estudiante->id }}">
                                            <span class="font-weight-bold text-dark text-truncate mr-2"
                                                style="font-size: 0.72rem; line-height: 1.1;">
                                                {{ $estudiante->persona->ap_paterno }}
                                                {{ $estudiante->persona->ap_materno }} {{ $estudiante->persona->nombres }}
                                                - CI: {{ $estudiante->persona->ci }} (RU:
                                                {{ $estudiante->registro_universitario }})
                                            </span>
                                            <button type="button" class="btn btn-xs text-danger btn-remover-estudiante p-0"
                                                title="Quitar">
                                                <i class="fas fa-times-circle"></i>
                                            </button>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ================= COLUMNA 3: PARÁMETROS DE LA INSCRIPCIÓN ================= -->
                <div class="columna-opciones" id="panel-opciones">
                    <div class="card card-warning card-outline h-100 mb-0 shadow-sm d-flex flex-column">
                        <div class="card-header bg-white py-2">
                            <h6 class="card-title text-dark font-weight-bold mb-0"><i
                                    class="fas fa-sliders-h mr-1 text-warning"></i> 3. Datos de Carrera y Periodo</h6>
                        </div>
                        <div class="card-body d-flex flex-column p-3 flex-grow-1 overflow-auto">
                            <div class="flex-grow-1">
                                <div class="form-group mb-2">
                                    <label for="carrera_id" class="small font-weight-bold mb-1">Carrera:</label>
                                    <select name="carrera_id" id="carrera_id" class="form-control form-control-sm" required>
                                        <option value="">Seleccione carrera</option>
                                        @foreach ($carreras as $carrera)
                                            <option value="{{ $carrera->id }}"
                                                {{ $inscripcionCarrera->carrera_id == $carrera->id ? 'selected' : '' }}>
                                                {{ $carrera->nombre }} ({{ $carrera->sigla }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group mb-2">
                                    <label for="periodo_id" class="small font-weight-bold mb-1">Periodo Académico:</label>
                                    <select name="periodo_id" id="periodo_id" class="form-control form-control-sm"
                                        required>
                                        <option value="">Seleccione periodo</option>
                                        @foreach ($periodos as $periodo)
                                            <option value="{{ $periodo->id }}"
                                                {{ isset($inscripcionCarrera) && $inscripcionCarrera->periodo_id == $periodo->id ? 'selected' : '' }}>
                                                {{ $periodo->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group mb-2">
                                    <label for="fecha_inscripcion" class="small font-weight-bold mb-1">Fecha de
                                        Inscripción:</label>
                                    <input type="date" name="fecha_inscripcion" id="fecha_inscripcion"
                                        class="form-control form-control-sm"
                                        value="{{ isset($inscripcionCarrera) && $inscripcionCarrera->fecha_inscripcion ? $inscripcionCarrera->fecha_inscripcion->format('Y-m-d') : date('Y-m-d') }}"
                                        required>
                                </div>

                                <div class="form-group mb-2">
                                    <label for="estado_id" class="small font-weight-bold mb-1">Estado de
                                        Inscripción:</label>
                                    <select name="estado_id" id="estado_id" class="form-control form-control-sm"
                                        required>
                                        @foreach ($estados as $estado)
                                            <option value="{{ $estado->id }}"
                                                {{ isset($inscripcionCarrera) && $inscripcionCarrera->estado_id == $estado->id ? 'selected' : '' }}>
                                                {{ $estado->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group form-check mb-2 mt-3">
                                    <input type="checkbox" name="es_especialidad_activa" id="es_especialidad_activa"
                                        class="form-check-input" value="1"
                                        {{ isset($inscripcionCarrera) && $inscripcionCarrera->es_especialidad_activa ? 'checked' : '' }}>
                                    <label class="form-check-label small font-weight-bold"
                                        for="es_especialidad_activa">¿Especialidad Activa?</label>
                                </div>
                            </div>

                            <div class="border-top pt-3 mt-auto">
                                <button type="submit"
                                    class="btn btn-sm btn-warning btn-block font-weight-bold shadow-sm text-dark">
                                    <i class="fas fa-sync-alt mr-1"></i> Actualizar Inscripción Masiva
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>

    <!-- ================= MODAL PARA PEGAR LISTA DE RUS (EXCEL) ================= -->
    <div class="modal fade" id="modalPegarRUs" tabindex="-1" role="dialog" aria-labelledby="modalPegarRUsLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning py-2">
                    <h5 class="modal-title font-weight-bold text-dark" id="modalPegarRUsLabel"
                        style="font-size: 0.95rem;">
                        <i class="fas fa-file-excel mr-1"></i> Importar RUs desde Excel (Copiar y Pegar)
                    </h5>
                    <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-2">
                        Copia la columna de <b>Registros Universitarios (RU)</b> o <b>Carnets de Identidad (CI)</b>
                        directamente desde tu planilla de Excel y pégala en el siguiente recuadro (un registro por línea):
                    </p>
                    <div class="form-group mb-0">
                        <textarea id="textarea-rus" class="form-control" rows="8" placeholder="Ej:&#10;RU001&#10;RU002&#10;RU003"></textarea>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" id="btn-procesar-rus"
                        class="btn btn-sm btn-warning font-weight-bold text-dark">
                        <i class="fas fa-check mr-1"></i> Procesar y Añadir Estudiantes
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            function actualizarContadores() {
                const totalSeleccionados = $('#zona-grabacion .estudiante-item-seleccionado').length;
                $('#contador-seleccionados').text(totalSeleccionados + ' sel.');

                if (totalSeleccionados > 0) {
                    $('#vacio-mensaje').hide();
                } else {
                    $('#vacio-mensaje').show();
                }
            }

            // Inicializar contadores al cargar la vista
            actualizarContadores();

            // 1. Filtrado rápido del Catálogo
            $('#filtrarCatalogo').on('keyup', function() {
                let val = $(this).val().toLowerCase().trim();
                $('#catalogo-origen .estudiante-item-catalogo').filter(function() {
                    let textoItem = $(this).attr('data-texto').toLowerCase();
                    // Solo filtra si el elemento sigue visible en el catálogo (no está ya seleccionado)
                    if ($(this).data('is-selected') !== true) {
                        $(this).toggle(textoItem.indexOf(val) > -1);
                    }
                });
            });

            // Función para mover un estudiante del catálogo a la zona activa
            function agregarEstudianteAZona(itemPadre) {
                const id = itemPadre.attr('data-id');
                const textoCompleto = itemPadre.attr('data-texto');

                if ($(`#zona-grabacion input[value="${id}"]`).length > 0) {
                    return;
                }

                const htmlItem = `
                    <div class="estudiante-item-seleccionado py-1 px-2 mb-1" data-id="${id}">
                        <input type="hidden" name="estudiante_id[]" value="${id}">
                        <span class="font-weight-bold text-dark text-truncate mr-2" style="font-size: 0.72rem; line-height: 1.1;">
                            ${textoCompleto}
                        </span>
                        <button type="button" class="btn btn-xs text-danger btn-remover-estudiante p-0" title="Quitar">
                            <i class="fas fa-times-circle"></i>
                        </button>
                    </div>
                `;

                $('#zona-grabacion').append(htmlItem);
                itemPadre.hide().data('is-selected', true);
                actualizarContadores();
            }

            // 2. Agregar estudiante individualmente al hacer clic en (+)
            $(document).on('click', '.btn-agregar-estudiante', function() {
                const itemPadre = $(this).closest('.estudiante-item-catalogo');
                agregarEstudianteAZona(itemPadre);
            });

            // 3. Remover estudiante de la zona activa y devolverlo al catálogo izquierdo
            $(document).on('click', '.btn-remover-estudiante', function() {
                const itemSeleccionado = $(this).closest('.estudiante-item-seleccionado');
                const id = itemSeleccionado.attr('data-id');

                itemSeleccionado.fadeOut(200, function() {
                    $(this).remove();
                    const itemCat = $(
                        `#catalogo-origen .estudiante-item-catalogo[data-id="${id}"]`);
                    itemCat.data('is-selected', false).show();
                    actualizarContadores();
                });
            });

            // 4. Procesar lista pegada desde Excel (RUs o CIs)
            $('#btn-procesar-rus').on('click', function() {
                const textoPegado = $('#textarea-rus').val();
                if (!textoPegado.trim()) {
                    Swal.fire('Atención', 'Por favor, pega los registros en el recuadro.', 'warning');
                    return;
                }

                const lineas = textoPegado.split(/\r?\n/).map(l => l.trim()).filter(l => l.length > 0);

                let agregados = 0;
                let noEncontrados = [];

                lineas.forEach(valor => {
                    const itemCatalogo = $(`#catalogo-origen .estudiante-item-catalogo`).filter(
                        function() {
                            const ru = $(this).attr('data-ru');
                            const ci = $(this).attr('data-ci');
                            return (ru.toLowerCase() === valor.toLowerCase() || ci
                                .toLowerCase() === valor.toLowerCase());
                        });

                    if (itemCatalogo.length > 0 && itemCatalogo.is(':visible')) {
                        agregarEstudianteAZona(itemCatalogo.first());
                        agregados++;
                    } else {
                        const yaAgregado = $(`#zona-grabacion input[name="estudiante_id[]"]`)
                            .toArray().some(input => {
                                const idEst = $(input).val();
                                const target = $(
                                    `#catalogo-origen .estudiante-item-catalogo[data-id="${idEst}"]`
                                );
                                return target.attr('data-ru').toLowerCase() === valor
                                    .toLowerCase() ||
                                    target.attr('data-ci').toLowerCase() === valor
                                    .toLowerCase();
                            });

                        if (!yaAgregado) {
                            noEncontrados.push(valor);
                        }
                    }
                });

                $('#modalPegarRUs').modal('hide');
                $('#textarea-rus').val('');

                let mensajeSwal = `Se añadieron ${agregados} estudiantes correctamente.`;
                if (noEncontrados.length > 0) {
                    mensajeSwal +=
                        `<br><br><span class="text-danger small">No se encontraron o ya estaban agregados: ${noEncontrados.join(', ')}</span>`;
                }

                Swal.fire({
                    title: 'Proceso de importación',
                    html: mensajeSwal,
                    icon: agregados > 0 ? 'success' : 'warning',
                    confirmButtonColor: '#ffc107'
                });
            });

            // 5. Toggle Panel Opciones
            $('#btn-toggle-opciones').on('click', function() {
                $('#panel-opciones').toggleClass('collapsed');
                const isCollapsed = $('#panel-opciones').hasClass('collapsed');
                $('#toggle-text').text(isCollapsed ? 'Mostrar Opciones' : 'Opciones');
                $(this).find('i').toggleClass('fa-columns fa-indent');
            });

            // 6. Validación previa al envío
            $('#form-inscripcion').on('submit', function(e) {
                if ($('#zona-grabacion input[name="estudiante_id[]"]').length === 0) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Sin estudiantes seleccionados',
                        text: 'Debes mantener al menos un estudiante en el lote para actualizar la inscripción masiva.',
                        icon: 'warning',
                        confirmButtonColor: '#ffc107'
                    });
                }
            });
        });
    </script>
@stop
