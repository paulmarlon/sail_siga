@extends('adminlte::page')

@section('title', 'Gestión General de Personal')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Gestión General de <b>Personal</b></h1>
        <div>
            <!-- Botón para ir a la Papelera general -->
            <a href="{{ route('admin.personal.trashed', $tipo ?? 'docente') }}" class="btn btn-secondary">
                <i class="fas fa-trash"></i> Papelera
            </a>
            <!-- Botón para crear nuevo registro -->
            <a href="{{ route('admin.personal.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nuevo Personal
            </a>
        </div>
    </div>
@stop

@section('content')
    @if (session('mensaje'))
        <div class="alert alert-{{ session('icono', 'success') }} alert-dismissible fade show" role="alert">
            {{ session('mensaje') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-users mr-1"></i> Listado General de Registros Activos</h3>
        </div>
        <div class="card-body">

            <!-- FILTRO RÁPIDO POR TIPO (DataTables) -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="filtro_tipo"><i class="fas fa-filter"></i> Filtrar por Tipo:</label>
                    <select id="filtro_tipo" class="form-control form-control-sm">
                        <option value="">-- Todos los Tipos --</option>
                        <option value="docente">Docentes</option>
                        <option value="administrativo">Administrativos</option>
                        <option value="planta">Personal de Planta</option>
                    </select>
                </div>
            </div>

            <table id="tabla-personal" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Foto</th>
                        <th>Tipo</th> <!-- Añadido para que el filtro de la columna funcione visualmente -->
                        <th>Apellidos y Nombres</th>
                        <th>CI</th>
                        <th>Profesión</th>
                        <th>Celular / Email</th>
                        <th>Usuario / Rol</th>
                        <th>Estado</th>
                        <th style="width: 150px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($personals as $index => $personal)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td class="text-center">
                                @if ($personal->persona && $personal->persona->foto_path)
                                    <img src="{{ asset($personal->persona->foto_path) }}" alt="Foto"
                                        class="img-circle shadow-sm" width="40" height="40"
                                        style="object-fit: cover;">
                                @else
                                    <img src="{{ asset('vendor/adminlte/dist/img/user2-160x160.jpg') }}" alt="Default"
                                        class="img-circle shadow-sm" width="40" height="40">
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-secondary text-uppercase">{{ $personal->tipo }}</span>
                            </td>
                            <td>
                                {{ $personal->persona->ap_paterno ?? '' }} {{ $personal->persona->ap_materno ?? '' }}
                                {{ $personal->persona->nombres ?? '' }}
                            </td>
                            <td>{{ $personal->persona->ci ?? 'N/A' }}</td>
                            <td>{{ $personal->profesion }}</td>
                            <td>
                                {{ $personal->persona->celular ?? 'Sin celular' }}<br>
                                <small
                                    class="text-muted">{{ $personal->usuario->email ?? ($personal->persona->email_personal ?? 'Sin correo') }}</small>
                            </td>
                            <td>
                                @if ($personal->usuario && $personal->usuario->roles->isNotEmpty())
                                    @foreach ($personal->usuario->roles as $role)
                                        <span class="badge badge-info">{{ $role->name }}</span>
                                    @endforeach
                                @else
                                    <span class="badge badge-secondary">Sin Rol</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-success">{{ $personal->estado->nombre ?? 'Vigente' }}</span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <!-- Ver -->
                                    <a href="{{ route('admin.personal.show', $personal->id) }}"
                                        class="btn btn-default btn-sm" title="Ver">
                                        <i class="fas fa-eye text-primary"></i>
                                    </a>
                                    <!-- Editar -->
                                    <a href="{{ route('admin.personal.edit', $personal->id) }}"
                                        class="btn btn-default btn-sm" title="Editar">
                                        <i class="fas fa-edit text-success"></i>
                                    </a>
                                    <!-- Eliminar (Soft Delete) -->
                                    <form action="{{ route('admin.personal.destroy', $personal->id) }}" method="POST"
                                        class="d-inline form-eliminar">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-default btn-sm" title="Enviar a papelera">
                                            <i class="fas fa-trash text-danger"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Inicialización de DataTable
            var table = $('#tabla-personal').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
                },
                "responsive": true,
                "autoWidth": false,
            });

            // Filtro dinámico por Tipo (Columna 2 de la tabla)
            $('#filtro_tipo').on('change', function() {
                var val = $.fn.dataTable.util.escapeRegex($(this).val());
                table.column(2).search(val ? '^' + val + '$' : '', true, false).draw();
            });

            // Confirmación con SweetAlert2 para enviar a la papelera
            $('.form-eliminar').submit(function(e) {
                e.preventDefault();
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "El registro será enviado a la papelera.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, enviar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.submit();
                    }
                })
            });
        });
    </script>
@stop
