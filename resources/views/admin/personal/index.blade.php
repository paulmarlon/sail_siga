@extends('adminlte::page')

@section('title', 'Gestión General de Personal')

<!-- Activamos los plugins globales definidos en config/adminlte.php -->
@section('plugins.Datatables', true)
@section('plugins.Sweetalert2', true)

@section('css')
    <style>
        /* Interlineado compacto y profesional */
        #tabla-personal th,
        #tabla-personal td {
            padding: 0.45rem !important;
            vertical-align: middle !important;
        }

        /* Ajuste estético para los botones de exportación */
        .dt-buttons {
            margin-bottom: 1rem;
        }

        @media (min-width: 768px) {
            .dt-buttons {
                float: left;
            }
        }
    </style>
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Gestión General de <b>Personal</b></h1>
        <div>
            <a href="{{ route('admin.personal.trashed', $tipo ?? 'docente') }}" class="btn btn-secondary btn-sm"
                id="btn-papelera">
                <i class="fas fa-trash"></i> Papelera
            </a>
            <a href="{{ route('admin.personal.create', $tipo ?? 'docente') }}" class="btn btn-primary btn-sm">
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

            <!-- FILTRO RÁPIDO POR TIPO -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="filtro_tipo"><i class="fas fa-filter"></i> Filtrar por Tipo:</label>
                    <select id="filtro_tipo" class="form-control form-control-sm">
                        <option value="">-- Todos los Tipos --</option>
                        <option value="docente" {{ isset($tipo) && $tipo == 'docente' ? 'selected' : '' }}>Docentes
                        </option>
                        <option value="administrativo" {{ isset($tipo) && $tipo == 'administrativo' ? 'selected' : '' }}>
                            Administrativos</option>
                        <option value="planta" {{ isset($tipo) && $tipo == 'planta' ? 'selected' : '' }}>Personal de
                            Planta</option>
                    </select>
                </div>
            </div>

            <table id="tabla-personal" class="table table-bordered table-striped table-sm">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Foto</th>
                        <th>Tipo</th>
                        <th>Apellidos y Nombres</th>
                        <th>CI</th>
                        <th>Profesión</th>
                        <th>Celular / Correo Personal</th>
                        <th>Estado</th>
                        <th style="width: 130px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($personals as $index => $personal)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td class="text-center">
                                @if ($personal->persona && $personal->persona->foto_path)
                                    <img src="{{ asset($personal->persona->foto_path) }}" alt="Foto"
                                        class="img-circle shadow-sm" width="32" height="32"
                                        style="object-fit: cover;">
                                @else
                                    <img src="{{ asset('vendor/adminlte/dist/img/user2-160x160.jpg') }}" alt="Default"
                                        class="img-circle shadow-sm" width="32" height="32">
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
                            <td>{{ $personal->profesion ?? 'No especificada' }}</td>
                            <td>
                                {{ $personal->persona->celular ?? 'Sin celular' }}<br>
                                <small class="text-muted">{{ $personal->persona->email_personal ?? 'Sin correo' }}</small>
                            </td>
                            <td>
                                <span class="badge badge-success">{{ $personal->estado->nombre ?? 'Vigente' }}</span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.personal.show', $personal->id) }}" class="btn btn-default"
                                        title="Ver detalles">
                                        <i class="fas fa-eye text-primary"></i>
                                    </a>
                                    <a href="{{ route('admin.personal.edit', $personal->id) }}" class="btn btn-default"
                                        title="Editar datos laborales">
                                        <i class="fas fa-edit text-success"></i>
                                    </a>
                                    <form action="{{ route('admin.personal.destroy', $personal->id) }}" method="POST"
                                        class="d-inline form-eliminar">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-default" title="Enviar a papelera">
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
            // Como los plugins ya están cargados globalmente, inicializamos directamente DataTables con los botones
            var table = $('#tabla-personal').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
                },
                "responsive": true,
                "autoWidth": false,
                "dom": "<'row'<'col-md-3'l><'col-md-5'B><'col-md-4'f>>" +
                    "<'row'<'col-md-12'tr>>" +
                    "<'row'<'col-md-5'i><'col-md-7'p>>",
                "buttons": [{
                        extend: 'copy',
                        text: '<i class="fas fa-copy"></i>',
                        titleAttr: 'Copiar',
                        className: 'btn btn-secondary btn-sm'
                    },
                    {
                        extend: 'excel',
                        text: '<i class="fas fa-file-excel"></i>',
                        titleAttr: 'Exportar a Excel',
                        className: 'btn btn-success btn-sm'
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="fas fa-file-pdf"></i>',
                        titleAttr: 'Exportar a PDF',
                        className: 'btn btn-danger btn-sm'
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print"></i>',
                        titleAttr: 'Imprimir',
                        className: 'btn btn-info btn-sm'
                    },
                    {
                        extend: 'colvis',
                        text: '<i class="fas fa-columns"></i>',
                        titleAttr: 'Columnas',
                        className: 'btn btn-warning btn-sm'
                    }
                ]
            });

            // Filtro por tipo URL inicial
            var tipoUrl = "{{ $tipo ?? '' }}";
            if (tipoUrl) {
                table.column(2).search('^' + tipoUrl + '$', true, false).draw();
            }

            // Filtro dinámico por Tipo
            $('#filtro_tipo').on('change', function() {
                var val = $(this).val();
                var escapedVal = $.fn.dataTable.util.escapeRegex(val);

                table.column(2).search(escapedVal ? '^' + escapedVal + '$' : '', true, false).draw();

                if (val) {
                    $('#btn-papelera').attr('href', "{{ url('admin/personal/trashed') }}/" + val);
                } else {
                    $('#btn-papelera').attr('href', "{{ route('admin.personal.trashed', 'docente') }}");
                }
            });

            // Confirmación con SweetAlert2 centralizado
            $(document).on('submit', '.form-eliminar', function(e) {
                e.preventDefault();
                var form = this;
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
                        form.submit();
                    }
                })
            });
        });
    </script>
@stop
