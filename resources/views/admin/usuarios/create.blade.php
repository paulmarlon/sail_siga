@extends('adminlte::page')

@section('title', 'Habilitar Usuarios')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1><i class="fas fa-user-plus text-primary mr-2"></i> Habilitación Masiva de Usuarios</h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('admin.usuarios.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left mr-1"></i> Volver al listado
                </a>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid">
        <form action="{{ route('admin.usuarios.store') }}" method="POST" id="form-usuarios">
            @csrf

            <div class="row">
                <!-- COLUMNA IZQUIERDA: CATÁLOGO DE PERSONAS -->
                <div class="col-lg-5" id="panel-catalogo">
                    <div class="card card-primary card-outline shadow-sm h-100">
                        <div class="card-header py-2">
                            <h3 class="card-title font-weight-bold" style="font-size: 0.9rem;">
                                <i class="fas fa-address-book mr-1"></i> Catálogo de Personas
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                                        class="fas fa-minus"></i></button>
                            </div>
                        </div>
                        <div class="card-body d-flex flex-column" style="max-height: 75vh; overflow-y: auto;">

                            <!-- Botón para abrir Modal Masivo Excel -->
                            <div class="mb-2">
                                <button type="button" class="btn btn-outline-success btn-block btn-xs" data-toggle="modal"
                                    data-target="#modalPegarRUs">
                                    <i class="fas fa-file-excel mr-1"></i> Importación Masiva (Pegar CIs)
                                </button>
                            </div>

                            <!-- Buscador rápido (Inicia completamente vacío) -->
                            <div class="input-group input-group-sm mb-2">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                </div>
                                <input type="text" id="filtrarCatalogo" class="form-control" value=""
                                    placeholder="Filtrar por nombre o CI...">
                            </div>

                            <!-- Lista de personas origen -->
                            <div id="catalogo-origen" class="flex-grow-1" style="overflow-y: auto;">
                                @forelse($personasSinUsuario as $p)
                                    @php
                                        $nombresPersona = $p->nombres ?? '';
                                        $apPaterno = $p->ap_paterno ?? '';
                                        $apMaterno = $p->ap_materno ?? '';
                                        $ciPersona = $p->ci ?? '';
                                        $nombreCompleto = trim("{$nombresPersona} {$apPaterno} {$apMaterno}");

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

                                        $emailGenerado = strtolower(
                                            "{$inicialNombre}{$paternoLimpio}{$maternoLimpio}{$ciParcial}@siga.edu.bo",
                                        );

                                        $emailSugerido = !empty($p->email_sugerido)
                                            ? $p->email_sugerido
                                            : $emailGenerado;

                                        $textoBusqueda = strtolower("{$nombreCompleto} (ci: {$p->ci})");
                                    @endphp
                                    <div class="persona-item-catalogo border-bottom py-2 px-1 d-flex justify-content-between align-items-center"
                                        data-id="{{ $p->id }}" data-ci="{{ $p->ci }}"
                                        data-nombre="{{ $nombreCompleto }}" data-texto="{{ $textoBusqueda }}"
                                        data-email="{{ $emailSugerido }}">
                                        <div class="text-truncate mr-2" style="font-size: 0.75rem;">
                                            <span class="font-weight-bold text-dark">{{ $nombreCompleto }}</span><br>
                                            <span class="text-muted">CI: {{ $p->ci }}</span>
                                        </div>
                                        <button type="button"
                                            class="btn btn-xs btn-primary btn-agregar-persona text-nowrap px-2"
                                            style="font-size: 0.7rem;">
                                            <i class="fas fa-plus"></i> Añadir
                                        </button>
                                    </div>
                                @empty
                                    <p class="text-muted text-center my-3" style="font-size: 0.8rem;">No hay personas
                                        disponibles para habilitar.</p>
                                @endforelse
                            </div>

                        </div>
                    </div>
                </div>

                <!-- COLUMNA CENTRAL: ZONA DE HABILITACIÓN SELECCIONADOS -->
                <div class="col-lg-4" id="panel-seleccionados">
                    <div class="card card-success card-outline shadow-sm h-100">
                        <div class="card-header py-2 d-flex justify-content-between align-items-center">
                            <h3 class="card-title font-weight-bold text-success" style="font-size: 0.9rem;">
                                <i class="fas fa-users mr-1"></i> Habilitados (<span id="contador-seleccionados">0</span>)
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool text-danger" id="btn-limpiar-todo"
                                    title="Limpiar todo">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body d-flex flex-column p-2" style="max-height: 75vh; overflow-y: auto;"
                            id="zona-grabacion">
                            <div id="placeholder-vacio" class="text-center text-muted my-auto py-5"
                                style="font-size: 0.8rem;">
                                <i class="fas fa-hand-pointer fa-2x mb-2 text-muted"></i>
                                <p>Selecciona personas del catálogo de la izquierda o usa la importación masiva.</p>
                            </div>
                            <!-- Aquí se inyectan dinámicamente los seleccionados -->
                        </div>
                    </div>
                </div>

                <!-- COLUMNA DERECHA: CONFIGURACIÓN Y CREDENCIALES -->
                <div class="col-lg-3" id="panel-opciones">
                    <div class="card card-info card-outline shadow-sm h-100">
                        <div class="card-header py-2">
                            <h3 class="card-title font-weight-bold" style="font-size: 0.9rem;">
                                <i class="fas fa-cogs mr-1"></i> Configuración
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                                        class="fas fa-minus"></i></button>
                            </div>
                        </div>
                        <div class="card-body" style="font-size: 0.8rem;">
                            <!-- Rol Global -->
                            <div class="form-group mb-2">
                                <label for="roles" class="font-weight-bold mb-1">Rol a Asignar:</label>
                                <select name="roles[]" id="roles" class="form-control form-control-sm" required
                                    multiple>
                                    @foreach ($roles as $rol)
                                        <option value="{{ $rol->name }}">{{ $rol->name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted" style="font-size: 0.65rem;">Mantén presionado Ctrl para
                                    seleccionar varios roles.</small>
                            </div>

                            <!-- Checkbox CI como contraseña -->
                            <div class="form-group mb-2">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="usar_ci_password"
                                        name="usar_ci_password" value="1" checked>
                                    <label class="custom-control-label" for="usar_ci_password">Usar CI como contraseña
                                        inicial</label>
                                </div>
                            </div>

                            <!-- Sección de contraseñas personalizadas (Oculta por defecto si usa CI) -->
                            <div id="seccion-contrasenias" style="display: none;">
                                <div class="form-group mb-2">
                                    <label for="password_global" class="font-weight-bold mb-1">Contraseña Global:</label>
                                    <input type="password" name="password" id="password_global"
                                        class="form-control form-control-sm" placeholder="Mínimo 8 caracteres">
                                </div>
                                <div class="form-group mb-2">
                                    <label for="password_confirmation" class="font-weight-bold mb-1">Confirmar
                                        Contraseña:</label>
                                    <input type="password" name="password_confirmation" id="password_confirmation"
                                        class="form-control form-control-sm" placeholder="Repetir contraseña">
                                </div>
                            </div>

                            <hr class="my-2">

                            <!-- Botones de control de visibilidad rápidos -->
                            <div class="mb-3">
                                <button type="button" class="btn btn-default btn-xs btn-block text-left"
                                    id="btn-toggle-catalogo">
                                    <i class="fas fa-search mr-1"></i> <span id="toggle-cat-text">Ocultar Catálogo</span>
                                </button>
                                <button type="button" class="btn btn-default btn-xs btn-block text-left"
                                    id="btn-toggle-opciones">
                                    <i class="fas fa-columns mr-1"></i> <span id="toggle-opt-text">Ocultar Opciones</span>
                                </button>
                            </div>

                            <!-- Botón Enviar Final -->
                            <button type="submit" class="btn btn-success btn-block font-weight-bold shadow-sm">
                                <i class="fas fa-save mr-1"></i> Guardar y Habilitar
                            </button>

                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- MODAL PARA IMPORTACIÓN MASIVA (PEGAR DESDE EXCEL) -->
    <div class="modal fade" id="modalPegarRUs" tabindex="-1" role="dialog" aria-labelledby="modalPegarRUsLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success py-2">
                    <h5 class="modal-title font-weight-bold" id="modalPegarRUsLabel" style="font-size: 0.95rem;">
                        <i class="fas fa-file-excel mr-1"></i> Importar CIs Masivamente
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="text-muted" style="font-size: 0.8rem;">
                        Copia una columna de números de carnet de identidad (CI) desde Excel o un bloc de notas y pégala
                        aquí abajo. El sistema buscará coincidencias automáticamente.
                    </p>
                    <div class="form-group mb-1">
                        <textarea id="lista-excel-modal" class="form-control" rows="8" placeholder="1234567&#10;7654321&#10;9876543"
                            style="font-size: 0.8rem;"></textarea>
                    </div>
                    <div class="text-right text-muted" style="font-size: 0.75rem;">
                        <span id="excel-contador-lineas">0 líneas</span> detectadas
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                    <button type="button" id="btn-procesar-excel-modal" class="btn btn-success btn-sm font-weight-bold">
                        <i class="fas fa-check mr-1"></i> Procesar e Insertar
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            const STORAGE_KEY = 'habilitacion_usuarios_seleccionados_v1';

            // Limpiar explícitamente el input de filtro al cargar la página por seguridad del navegador
            $('#filtrarCatalogo').val('');

            // Función para actualizar contador y visibilidad del placeholder vacío
            function actualizarContador() {
                const total = $('#zona-grabacion .persona-item-seleccionada').length;
                $('#contador-seleccionados').text(total);

                if (total === 0) {
                    if ($('#placeholder-vacio').length === 0) {
                        $('#zona-grabacion').html(`
                            <div id="placeholder-vacio" class="text-center text-muted my-auto py-5" style="font-size: 0.8rem;">
                                <i class="fas fa-hand-pointer fa-2x mb-2 text-muted"></i>
                                <p>Selecciona personas del catálogo de la izquierda o usa la importación masiva.</p>
                            </div>
                        `);
                    }
                } else {
                    $('#placeholder-vacio').remove();
                }

                sincronizarStorage();
            }

            // Sincronizar la visibilidad del catálogo en base al filtro y los ya seleccionados
            function refrescarCatalogoVisible() {
                let valorBusqueda = $('#filtrarCatalogo').val().toLowerCase().trim();

                $('#catalogo-origen .persona-item-catalogo').each(function() {
                    let idItem = $(this).attr('data-id');
                    let textoItem = $(this).attr('data-texto') || '';
                    let estaSeleccionado = $(`#zona-grabacion input[value="${idItem}"]`).length > 0;

                    if (estaSeleccionado) {
                        $(this).removeClass('d-flex').addClass('d-none');
                        return;
                    }

                    if (valorBusqueda === "") {
                        $(this).removeClass('d-none').addClass('d-flex');
                    } else {
                        if (textoItem.indexOf(valorBusqueda) > -1) {
                            $(this).removeClass('d-none').addClass('d-flex');
                        } else {
                            $(this).removeClass('d-flex').addClass('d-none');
                        }
                    }
                });
            }

            // Función auxiliar para generar el HTML de una persona seleccionada con su nombre y CI
            function crearItemSeleccionadoHtml(id, ci, nombreCompleto, emailSugerido) {
                return `
            <div class="persona-item-seleccionada border-bottom py-1 px-2 mb-1" data-id="${id}">
                <input type="hidden" name="personas[${id}][persona_id]" value="${id}">
                <input type="hidden" name="personas[${id}][ci]" value="${ci}">
                <input type="hidden" name="personas[${id}][nombre]" value="${nombreCompleto}">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="font-weight-bold text-success text-truncate" style="font-size: 0.70rem;" title="${nombreCompleto} - CI: ${ci}">
                        <i class="fas fa-user-check mr-1"></i> ${nombreCompleto} <span class="text-muted font-weight-normal">(CI: ${ci})</span>
                    </span>
                    <button type="button" class="btn btn-xs text-danger btn-remover-persona p-0" title="Quitar">
                        <i class="fas fa-times-circle"></i>
                    </button>
                </div>
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-light text-muted px-1 py-0" style="font-size: 0.60rem;"><i class="fas fa-envelope"></i></span>
                    </div>
                    <input type="email" name="personas[${id}][email]" class="form-control form-control-sm py-0" value="${emailSugerido}" placeholder="Correo institucional" autocomplete="off" style="font-size: 0.70rem; height: calc(1.3em + 0.3rem + 2px);">
                </div>
            </div>
        `;
            }

            // Restaurar selección previa desde localStorage al recargar la vista
            function restaurarSeleccionadosDesdeStorage() {
                const guardado = localStorage.getItem(STORAGE_KEY);
                if (!guardado) return;

                try {
                    const itemsGuardados = JSON.parse(guardado);
                    let htmlAcumulado = '';
                    let restauradosCount = 0;

                    itemsGuardados.forEach(item => {
                        const target = $(`#catalogo-origen .persona-item-catalogo[data-id="${item.id}"]`);
                        if (target.length > 0) {
                            const ci = target.attr('data-ci');
                            const nombreCompleto = target.attr('data-nombre');
                            const emailSugerido = item.emailSugerido || target.attr('data-email') || '';

                            htmlAcumulado += crearItemSeleccionadoHtml(item.id, ci, nombreCompleto,
                                emailSugerido);
                            restauradosCount++;
                        }
                    });

                    if (restauradosCount > 0) {
                        $('#zona-grabacion').append(htmlAcumulado);
                        actualizarContador();
                    }
                } catch (e) {
                    console.error("Error al restaurar localStorage:", e);
                    localStorage.removeItem(STORAGE_KEY);
                }
            }

            function sincronizarStorage() {
                let seleccionados = [];
                $('#zona-grabacion .persona-item-seleccionada').each(function() {
                    const id = $(this).attr('data-id');
                    const ci = $(this).find('input[name*="[ci]"]').val();
                    const nombre = $(this).find('input[name*="[nombre]"]').val();
                    const emailSugerido = $(this).find('input[type="email"]').val();

                    seleccionados.push({
                        id: id,
                        ci: ci,
                        nombre: nombre,
                        emailSugerido: emailSugerido
                    });
                });
                localStorage.setItem(STORAGE_KEY, JSON.stringify(seleccionados));
            }

            // Ejecución inicial en orden correcto
            restaurarSeleccionadosDesdeStorage();
            refrescarCatalogoVisible();

            // Guardar cambios en el input de correo en tiempo real
            $(document).on('input', '#zona-grabacion input[type="email"]', function() {
                sincronizarStorage();
            });

            // Control de contraseñas
            $('#usar_ci_password').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#seccion-contrasenias').slideUp(200);
                    $('#password_global, #password_confirmation').removeAttr('required').val('');
                } else {
                    $('#seccion-contrasenias').slideDown(200);
                    $('#password_global, #password_confirmation').attr('required', 'required');
                }
            });

            // Filtrado del catálogo en tiempo real
            $(document).on('input', '#filtrarCatalogo', function() {
                refrescarCatalogoVisible();
            });

            // Botón Añadir individual
            $(document).on('click', '.btn-agregar-persona', function() {
                const itemPadre = $(this).closest('.persona-item-catalogo');
                const id = itemPadre.attr('data-id');
                const ci = itemPadre.attr('data-ci');
                const nombreCompleto = itemPadre.attr('data-nombre');
                const emailSugerido = itemPadre.attr('data-email') || '';

                if ($(`#zona-grabacion input[value="${id}"]`).length > 0) {
                    toastr.warning('Esta persona ya se encuentra en la lista de habilitación.');
                    return;
                }

                const htmlItem = crearItemSeleccionadoHtml(id, ci, nombreCompleto, emailSugerido);

                $('#zona-grabacion').append(htmlItem);
                refrescarCatalogoVisible();
                actualizarContador();
                toastr.success('Persona añadida a la cola');
            });

            // Botón Remover persona seleccionada
            $(document).on('click', '.btn-remover-persona', function() {
                $(this).closest('.persona-item-seleccionada').remove();
                refrescarCatalogoVisible();
                actualizarContador();
            });

            // Limpiar todo
            $('#btn-limpiar-todo').on('click', function() {
                $('#zona-grabacion .persona-item-seleccionada').remove();
                localStorage.removeItem(STORAGE_KEY);
                refrescarCatalogoVisible();
                actualizarContador();
                toastr.info('Lista restablecida');
            });

            // Botones de colapso rápido paneles laterales
            $('#btn-toggle-catalogo').on('click', function() {
                $('#panel-catalogo').toggleClass('d-none');
                const isHidden = $('#panel-catalogo').hasClass('d-none');
                $('#toggle-cat-text').text(isHidden ? 'Mostrar Catálogo' : 'Ocultar Catálogo');
            });

            $('#btn-toggle-opciones').on('click', function() {
                $('#panel-opciones').toggleClass('d-none');
                const isHidden = $('#panel-opciones').hasClass('d-none');
                $('#toggle-opt-text').text(isHidden ? 'Mostrar Opciones' : 'Ocultar Opciones');
            });

            // Contador de líneas del modal de importación
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

            // Procesar Importación Masiva por Modal Excel
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

                    if (target.length > 0) {
                        const id = target.attr('data-id');
                        const ci = target.attr('data-ci');
                        const nombreCompleto = target.attr('data-nombre');
                        const emailSugerido = target.attr('data-email') || '';

                        if ($(`#zona-grabacion input[value="${id}"]`).length === 0) {
                            htmlAcumulado += crearItemSeleccionadoHtml(id, ci, nombreCompleto,
                                emailSugerido);
                            encontrados++;
                        }
                    }
                });

                $('#modalPegarRUs').modal('hide');

                if (encontrados > 0) {
                    $('#zona-grabacion').append(htmlAcumulado);
                    refrescarCatalogoVisible();
                    actualizarContador();
                    toastr.success(`¡Procesado! ${encontrados} coincidencias añadidas en lote.`);
                } else {
                    toastr.warning('No se encontraron coincidencias con los CIs ingresados.');
                }
            });

            // Limpiar almacenamiento al enviar formulario y validación previa
            // Limpiar almacenamiento SOLO cuando el formulario sea enviado con éxito y pase las validaciones del cliente
            $('#form-usuarios').on('submit', function(e) {
                if ($('#zona-grabacion .persona-item-seleccionada').length === 0) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Sin personas seleccionadas',
                        text: 'Debes añadir al menos una persona desde el catálogo o mediante la importación en lote.',
                        icon: 'warning',
                        confirmButtonColor: '#003366'
                    });
                    return false;
                }

                // El localStorage se limpiará al recargar o redireccionar si el servidor responde OK,
                // pero si hay un error de validación de Laravel, se mantendrán los datos guardados.
            });
        });
    </script>
@endsection
