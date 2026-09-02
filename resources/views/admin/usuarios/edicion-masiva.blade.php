@extends('adminlte::page')

@section('title', 'Edición de Cuentas de Usuario')

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

        #tabla-usuarios-edicion thead th {
            position: sticky;
            top: 0;
            z-index: 10;
            background-color: #f8f9fa !important;
            box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.1);
        }

        #tabla-usuarios-edicion th,
        #tabla-usuarios-edicion td {
            padding: 0.4rem 0.5rem !important;
            vertical-align: middle !important;
            font-size: 0.78rem;
        }

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

        .input-password.modificado {
            border-color: #28a745 !important;
            background-color: #e8f5e9 !important;
            color: #155724;
            font-weight: bold;
        }
    </style>
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fas fa-users-cog mr-2 text-success"></i> Edición Masiva / Individual de Cuentas</h1>
            <p class="text-muted mb-0">Control matricial y actualización sincronizada de credenciales y accesos.</p>
        </div>
        <div class="d-flex align-items-center">
            <button type="button" class="btn btn-outline-success btn-sm mr-2 d-none shadow-sm" id="btn-mostrar-filtros"
                onclick="toggleFiltros()">
                <i class="fas fa-filter mr-1"></i> <span id="toggle-filtros-text">Mostrar Filtros</span>
            </button>
            <a href="{{ route('admin.usuarios.index') }}" class="btn btn-secondary btn-sm shadow-sm font-weight-bold">
                <i class="fas fa-arrow-left mr-1"></i> Volver al Listado
            </a>
        </div>
    </div>
@stop

@section('content')
    <form action="{{ route('admin.usuarios.updateMasivo') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row">
            <!-- ================= COLUMNA 1: FILTROS Y BÚSQUEDA RÁPIDA ================= -->
            <div class="col-md-3 px-1" id="panel-filtros-izquierdo">
                <div class="card card-success card-outline shadow-sm mb-3">
                    <div class="card-header bg-white py-2 px-2 d-flex justify-content-between align-items-center">
                        <h6 class="card-title text-dark font-weight-bold mb-0" style="font-size: 0.85rem;">
                            <i class="fas fa-filter mr-1 text-success"></i> Filtrar Lote Actual
                        </h6>
                        <button type="button" class="btn btn-xs btn-outline-secondary font-weight-bold px-2 py-1"
                            onclick="toggleFiltros()" style="font-size: 0.7rem;">
                            <i class="fas fa-indent mr-1"></i> <span id="toggle-btn-text">Ocultar</span>
                        </button>
                    </div>
                    <div class="card-body p-2 bg-light">
                        <div class="form-group mb-2">
                            <label class="small font-weight-bold text-secondary mb-1">
                                <i class="fas fa-keyboard mr-1"></i> Filtrar Filas Visibles:
                            </label>
                            <input type="text" id="filtro-busqueda-edicion" class="form-control form-control-sm"
                                placeholder="Escribe para buscar..." style="font-size: 0.75rem;">
                        </div>

                        <div class="alert alert-default-info border-info small p-2 mb-2" style="font-size: 0.72rem;">
                            <i class="fas fa-info-circle mr-1 text-info"></i> Estás editando un lote de
                            <b>{{ $usuarios->count() }}</b> registros seleccionados previamente.
                        </div>

                        <div class="pt-2 border-top">
                            <span class="text-muted d-block" style="font-size: 0.72rem;">
                                <i class="fas fa-shield-alt mr-1"></i> Los cambios se guardarán de forma global al presionar
                                el botón inferior.
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ================= COLUMNA 2: TABLA MATRICIAL ================= -->
            <div class="col-md-9 px-1" id="columna-tabla-derecha">
                <div class="card card-success card-outline card-scroll shadow-sm mb-3">
                    <div class="card-header bg-white py-2 px-3 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <h3 class="card-title font-weight-bold mb-0 text-success" style="font-size: 0.85rem;">
                                <i class="fas fa-table mr-1"></i> Panel Matricial de Edición
                            </h3>
                        </div>
                        <span class="badge badge-success px-2 py-1" style="font-size: 0.75rem;">
                            Registros Visibles: <span id="contador-visibles">{{ $usuarios->count() }}</span>
                        </span>
                    </div>

                    <div class="card-body p-2 card-body-scroll">
                        <div id="tabla-vacia-mensaje" class="text-center text-muted py-5 d-none">
                            <i class="fas fa-search fa-3x mb-2 text-secondary"></i>
                            <p class="font-weight-bold mb-1">No se encontraron registros con los filtros aplicados.</p>
                        </div>

                        <table id="tabla-usuarios-edicion"
                            class="table table-bordered table-striped table-hover text-nowrap w-100 mb-0">
                            <thead>
                                <tr>
                                    <th class="text-center align-middle" style="width: 35px;">#</th>
                                    <th class="align-middle col-identidad" style="min-width: 250px;">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span><i class="fas fa-id-card mr-1"></i> Identidad / CI / Correo</span>
                                            <i class="fas fa-eye-slash text-muted cursor-pointer ml-2"
                                                onclick="$('.col-identidad').toggle()"
                                                title="Ocultar/Mostrar Identidad"></i>
                                        </div>
                                    </th>
                                    <th class="text-center align-middle" style="width: 40px;" title="Es Estudiante">Est.
                                    </th>
                                    <th class="text-center align-middle" style="width: 40px;" title="Es Personal">Pers.</th>

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

                                    <th class="align-middle" style="min-width: 200px;"><i class="fas fa-key mr-1"></i>
                                        Contraseña / Acceso</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($usuarios as $index => $user)
                                    @php
                                        $ciOriginal = $user->persona->ci ?? '';
                                        $esEstudianteDB = $user->persona && $user->persona->estudiante()->exists();
                                        $esPersonalDB = $user->persona && $user->persona->personal()->exists();

                                        $nombresPersona = strtolower($user->persona->nombres ?? '');
                                        $apPaterno = strtolower($user->persona->ap_paterno ?? '');
                                        $apMaterno = strtolower($user->persona->ap_materno ?? '');
                                        $ciPersona = strtolower($ciOriginal);
                                        $emailUser = strtolower($user->email ?? '');
                                        $textoBusqueda = "{$apPaterno} {$apMaterno} {$nombresPersona} {$ciPersona} {$emailUser}";
                                    @endphp

                                    <tr class="usuario-row" data-texto="{{ $textoBusqueda }}" style="font-size: 0.75rem;">
                                        <input type="hidden" name="usuarios[{{ $index }}][user_id]"
                                            value="{{ $user->id }}">

                                        <td class="text-center font-weight-bold text-muted align-middle">
                                            {{ $index + 1 }}
                                        </td>

                                        <td class="align-middle py-1 col-identidad">
                                            <div class="font-weight-bold text-dark">
                                                {{ $user->persona->ap_paterno ?? '' }}
                                                {{ $user->persona->ap_materno ?? '' }},
                                                {{ $user->persona->nombres ?? 'Sin Nombre' }}
                                            </div>
                                            <div class="d-flex align-items-center mt-1">
                                                <span class="badge badge-secondary mr-2">CI:
                                                    {{ $ciOriginal ?: 'S/N' }}</span>
                                                <div class="input-group input-group-sm" style="flex: 1;">
                                                    <input type="email" name="usuarios[{{ $index }}][email]"
                                                        class="form-control form-control-sm"
                                                        value="{{ old('usuarios.' . $index . '.email', $user->email) }}"
                                                        required placeholder="Correo electrónico"
                                                        style="font-size: 0.72rem;">
                                                </div>
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
                                                <div
                                                    class="custom-control custom-checkbox d-flex justify-content-center m-0">
                                                    <input type="checkbox" name="usuarios[{{ $index }}][roles][]"
                                                        value="{{ $rol->name }}"
                                                        id="rol_{{ $index }}_{{ $rol->id }}"
                                                        class="custom-control-input"
                                                        {{ in_array($rol->name, old('usuarios.' . $index . '.roles', $user->roles->pluck('name')->toArray())) ? 'checked' : '' }}>
                                                    <label class="custom-control-label"
                                                        for="rol_{{ $index }}_{{ $rol->id }}"></label>
                                                </div>
                                            </td>
                                        @endforeach

                                        <!-- Gestión de Contraseña con Estado -->
                                        <td class="align-middle py-1">
                                            <div class="d-flex flex-column">
                                                <!-- Indicador de Estado -->
                                                <div class="mb-1">
                                                    @if ($user->tiene_clave_por_defecto)
                                                        <span class="badge badge-warning text-dark px-1 py-0"
                                                            style="font-size: 0.65rem;">
                                                            <i class="fas fa-exclamation-triangle"></i> Clave: CI (Por
                                                            defecto)
                                                        </span>
                                                    @else
                                                        <span class="badge badge-success px-1 py-0"
                                                            style="font-size: 0.65rem;">
                                                            <i class="fas fa-check-circle"></i> Clave: Personalizada
                                                        </span>
                                                    @endif
                                                </div>

                                                <div class="custom-control custom-checkbox mb-1">
                                                    <input type="checkbox"
                                                        class="custom-control-input toggle-activar-password"
                                                        id="activar_pass_{{ $index }}"
                                                        name="usuarios[{{ $index }}][cambiar_password]"
                                                        value="1">
                                                    <label class="custom-control-label text-secondary font-weight-normal"
                                                        for="activar_pass_{{ $index }}"
                                                        style="font-size: 0.7rem;">
                                                        {{ $user->tiene_clave_por_defecto ? 'Cambiar CI por nueva' : 'Restablecer a CI' }}
                                                    </label>
                                                </div>

                                                <div class="input-group input-group-sm">
                                                    <input type="password" name="usuarios[{{ $index }}][password]"
                                                        class="form-control form-control-sm input-password"
                                                        value="{{ $user->tiene_clave_por_defecto ? $ciOriginal : '' }}"
                                                        data-ci="{{ $ciOriginal }}"
                                                        placeholder="{{ $user->tiene_clave_por_defecto ? 'CI por defecto' : 'Escriba para cambiar clave' }}"
                                                        autocomplete="new-password" disabled required
                                                        style="font-size: 0.72rem;">
                                                    <div class="input-group-append">
                                                        <button
                                                            class="btn btn-outline-secondary btn-sm btn-toggle-password"
                                                            type="button" title="Mostrar/Ocultar" disabled
                                                            style="font-size: 0.7rem; padding: 0.1rem 0.4rem;">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div
                        class="card-footer bg-white py-2 px-3 d-flex justify-content-between align-items-center border-top flex-wrap">
                        <div class="custom-control custom-checkbox my-1">
                            <input type="checkbox" class="custom-control-input" id="restablecer_ci_password"
                                name="restablecer_ci_password" value="1">
                            <label class="custom-control-label font-weight-bold text-dark cursor-pointer small"
                                for="restablecer_ci_password">
                                <i class="fas fa-sync-alt mr-1 text-success"></i> Restablecer clave masivamente usando el
                                número de CI de cada persona
                            </label>
                        </div>

                        <div class="my-1 text-right">
                            <a href="{{ route('admin.usuarios.index') }}"
                                class="btn btn-secondary btn-sm px-3 mr-1 font-weight-bold">Cancelar</a>
                            <button type="submit" class="btn btn-success btn-sm px-4 font-weight-bold shadow-sm">
                                <i class="fas fa-save mr-1"></i> Confirmar Edición ({{ $usuarios->count() }})
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@stop

@section('js')
    <script>
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
            $('#filtro-busqueda-edicion').on('keyup', function() {
                var textoBusqueda = $(this).val().toLowerCase().trim();
                var visiblesCount = 0;

                $('.usuario-row').each(function() {
                    var row = $(this);
                    var textoFila = row.data('texto') ? row.data('texto').toString().toLowerCase() :
                        '';

                    if (textoBusqueda === '' || textoFila.indexOf(textoBusqueda) !== -1) {
                        row.show();
                        visiblesCount++;
                    } else {
                        row.hide();
                    }
                });

                $('#contador-visibles').text(visiblesCount);

                if (visiblesCount === 0) {
                    $('#tabla-vacia-mensaje').removeClass('d-none');
                    $('#tabla-usuarios-edicion').addClass('d-none');
                } else {
                    $('#tabla-vacia-mensaje').addClass('d-none');
                    $('#tabla-usuarios-edicion').removeClass('d-none');
                }
            });

            $(document).on('change', '.toggle-activar-password', function() {
                var container = $(this).closest('td');
                var input = container.find('.input-password');
                var btn = container.find('.btn-toggle-password');

                if ($(this).is(':checked')) {
                    input.prop('disabled', false).focus();
                    btn.prop('disabled', false);
                } else {
                    input.prop('disabled', true).val(input.data('ci')).removeClass('modificado');
                    btn.prop('disabled', true);
                    if (input.attr('type') === 'text') {
                        input.attr('type', 'password');
                        btn.find('i').removeClass('fa-eye-slash').addClass('fa-eye');
                    }
                }
            });

            $(document).on('click', '.btn-toggle-password', function() {
                var input = $(this).closest('.input-group').find('.input-password');
                var icon = $(this).find('i');

                if (input.attr('type') === 'password') {
                    input.attr('type', 'text');
                    icon.removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    input.attr('type', 'password');
                    icon.removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });

            $(document).on('input', '.input-password', function() {
                var valActual = $(this).val();
                var ciOriginal = $(this).data('ci');

                if (valActual !== ciOriginal && valActual !== '') {
                    $(this).addClass('modificado');
                } else {
                    $(this).removeClass('modificado');
                }
            });
        });
    </script>
@stop
