@extends('adminlte::page')

@section('title', 'Habilitar Accesos Masivos | SIG@')

@section('css')
    <!-- Select2 y SweetAlert2 ya son inyectados por la configuración de AdminLTE -->
    <style>
        .usuario-main-wrapper {
            display: flex;
            flex-direction: column;
            height: calc(100vh - 140px);
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            overflow: hidden;
        }

        .usuario-layout {
            display: flex;
            flex-direction: row;
            flex-grow: 1;
            overflow: hidden;
            width: 100%;
            position: relative;
        }

        /* Clases de transición y comportamiento colapsable independiente */
        .columna-catalogo,
        .columna-opciones {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 10;
        }

        .columna-catalogo {
            width: 340px;
            min-width: 340px;
            background: #f8f9fa;
            border-right: 1px solid #ddd;
            display: flex;
            flex-direction: column;
            padding: 10px;
        }

        .columna-catalogo.collapsed {
            width: 0;
            min-width: 0;
            overflow: hidden;
            border-right: none;
            padding: 0;
            opacity: 0;
        }

        .columna-arrastre {
            flex: 1;
            background: #f4f6f9;
            border-right: 1px solid #ddd;
            display: flex;
            flex-direction: column;
            padding: 10px;
            overflow-y: auto;
            transition: all 0.3s ease;
        }

        .columna-opciones {
            width: 380px;
            min-width: 380px;
            background: #ffffff;
            display: flex;
            flex-direction: column;
            padding: 15px;
            border-left: 1px solid #ddd;
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
            border: 2px dashed #28a745;
            border-radius: 5px;
            padding: 6px;
            overflow-y: auto;
        }

        .persona-item-catalogo {
            background: white;
            border-radius: 3px;
            padding: 8px 10px;
            margin-bottom: 6px;
            font-size: 0.8rem;
            border-left: 4px solid #17a2b8;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .persona-item-seleccionada {
            background: white;
            border-radius: 3px;
            padding: 6px 8px;
            margin-bottom: 5px;
            border-left: 3px solid #28a745;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }
    </style>
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center py-1">
        <h1 class="h4 mb-0"><i class="fas fa-user-shield mr-2 text-primary"></i> Habilitación Dinámica de Accesos</h1>
        <div class="btn-group">
            <button id="btn-toggle-catalogo" class="btn btn-sm btn-outline-info" title="Mostrar/Ocultar Catálogo">
                <i class="fas fa-search"></i> <span id="toggle-cat-text">Ocultar Catálogo</span>
            </button>
            <button id="btn-toggle-opciones" class="btn btn-sm btn-info" title="Mostrar/Ocultar Panel de Configuración">
                <i class="fas fa-columns"></i> <span id="toggle-opt-text">Ocultar Opciones</span>
            </button>
            <a href="{{ route('admin.usuarios.index') }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>
@stop

@section('content')

    <div class="usuario-main-wrapper">
        <form action="{{ route('admin.usuarios.store') }}" method="POST" id="form-usuarios"
            class="d-flex flex-column flex-grow-1">
            @csrf
            <div class="usuario-layout">

                <!-- ================= COLUMNA 1: CATÁLOGO DE PERSONAS ================= -->
                <div class="columna-catalogo" id="panel-catalogo">
                    <div class="card card-primary card-outline h-100 mb-0 shadow-sm d-flex flex-column">
                        <div class="card-header bg-white py-1 px-2 d-flex justify-content-between align-items-center">
                            <h6 class="card-title text-dark font-weight-bold mb-0" style="font-size: 0.85rem;">
                                <i class="fas fa-search mr-1 text-primary"></i> 1. Catálogo
                            </h6>
                            <button type="button" class="btn btn-xs btn-outline-info p-1" data-toggle="modal"
                                data-target="#modalPegarRUs" title="Pegar desde Excel">
                                <i class="fas fa-file-excel"></i>
                            </button>
                        </div>
                        <div class="card-body d-flex flex-column p-2 flex-grow-1 overflow-hidden">
                            <!-- Buscador Rápido -->
                            <div class="mb-2">
                                <input type="text" id="filtrarCatalogo" class="form-control form-control-sm py-1"
                                    placeholder="Filtrar por CI o Nombre..." style="height: calc(1.5em + 0.5rem + 2px);">
                            </div>

                            <!-- Módulo Plegable de Importación Masiva de CIs -->
                            <div class="card mb-2 shadow-none border-info bg-light">
                                <div class="card-header p-1 bg-info text-white cursor-pointer" data-toggle="collapse"
                                    data-target="#collapseImport" style="cursor: pointer;">
                                    <small class="font-weight-bold"><i class="fas fa-file-upload mr-1"></i> Importación
                                        Masiva (CIs)</small>
                                </div>
                                <div id="collapseImport" class="collapse">
                                    <div class="card-body p-2">
                                        <textarea id="lista-importar" class="form-control form-control-sm mb-1" rows="2"
                                            placeholder="Pega los CIs aquí (separados por coma o salto de línea)..." style="font-size: 0.75rem;"></textarea>
                                        <button type="button" id="btn-procesar-importacion"
                                            class="btn btn-xs btn-info btn-block font-weight-bold">
                                            <i class="fas fa-check-circle mr-1"></i> Procesar Lote
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div id="catalogo-origen" class="flex-grow-1 overflow-auto pr-1"
                                style="max-height: calc(100vh - 330px);">
                                @foreach ($personasSinUsuario as $persona)
                                    @php
                                        $nombresPersona = $persona->nombres ?? '';
                                        $apPaterno = $persona->ap_paterno ?? '';
                                        $apMaterno = $persona->ap_materno ?? '';
                                        $ciPersona = $persona->ci ?? '';

                                        $limpiarTexto = function ($texto) {
                                            $originales = [
                                                'Á',
                                                'É',
                                                'Í',
                                                'Ó',
                                                'Ú',
                                                'Ä',
                                                'Ë',
                                                'Ï',
                                                'Ö',
                                                'Ü',
                                                'Ñ',
                                                'Ç',
                                                'á',
                                                'é',
                                                'í',
                                                'ó',
                                                'ú',
                                                'ä',
                                                'ë',
                                                'ï',
                                                'ö',
                                                'ü',
                                                'ñ',
                                                'ç',
                                            ];
                                            $modificadas = [
                                                'a',
                                                'e',
                                                'i',
                                                'o',
                                                'u',
                                                'a',
                                                'e',
                                                'i',
                                                'o',
                                                'u',
                                                'n',
                                                'c',
                                                'a',
                                                'e',
                                                'i',
                                                'o',
                                                'u',
                                                'a',
                                                'e',
                                                'i',
                                                'o',
                                                'u',
                                                'n',
                                                'c',
                                            ];
                                            $textoLimpio = str_replace($originales, $modificadas, $texto);
                                            return trim(preg_replace('/[\s+]+/', '', $textoLimpio));
                                        };

                                        $nombresLimpios = $limpiarTexto($nombresPersona);
                                        $paternoLimpio = $limpiarTexto($apPaterno);
                                        $maternoLimpio = $limpiarTexto($apMaterno);
                                        $ciLimpio = $limpiarTexto($ciPersona);

                                        $inicialNombre = substr($nombresLimpios, 0, 1);
                                        $ciParcial = substr($ciLimpio, -4);
                                        $emailSugerido = strtolower(
                                            "{$inicialNombre}{$paternoLimpio}{$maternoLimpio}{$ciParcial}@siga.edu.bo",
                                        );

                                        $textoBusqueda = strtolower(
                                            "ci: {$persona->ci} | {$persona->ap_paterno} {$persona->ap_materno} {$persona->nombres}",
                                        );
                                    @endphp
                                    <div class="persona-item-catalogo py-1 px-2 mb-1" data-id="{{ $persona->id }}"
                                        data-ci="{{ $persona->ci }}" data-email="{{ $emailSugerido }}"
                                        data-texto="{{ $textoBusqueda }}">
                                        <span class="font-weight-bold text-info text-truncate mr-2"
                                            style="font-size: 0.7rem; line-height: 1.1;">
                                            <span class="text-dark font-weight-600">CI: {{ $persona->ci }}</span><br>
                                            {{ $persona->ap_paterno }} {{ $persona->ap_materno }} {{ $persona->nombres }}
                                        </span>
                                        <button type="button"
                                            class="btn btn-xs btn-outline-success btn-agregar-persona p-0 px-1"
                                            title="Seleccionar">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ================= COLUMNA 2: ZONA ACTIVA ================= -->
                <div class="columna-arrastre">
                    <div class="card card-success card-outline h-100 mb-0 shadow-sm d-flex flex-column">
                        <div class="card-header bg-white py-1 px-2 d-flex justify-content-between align-items-center">
                            <h6 class="card-title text-dark font-weight-bold mb-0" style="font-size: 0.85rem;">
                                <i class="fas fa-user-check mr-1 text-success"></i> 2. Personas a Habilitar
                                <span class="badge badge-success ml-1" id="contador-seleccionados">0</span>
                            </h6>
                            <button type="button" id="btn-limpiar-todo" class="btn btn-xs btn-outline-danger"
                                title="Quitar todos">
                                <i class="fas fa-trash-alt"></i> Limpiar
                            </button>
                        </div>
                        <div class="card-body d-flex flex-column p-2 flex-grow-1">
                            <p class="text-muted small mb-1" style="font-size: 0.70rem; line-height: 1.1;">
                                <i class="fas fa-info-circle"></i> Configura el correo de acceso individual para cada
                                persona seleccionada.
                            </p>
                            <div id="zona-grabacion" class="lista-destino-arrastre p-1 overflow-auto"
                                style="max-height: calc(100vh - 270px);">
                                <div id="mensaje-vacio" class="text-center text-muted py-5">
                                    <i class="fas fa-hand-pointer fa-2x mb-2 text-success"></i>
                                    <p class="small mb-0">Haz clic en el botón (+) del catálogo o utiliza la importación
                                        por lote para añadir personas aquí.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ================= COLUMNA 3: PARÁMETROS GLOBALES Y ROLES ================= -->
                <div class="columna-opciones" id="panel-opciones">
                    <div class="card card-info card-outline h-100 mb-0 shadow-sm d-flex flex-column">
                        <div class="card-header bg-white py-2">
                            <h6 class="card-title text-dark font-weight-bold mb-0">
                                <i class="fas fa-shield-alt mr-1 text-info"></i> 3. Credenciales y Roles Globales
                            </h6>
                        </div>
                        <div class="card-body d-flex flex-column p-3 flex-grow-1 overflow-auto">
                            <div class="flex-grow-1">
                                <!-- Checkbox para usar CI como contraseña temporal -->
                                <div class="custom-control custom-checkbox mb-3 bg-light p-2 rounded border">
                                    <input type="checkbox" class="custom-control-input" id="usar_ci_password"
                                        name="usar_ci_password" value="1" checked>
                                    <label class="custom-control-label small font-weight-bold text-primary"
                                        for="usar_ci_password">
                                        <i class="fas fa-key mr-1"></i> Usar número de carnet (CI) como contraseña temporal
                                        inicial
                                    </label>
                                    <small class="form-text text-muted mt-1" style="font-size: 0.68rem;">Recomendado: El
                                        usuario iniciará sesión con su CI y el sistema le pedirá cambiarlo luego.</small>
                                </div>

                                <div id="seccion-contrasenias" style="display: none;">
                                    <div class="form-group mb-2">
                                        <label for="password_global" class="small font-weight-bold mb-1">Contraseña
                                            Temporal Personalizada:</label>
                                        <input type="password" id="password_global" name="password"
                                            class="form-control form-control-sm" placeholder="Mínimo 8 caracteres">
                                    </div>

                                    <div class="form-group mb-2">
                                        <label for="password_confirmation" class="small font-weight-bold mb-1">Confirmar
                                            Contraseña:</label>
                                        <input type="password" id="password_confirmation" name="password_confirmation"
                                            class="form-control form-control-sm">
                                    </div>
                                </div>

                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold mb-1">Asignar Roles por Defecto (Spatie):</label>
                                    <div class="border rounded p-2"
                                        style="max-height: 180px; overflow-y: auto; background: #fdfdfd;">
                                        @foreach ($roles as $role)
                                            <div class="custom-control custom-checkbox mb-1">
                                                <input type="checkbox" class="custom-control-input"
                                                    id="role_{{ $role->id }}" name="roles[]"
                                                    value="{{ $role->name }}">
                                                <label class="custom-control-label small"
                                                    for="role_{{ $role->id }}">{{ $role->name }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div class="border-top pt-3 mt-auto">
                                <button type="submit"
                                    class="btn btn-sm btn-success btn-block font-weight-bold shadow-sm">
                                    <i class="fas fa-save mr-1"></i> Procesar Accesos al Sistema
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>

    <!-- ================= MODAL PARA PEGAR DESDE EXCEL ================= -->
    <div class="modal fade" id="modalPegarRUs" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info py-2">
                    <h6 class="modal-title text-white font-weight-bold">
                        <i class="fas fa-file-excel mr-1"></i> Pegar Lista desde Excel
                    </h6>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-1">Copia una columna completa de CIs desde Excel y pégala aquí:</p>
                    <textarea id="lista-excel-modal" class="form-control" rows="6" placeholder="Pega aquí los CIs..."></textarea>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <small class="text-muted font-italic">Registros detectados en el portapapeles:</small>
                        <span id="excel-contador-lineas" class="badge badge-secondary px-2 py-1"
                            style="font-size: 0.75rem;">0 líneas</span>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" id="btn-procesar-excel-modal" class="btn btn-sm btn-info font-weight-bold">
                        <i class="fas fa-check mr-1"></i> Procesar Lote
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            const STORAGE_KEY = 'sig_usuarios_seleccionados_objetos';

            // Función para obtener los objetos completos guardados
            function obtenerSeleccionadosStorage() {
                try {
                    return JSON.parse(localStorage.getItem(STORAGE_KEY)) || {};
                } catch (e) {
                    return {};
                }
            }

            // Función para guardar los objetos completos por su ID
            function guardarSeleccionadosStorage(mapaSeleccionados) {
                localStorage.setItem(STORAGE_KEY, JSON.stringify(mapaSeleccionados));
            }

            // Sincronizar y restaurar elementos seleccionados (incluso si cambiaron de filtro)
            function restaurarSeleccionadosDesdeStorage() {
                const seleccionadosMap = obtenerSeleccionadosStorage();

                Object.keys(seleccionadosMap).forEach(id => {
                    const data = seleccionadosMap[id];

                    // 1. Si el elemento está visible en el catálogo actual, lo ocultamos
                    const target = $(`#catalogo-origen .persona-item-catalogo[data-id="${id}"]`);
                    if (target.length > 0) {
                        target.hide();
                    }

                    // 2. Si todavía no está pintado en la zona de grabación, lo creamos con sus datos guardados
                    if ($(`#zona-grabacion input[value="${id}"]`).length === 0) {
                        const htmlItem = `
                            <div class="persona-item-seleccionada" data-id="${id}">
                                <input type="hidden" name="personas[${id}][persona_id]" value="${data.id}">
                                <input type="hidden" name="personas[${id}][ci]" value="${data.ci}">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="font-weight-bold text-success text-truncate" style="font-size: 0.70rem;">
                                        <i class="fas fa-user-check mr-1"></i> ${data.textoCompleto}
                                    </span>
                                    <button type="button" class="btn btn-xs text-danger btn-remover-persona p-0" title="Quitar">
                                        <i class="fas fa-times-circle"></i>
                                    </button>
                                </div>
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light text-muted px-1 py-0" style="font-size: 0.60rem;"><i class="fas fa-envelope"></i></span>
                                    </div>
                                    <input type="email" name="personas[${id}][email]" class="form-control form-control-sm py-0" value="${data.emailSugerido}" placeholder="Correo institucional" required style="font-size: 0.70rem; height: calc(1.3em + 0.3rem + 2px);">
                                </div>
                            </div>
                        `;
                        $('#zona-grabacion').append(htmlItem);
                    }
                });
                actualizarContador(false); // No sobrescribir storage al restaurar
            }

            function actualizarContador(sincronizarStorage = true) {
                const total = $('#zona-grabacion .persona-item-seleccionada').length;
                $('#contador-seleccionados').text(total);
                if (total > 0) {
                    $('#mensaje-vacio').hide();
                } else {
                    $('#mensaje-vacio').show();
                }

                if (sincronizarStorage) {
                    const mapaSeleccionados = {};
                    $('#zona-grabacion .persona-item-seleccionada').each(function() {
                        const id = $(this).attr('data-id');
                        // Intentamos buscar los datos en el DOM actual o reconstruirlos del input hidden
                        const target = $(`#catalogo-origen .persona-item-catalogo[data-id="${id}"]`);

                        let ci, textoCompleto, emailSugerido;
                        if (target.length > 0) {
                            ci = target.attr('data-ci');
                            textoCompleto = target.attr('data-texto');
                            emailSugerido = target.attr('data-email');
                        } else {
                            // Si cambió de filtro y no está en el catálogo visual, mantenemos lo que ya tenía guardado
                            const actualStorage = obtenerSeleccionadosStorage();
                            if (actualStorage[id]) {
                                ci = actualStorage[id].ci;
                                textoCompleto = actualStorage[id].textoCompleto;
                                emailSugerido = $(this).find('input[type="email"]').val() || actualStorage[
                                    id].emailSugerido;
                            }
                        }

                        mapaSeleccionados[id] = {
                            id: id,
                            ci: ci,
                            textoCompleto: textoCompleto,
                            emailSugerido: emailSugerido
                        };
                    });
                    guardarSeleccionadosStorage(mapaSeleccionados);
                }
            }

            // Restaurar selección previa al cargar la vista
            restaurarSeleccionadosDesdeStorage();

            // Alternar visibilidad de inputs de contraseña personalizada
            $('#usar_ci_password').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#seccion-contrasenias').slideUp(200);
                    $('#password_global, #password_confirmation').removeAttr('required').val('');
                } else {
                    $('#seccion-contrasenias').slideDown(200);
                    $('#password_global, #password_confirmation').attr('required', 'required');
                }
            });

            // 1. Filtrado rápido del Catálogo
            $('#filtrarCatalogo').on('keyup', function() {
                let val = $(this).val().toLowerCase().trim();
                $('#catalogo-origen .persona-item-catalogo').filter(function() {
                    let textoItem = $(this).attr('data-texto').toLowerCase();
                    $(this).toggle(textoItem.indexOf(val) > -1);
                });
            });

            // 2. Agregar persona individual
            $(document).on('click', '.btn-agregar-persona', function() {
                const itemPadre = $(this).closest('.persona-item-catalogo');
                const id = itemPadre.attr('data-id');
                const ci = itemPadre.attr('data-ci');
                const textoCompleto = itemPadre.attr('data-texto');
                const emailSugerido = itemPadre.attr('data-email');

                if ($(`#zona-grabacion input[value="${id}"]`).length > 0) {
                    toastr.warning('Esta persona ya se encuentra en la lista de habilitación.');
                    return;
                }

                const htmlItem = `
                    <div class="persona-item-seleccionada" data-id="${id}">
                        <input type="hidden" name="personas[${id}][persona_id]" value="${id}">
                        <input type="hidden" name="personas[${id}][ci]" value="${ci}">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="font-weight-bold text-success text-truncate" style="font-size: 0.70rem;">
                                <i class="fas fa-user-check mr-1"></i> ${textoCompleto}
                            </span>
                            <button type="button" class="btn btn-xs text-danger btn-remover-persona p-0" title="Quitar">
                                <i class="fas fa-times-circle"></i>
                            </button>
                        </div>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light text-muted px-1 py-0" style="font-size: 0.60rem;"><i class="fas fa-envelope"></i></span>
                            </div>
                            <input type="email" name="personas[${id}][email]" class="form-control form-control-sm py-0" value="${emailSugerido}" placeholder="Correo institucional" required style="font-size: 0.70rem; height: calc(1.3em + 0.3rem + 2px);">
                        </div>
                    </div>
                `;

                $('#zona-grabacion').append(htmlItem);
                itemPadre.fadeOut(150);
                actualizarContador(true);
                toastr.success('Persona añadida a la cola');
            });

            // 3. Procesar lista pegada de CIs (Importación Masiva barra lateral)
            $('#btn-procesar-importacion').on('click', function() {
                const rawInput = $('#lista-importar').val();
                const listaCIs = rawInput.split(/[\n,]+/).map(item => item.trim()).filter(item => item !==
                    "");

                let encontrados = 0;
                let htmlAcumulado = '';

                listaCIs.forEach(ci => {
                    const target = $(`#catalogo-origen .persona-item-catalogo[data-ci="${ci}"]`);
                    if (target.length > 0 && target.is(':visible')) {
                        const id = target.attr('data-id');
                        const textoCompleto = target.attr('data-texto');
                        const emailSugerido = target.attr('data-email');

                        if ($(`#zona-grabacion input[value="${id}"]`).length === 0) {
                            htmlAcumulado += `
                                <div class="persona-item-seleccionada" data-id="${id}">
                                    <input type="hidden" name="personas[${id}][persona_id]" value="${id}">
                                    <input type="hidden" name="personas[${id}][ci]" value="${ci}">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="font-weight-bold text-success text-truncate" style="font-size: 0.70rem;">
                                            <i class="fas fa-user-check mr-1"></i> ${textoCompleto}
                                        </span>
                                        <button type="button" class="btn btn-xs text-danger btn-remover-persona p-0" title="Quitar">
                                            <i class="fas fa-times-circle"></i>
                                        </button>
                                    </div>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-light text-muted px-1 py-0" style="font-size: 0.60rem;"><i class="fas fa-envelope"></i></span>
                                        </div>
                                        <input type="email" name="personas[${id}][email]" class="form-control form-control-sm py-0" value="${emailSugerido}" placeholder="Correo institucional" required style="font-size: 0.70rem; height: calc(1.3em + 0.3rem + 2px);">
                                    </div>
                                </div>
                            `;
                            target.hide();
                            encontrados++;
                        }
                    }
                });

                if (encontrados > 0) {
                    $('#zona-grabacion').append(htmlAcumulado);
                    actualizarContador(true);
                    toastr.success(`Se agregaron ${encontrados} personas de forma masiva.`);
                    $('#lista-importar').val('');
                } else {
                    toastr.error('No se encontraron personas coincidentes con los CIs provistos.');
                }
            });

            // 4. Remover persona
            $(document).on('click', '.btn-remover-persona', function() {
                const contenedorItem = $(this).closest('.persona-item-seleccionada');
                const id = contenedorItem.attr('data-id');

                contenedorItem.fadeOut(200, function() {
                    $(this).remove();
                    $(`#catalogo-origen .persona-item-catalogo[data-id="${id}"]`).fadeIn(150);
                    actualizarContador(true);
                });
            });

            // 5. Limpiar todo
            $('#btn-limpiar-todo').on('click', function() {
                $('#zona-grabacion .persona-item-seleccionada').each(function() {
                    const id = $(this).attr('data-id');
                    $(`#catalogo-origen .persona-item-catalogo[data-id="${id}"]`).fadeIn(150);
                    $(this).remove();
                });
                localStorage.removeItem(STORAGE_KEY);
                actualizarContador(false);
                toastr.info('Lista de habilitación restablecida');
            });

            // 6. Toggles
            $('#btn-toggle-catalogo').on('click', function() {
                $('#panel-catalogo').toggleClass('collapsed');
                const isCollapsed = $('#panel-catalogo').hasClass('collapsed');
                $('#toggle-cat-text').text(isCollapsed ? 'Mostrar Catálogo' : 'Ocultar Catálogo');
                $(this).find('i').toggleClass('fa-search fa-indent');
            });

            $('#btn-toggle-opciones').on('click', function() {
                $('#panel-opciones').toggleClass('collapsed');
                const isCollapsed = $('#panel-opciones').hasClass('collapsed');
                $('#toggle-opt-text').text(isCollapsed ? 'Mostrar Opciones' : 'Ocultar Opciones');
                $(this).find('i').toggleClass('fa-columns fa-indent');
            });

            // 7. Validación al enviar
            $('#form-usuarios').on('submit', function(e) {
                if ($('#zona-grabacion .persona-item-seleccionada').length === 0) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Sin personas seleccionadas',
                        text: 'Debes añadir al menos una persona desde el catálogo o mediante la importación en lote.',
                        icon: 'warning',
                        confirmButtonColor: '#003366'
                    });
                } else {
                    localStorage.removeItem(STORAGE_KEY);
                }
            });

            // 8. Contador de líneas modal Excel
            $('#lista-excel-modal').on('input', function() {
                const text = $(this).val();
                const lineas = text.split(/[\n\t,]+/).map(item => item.trim()).filter(item => item !== "");
                $('#excel-contador-lineas').text(lineas.length + (lineas.length === 1 ? ' línea' :
                    ' líneas'));
            });

            $('#modalPegarRUs').on('hidden.bs.modal', function() {
                $('#lista-excel-modal').val('');
                $('#excel-contador-lineas').text('0 líneas');
            });

            // 9. Lógica modal Excel
            $('#btn-procesar-excel-modal').on('click', function() {
                const rawInput = $('#lista-excel-modal').val();
                const items = rawInput.split(/[\n\t,]+/).map(item => item.trim()).filter(item => item !==
                    "");

                let encontrados = 0;
                let htmlAcumulado = '';

                items.forEach(val => {
                    const target = $(`#catalogo-origen .persona-item-catalogo`).filter(function() {
                        return $(this).attr('data-ci') === val;
                    });

                    if (target.length > 0 && target.is(':visible')) {
                        const id = target.attr('data-id');
                        const ci = target.attr('data-ci');
                        const textoCompleto = target.attr('data-texto');
                        const emailSugerido = target.attr('data-email');

                        if ($(`#zona-grabacion input[value="${id}"]`).length === 0) {
                            htmlAcumulado += `
                                <div class="persona-item-seleccionada" data-id="${id}">
                                    <input type="hidden" name="personas[${id}][persona_id]" value="${id}">
                                    <input type="hidden" name="personas[${id}][ci]" value="${ci}">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="font-weight-bold text-success text-truncate" style="font-size: 0.70rem;">
                                            <i class="fas fa-user-check mr-1"></i> ${textoCompleto}
                                        </span>
                                        <button type="button" class="btn btn-xs text-danger btn-remover-persona p-0" title="Quitar">
                                            <i class="fas fa-times-circle"></i>
                                        </button>
                                    </div>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-light text-muted px-1 py-0" style="font-size: 0.60rem;"><i class="fas fa-envelope"></i></span>
                                        </div>
                                        <input type="email" name="personas[${id}][email]" class="form-control form-control-sm py-0" value="${emailSugerido}" placeholder="Correo institucional" required style="font-size: 0.70rem; height: calc(1.3em + 0.3rem + 2px);">
                                    </div>
                                </div>
                            `;
                            target.hide();
                            encontrados++;
                        }
                    }
                });

                $('#modalPegarRUs').modal('hide');

                if (encontrados > 0) {
                    $('#zona-grabacion').append(htmlAcumulado);
                    actualizarContador(true);
                    toastr.success(`¡Procesado! ${encontrados} coincidencias añadidas en lote.`);
                } else {
                    toastr.warning('No se encontraron coincidencias con los datos ingresados.');
                }
            });
        });
    </script>
@stop
