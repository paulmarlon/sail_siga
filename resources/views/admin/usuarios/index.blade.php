@extends('adminlte::page')

@section('title', 'Gestión de Accesos y Usuarios')

@section('css')
    <style>
        .card-scroll {
            max-height: 75vh;
            display: flex;
            flex-direction: column;
        }

        .card-body-scroll {
            flex: 1;
            overflow-y: auto;
        }

        /* Fija el encabezado de la tabla para que no desaparezca al hacer scroll */
        #tabla-usuarios thead th {
            position: sticky;
            top: 0;
            z-index: 10;
            background-color: #f8f9fa !important;
            box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.1);
        }

        #tabla-usuarios th,
        #tabla-usuarios td {
            padding: 0.4rem 0.5rem !important;
            vertical-align: middle !important;
            font-size: 0.78rem;
        }

        /* Estilo para los roles (3 caracteres, horizontal, colores suaves) */
        .th-rol {
            width: 45px;
            text-align: center;
            font-size: 0.7rem !important;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .bg-soft-primary {
            background-color: #e7f1ff;
            color: #0d6efd;
        }

        .bg-soft-success {
            background-color: #e8f5e9;
            color: #28a745;
        }

        .bg-soft-warning {
            background-color: #fff3e0;
            color: #fd7e14;
        }

        .bg-soft-info {
            background-color: #e0f7fa;
            color: #17a2b8;
        }
    </style>
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fas fa-users-cog mr-2 text-primary"></i> Gestión de Accesos al Sistema</h1>
            <p class="text-muted mb-0">Control centralizado de usuarios, credenciales activas, roles y permisos.</p>
        </div>
        <div class="d-flex align-items-center">
            <!-- Botón flotante superior para mostrar filtros si estaban ocultos -->
            <button type="button" class="btn btn-outline-primary btn-sm mr-2 d-none shadow-sm" id="btn-mostrar-filtros"
                onclick="toggleFiltros()">
                <i class="fas fa-filter mr-1"></i> <span id="toggle-filtros-text">Mostrar Filtros</span>
            </button>
            <a href="{{ route('admin.usuarios.create') }}" class="btn btn-primary btn-sm shadow-sm font-weight-bold">
                <i class="fas fa-user-plus mr-1"></i> Habilitar Nuevo Acceso
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

    <!-- FORMULARIO ENVOLVENTE GENERAL PARA ACCIONES MASIVAS -->
    <form action="" method="POST" id="form-masivo-usuarios">
        @csrf

        <div class="row">
            <!-- ================= COLUMNA 1: FILTROS, BÚSQUEDA Y EXCEL (IZQUIERDA) ================= -->
            <div class="col-md-3 px-1" id="panel-filtros-izquierdo">
                <div class="card card-primary card-outline shadow-sm mb-3">
                    <div class="card-header bg-white py-2 px-2 d-flex justify-content-between align-items-center">
                        <h6 class="card-title text-dark font-weight-bold mb-0" style="font-size: 0.85rem;">
                            <i class="fas fa-filter mr-1 text-primary"></i> Panel de Filtros
                        </h6>
                        <!-- Botón interactivo mejorado con texto e ícono dinámico -->
                        <button type="button" class="btn btn-xs btn-outline-secondary font-weight-bold px-2 py-1"
                            onclick="toggleFiltros()" style="font-size: 0.7rem;">
                            <i class="fas fa-indent mr-1"></i> <span id="toggle-btn-text">Ocultar</span>
                        </button>
                    </div>
                    <div class="card-body p-2 bg-light">
                        <!-- Buscador por Texto Libre -->
                        <div class="form-group mb-2">
                            <label class="small font-weight-bold text-secondary mb-1">
                                <i class="fas fa-keyboard mr-1"></i> CI / Nombre / Correo:
                            </label>
                            <input type="text" id="filtro-busqueda" class="form-control form-control-sm"
                                placeholder="Escribe para buscar..." style="font-size: 0.75rem;">
                        </div>

                        <!-- Select: Filtrar por Rol -->
                        <div class="form-group mb-2">
                            <label class="small font-weight-bold text-secondary mb-1">Filtrar por Rol:</label>
                            <select id="filtro-rol" class="form-control form-control-sm" style="font-size: 0.75rem;">
                                <option value="">-- Todos los Roles --</option>
                                @foreach ($roles ?? [] as $rol)
                                    <option value="{{ strtolower($rol->name) }}">
                                        {{ ucfirst($rol->name) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Filtros Académicos Opcionales (Gestión, Periodo, Turno, Paralelo) -->
                        <div class="form-group mb-2">
                            <label class="small font-weight-bold text-secondary mb-1">Gestión:</label>
                            <select id="filtro-gestion" class="form-control form-control-sm" style="font-size: 0.75rem;"
                                onchange="this.form.submit()">
                                <option value="">-- Todas --</option>
                                @foreach ($gestions ?? [] as $g)
                                    <option value="{{ $g->id }}"
                                        {{ request('gestion_id') == $g->id ? 'selected' : '' }}>
                                        {{ $g->nombre ?? $g->gestion }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Botón para Modal de Excel -->
                        <div class="form-group mb-2">
                            <button type="button" class="btn btn-outline-info btn-block btn-sm shadow-sm"
                                data-toggle="modal" data-target="#modalPegarRUs" style="font-size: 0.75rem;">
                                <i class="fas fa-file-excel mr-1"></i> Pegar CIs (Excel)
                            </button>
                        </div>

                        <!-- Botón Limpiar Filtros -->
                        <div class="pt-2 border-top">
                            <a href="{{ route('admin.usuarios.index') }}" id="btn-limpiar-filtros"
                                class="btn btn-xs btn-outline-secondary btn-block" style="font-size: 0.75rem;">
                                <i class="fas fa-sync-alt mr-1"></i> Limpiar Filtros
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ================= COLUMNA 2: TABLA DE RESULTADOS Y ACCIONES AL PIE (DERECHA) ================= -->
            <div class="col-md-9 px-1" id="columna-tabla-derecha">
                <div class="card card-success card-outline card-scroll shadow-sm mb-0">
                    <div class="card-header bg-white py-2 px-3 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <div class="custom-control custom-checkbox mr-3">
                                <input type="checkbox" class="custom-control-input" id="seleccionar-todos-visibles">
                                <label class="custom-control-label small font-weight-bold text-dark cursor-pointer"
                                    for="seleccionar-todos-visibles">Seleccionar Visibles</label>
                            </div>
                            <span class="badge badge-secondary px-2 py-1" style="font-size: 0.75rem;">
                                Coincidentes: <span id="contador-visibles">{{ count($usuarios) }}</span>
                            </span>
                        </div>
                    </div>

                    <div class="card-body p-2 card-body-scroll">
                        <div id="tabla-vacia-mensaje" class="text-center text-muted py-5 d-none">
                            <i class="fas fa-search fa-3x mb-2 text-secondary"></i>
                            <p class="font-weight-bold mb-1">No se encontraron usuarios con los filtros aplicados.</p>
                            <small>Intenta limpiar los filtros o realizar otra búsqueda.</small>
                        </div>

                        <!-- TABLA GENERAL -->
                        <table id="tabla-usuarios"
                            class="table table-bordered table-striped table-hover text-nowrap w-100 mb-0">
                            <thead>
                                <tr>
                                    <th class="text-center align-middle" style="width: 35px;"><i
                                            class="fas fa-check-square"></i></th>

                                    <!-- Identidad (Apellidos y Nombres) con ícono para ocultar columna completa -->
                                    <th class="align-middle col-identidad" style="min-width: 250px;">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span><i class="fas fa-id-card mr-1"></i> Apellidos y Nombres / Correo</span>
                                            <i class="fas fa-eye-slash text-muted cursor-pointer ml-2"
                                                onclick="$('.col-identidad').toggle()"
                                                title="Ocultar/Mostrar Identidad"></i>
                                        </div>
                                    </th>

                                    <!-- Estado Académico / Institucional -->
                                    <th class="text-center align-middle" style="width: 40px;" title="Es Estudiante">Est.
                                    </th>

                                    <!-- Personal (Docente/Planta) -->
                                    <th class="text-center align-middle" style="width: 40px;" title="Es Personal">Pers.
                                    </th>

                                    <!-- Roles con diseño horizontal y 3 caracteres -->
                                    @foreach ($roles as $index => $rol)
                                        @php
                                            $colors = [
                                                'bg-soft-primary',
                                                'bg-soft-success',
                                                'bg-soft-warning',
                                                'bg-soft-info',
                                            ];
                                            $colorClass = $colors[$index % count($colors)];
                                        @endphp
                                        <th class="text-center align-middle th-rol {{ $colorClass }}"
                                            title="{{ ucfirst($rol->name) }}">
                                            {{ substr(strtoupper($rol->name), 0, 3) }}
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($usuarios as $usuario)
                                    @php
                                        $esEstudianteDB =
                                            $usuario->persona && $usuario->persona->estudiante()->exists();
                                        $esPersonalDB = $usuario->persona && $usuario->persona->personal()->exists();

                                        $nombresPersona = strtolower($usuario->persona->nombres ?? '');
                                        $apPaterno = strtolower($usuario->persona->ap_paterno ?? '');
                                        $apMaterno = strtolower($usuario->persona->ap_materno ?? '');
                                        $ciPersona = strtolower($usuario->persona->ci ?? '');
                                        $emailUser = strtolower($usuario->email ?? '');

                                        $rolesNombres = $usuario->roles
                                            ->pluck('name')
                                            ->map(fn($r) => strtolower(trim($r)))
                                            ->implode(',');

                                        $textoBusqueda = "{$apPaterno} {$apMaterno} {$nombresPersona} {$ciPersona} {$emailUser}";
                                    @endphp

                                    <tr class="usuario-row" data-texto="{{ $textoBusqueda }}"
                                        data-roles="{{ $rolesNombres }}" data-id="{{ $usuario->id }}"
                                        style="font-size: 0.75rem;">
                                        <td class="text-center align-middle">
                                            <div class="custom-control custom-checkbox m-0">
                                                @if ($usuario->id === 1)
                                                    <!-- El usuario root NO es seleccionable -->
                                                    <input type="checkbox" class="custom-control-input" disabled>
                                                    <label class="custom-control-label text-muted"
                                                        title="Usuario Administrador Protegido"><i
                                                            class="fas fa-shield-alt text-danger"></i></label>
                                                @else
                                                    <input type="checkbox" name="usuarios_ids[]"
                                                        value="{{ $usuario->id }}" id="usuario_chk_{{ $usuario->id }}"
                                                        class="custom-control-input usuario-checkbox">
                                                    <label class="custom-control-label"
                                                        for="usuario_chk_{{ $usuario->id }}"></label>
                                                @endif
                                            </div>
                                        </td>

                                        <td class="align-middle py-1 col-identidad">
                                            <!-- Apellidos primero, Nombres después (Estándar Institucional) -->
                                            <div class="font-weight-bold text-dark">
                                                {{ $usuario->persona->ap_paterno ?? '' }}
                                                {{ $usuario->persona->ap_materno ?? '' }},
                                                {{ $usuario->persona->nombres ?? 'Sin Nombre' }}
                                            </div>
                                            <div style="font-size: 0.68rem;">
                                                <span class="badge badge-secondary mr-1">CI:
                                                    {{ $usuario->persona->ci ?? 'S/N' }}</span>
                                                <span class="text-muted"><i class="fas fa-envelope mr-1"></i>
                                                    {{ $usuario->email }}</span>
                                            </div>
                                        </td>

                                        <td class="text-center align-middle">
                                            {!! $esEstudianteDB ? '<i class="fas fa-check text-info"></i>' : '<span class="text-muted">-</span>' !!}
                                        </td>

                                        <td class="text-center align-middle">
                                            {!! $esPersonalDB ? '<i class="fas fa-check text-primary"></i>' : '<span class="text-muted">-</span>' !!}
                                        </td>

                                        @foreach ($roles as $rol)
                                            <td class="text-center align-middle">
                                                @if (in_array($rol->name, $usuario->roles->pluck('name')->toArray()))
                                                    <i class="fas fa-check text-success" style="font-size: 0.7rem;"></i>
                                                @else
                                                    <span class="text-muted" style="opacity: 0.3;">·</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- ================= BOTONES DE ACCIÓN MASIVA AL PIE DE LA TABLA ================= -->
                    <div
                        class="card-footer bg-white py-2 px-3 d-flex justify-content-between align-items-center border-top">
                        <span class="text-muted small font-italic">Seleccione elementos de la tabla para habilitar las
                            acciones.</span>
                        <div class="btn-group">
                            <button type="submit" formaction="{{ route('admin.usuarios.prepararEdicionMasiva') }}"
                                formmethod="POST" id="btn-editar-lote-avanzado"
                                class="btn btn-info btn-sm font-weight-bold shadow-sm mr-2 action-btn-lote" disabled
                                style="font-size: 0.78rem;">
                                <i class="fas fa-user-edit mr-1"></i> Editar Seleccionados (<span
                                    id="contador-lote-roles">0</span>)
                            </button>
                            <button type="submit" id="btn-revocar-bloque"
                                class="btn btn-danger btn-sm font-weight-bold shadow-sm action-btn-lote" disabled
                                style="font-size: 0.78rem;" data-action="{{ route('admin.usuarios.destroy-masivo') }}"
                                data-method="POST">
                                <i class="fas fa-trash-alt mr-1"></i> Revocar Accesos (<span id="contador-lote">0</span>)
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </form>

    <!-- ================= MODAL PARA PEGAR DESDE EXCEL ================= -->
    <div class="modal fade" id="modalPegarRUs" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info py-2">
                    <h6 class="modal-title text-white font-weight-bold">
                        <i class="fas fa-file-excel mr-1"></i> Marcar Lote Mediante CIs (Excel)
                    </h6>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-1">Copia la columna de CIs desde tu hoja de Excel y pégala aquí abajo:
                    </p>
                    <textarea id="lista-excel-modal" class="form-control" rows="6"
                        placeholder="Pega aquí los números de carnet..."></textarea>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <small class="text-muted font-italic">Registros detectados en el portapapeles:</small>
                        <span id="excel-contador-lineas" class="badge badge-secondary px-2 py-1"
                            style="font-size: 0.75rem;">0 líneas</span>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" id="btn-procesar-excel-modal" class="btn btn-sm btn-info font-weight-bold">
                        <i class="fas fa-check mr-1"></i> Marcar Coincidentes
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        // Función limpia y dinámica inspirada en tu estilo para mostrar/ocultar el panel de filtros
        function toggleFiltros() {
            var panel = $('#panel-filtros-izquierdo');
            var columna = $('#columna-tabla-derecha');
            var btnMostrar = $('#btn-mostrar-filtros');

            panel.toggle();
            var isHidden = panel.is(':hidden');

            if (isHidden) {
                columna.removeClass('col-md-9').addClass('col-md-12');
                btnMostrar.removeClass('d-none');
            } else {
                columna.removeClass('col-md-12').addClass('col-md-9');
                btnMostrar.addClass('d-none');
            }
        }

        $(function() {
            // Configurar acción del formulario al hacer clic en botones por lotes
            $('#btn-revocar-bloque').on('click', function(e) {
                $('#form-masivo-usuarios').attr('action', $(this).data('action'));
                $('#form-masivo-usuarios').attr('method', $(this).data('method'));
            });

            // ==========================================
            // FILTRADO EN TIEMPO REAL (TEXTO + ROL)
            // ==========================================
            $('#filtro-busqueda, #filtro-rol').on('keyup change', function() {
                filtrarTabla();
            });

            function filtrarTabla() {
                var textoBusqueda = $('#filtro-busqueda').val().toLowerCase().trim();
                var rolSeleccionado = $('#filtro-rol').val().toLowerCase().trim();
                var visiblesCount = 0;

                $('.usuario-row').each(function() {
                    var row = $(this);
                    var textoFila = row.data('texto') ? row.data('texto').toString().toLowerCase() : '';
                    var rolesFila = row.data('roles') ? row.data('roles').toString().toLowerCase() : '';

                    var cumpleTexto = textoBusqueda === '' || textoFila.indexOf(textoBusqueda) !== -1;
                    var cumpleRol = rolSeleccionado === '' || rolesFila.indexOf(rolSeleccionado) !== -1;

                    if (cumpleTexto && cumpleRol) {
                        row.show();
                        visiblesCount++;
                    } else {
                        row.hide();
                    }
                });

                $('#contador-visibles').text(visiblesCount);

                if (visiblesCount === 0) {
                    $('#tabla-vacia-mensaje').removeClass('d-none');
                    $('#tabla-usuarios').addClass('d-none');
                } else {
                    $('#tabla-vacia-mensaje').addClass('d-none');
                    $('#tabla-usuarios').removeClass('d-none');
                }
            }

            // ==========================================
            // GESTIÓN DE SELECCIÓN DE CHECKBOXES
            // ==========================================
            $(document).on('change', '.usuario-checkbox', function() {
                actualizarBarraAcciones();
            });

            function actualizarBarraAcciones() {
                var totalChecked = $('.usuario-checkbox:checked').length;
                $('#contador-lote').text(totalChecked);
                $('#contador-lote-roles').text(totalChecked);

                if (totalChecked > 0) {
                    $('.action-btn-lote').prop('disabled', false);
                } else {
                    $('.action-btn-lote').prop('disabled', true);
                }
            }

            // Seleccionar/Deseleccionar todos los visibles
            $('#seleccionar-todos-visibles').on('change', function() {
                var isChecked = $(this).is(':checked');
                $('.usuario-row:visible').each(function() {
                    var id = $(this).data('id');
                    $('#usuario_chk_' + id).prop('checked', isChecked);
                });
                actualizarBarraAcciones();
            });

            // ==========================================
            // MODAL DE PROCESAMIENTO EXCEL
            // ==========================================
            $('#lista-excel-modal').on('input', function() {
                var lineas = $(this).val().split('\n').filter(l => l.trim() !== '');
                $('#excel-contador-lineas').text(lineas.length + ' líneas');
            });

            $('#btn-procesar-excel-modal').on('click', function() {
                var textoCIs = $('#lista-excel-modal').val();
                var lineas = textoCIs.split('\n');
                var listaCIs = [];

                lineas.forEach(l => {
                    var val = l.trim();
                    if (val !== '') listaCIs.push(val.toLowerCase());
                });

                if (listaCIs.length === 0) {
                    Swal.fire('Atención', 'No se detectaron CIs válidos para procesar.', 'warning');
                    return;
                }

                var marcados = 0;
                $('.usuario-row').each(function() {
                    var row = $(this);
                    var textoFila = row.data('texto') ? row.data('texto').toString().toLowerCase() :
                        '';
                    var id = row.data('id');
                    var encontrado = listaCIs.some(ci => textoFila.indexOf(ci) !== -1);

                    if (encontrado) {
                        $('#usuario_chk_' + id).prop('checked', true);
                        marcados++;
                    }
                });

                actualizarBarraAcciones();
                $('#modalPegarRUs').modal('hide');
                Swal.fire('Proceso exitoso', 'Se han marcado ' + marcados + ' usuarios coincidentes.',
                    'success');
            });
        });
    </script>
@stop
