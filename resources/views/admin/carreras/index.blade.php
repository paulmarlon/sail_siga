@extends('adminlte::page')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1><b>Listado de Carreras</b></h1>
        <div class="card-tools">
            <a href="{{ route('admin.carreras.index') }}" class="btn btn-success btn-sm">Ver Activos</a>
            <a href="{{ route('admin.carreras.index', ['papelera' => 1]) }}" class="btn btn-danger btn-sm">Ver Papelera</a>
            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#ModalCreate">
                <i class="fas fa-plus"></i> Nueva Carrera
            </button>
        </div>
    </div>
@stop

@section('content')
    <div class="card shadow-sm border-0">
        <div class="card-body p-2">
            <table id="carreras-table" class="table table-bordered table-striped table-sm" style="font-size: 0.85rem;">
                <thead>
                    <tr class="text-secondary text-uppercase">
                        <th>Sigla</th>
                        <th>Resolución</th> <!-- Añadido -->
                        <th>Nombre</th>
                        <th>Duración</th>
                        <th>Título</th>
                        <th>Nivel</th>
                        <th>Estado</th>
                        <th>Tronco Común</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($carreras as $c)
                        <tr>
                            <td class="font-weight-bold">{{ $c->sigla }}</td>
                            <td>{{ $c->resolucion ?? 'N/A' }}</td> <!-- Nuevo campo -->
                            <td>{{ $c->nombre }}</td>
                            <td>{{ $c->duracion }}</td>
                            <td>{{ $c->titulo }}</td>
                            <td>{{ $c->nivel->nombre ?? 'N/A' }}</td>
                            <td>
                                <span class="badge"
                                    style="background-color: {{ $c->estado->color_hex ?? '#ccc' }}; color: #fff;">
                                    {{ $c->estado->nombre ?? 'N/A' }}
                                </span>
                            </td>
                            <td>
                                @if ($c->es_tronco_comun)
                                    <span class="badge badge-success">SÍ</span>
                                @else
                                    <span class="badge badge-secondary">NO</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group">
                                    @if ($c->trashed())
                                        {{-- Formulario Restaurar con SweetAlert --}}
                                        <form action="{{ route('admin.carreras.restaurar', $c->id) }}" method="POST"
                                            id="formRestaurar{{ $c->id }}">
                                            @csrf
                                            <button type="button" class="btn btn-xs btn-info"
                                                onclick="confirmarRestauracion({{ $c->id }})" title="Restaurar">
                                                <i class="fas fa-trash-restore"></i>
                                            </button>
                                        </form>
                                    @else
                                        <!-- Botón Editar -->
                                        <button type="button" class="btn btn-xs btn-success btn-edit" data-toggle="modal"
                                            data-target="#ModalUpdate" data-id="{{ $c->id }}"
                                            data-sigla="{{ $c->sigla }}" data-resolucion="{{ $c->resolucion }}"
                                            data-nombre="{{ $c->nombre }}" data-duracion="{{ $c->duracion }}"
                                            data-titulo="{{ $c->titulo }}" data-nivel_id="{{ $c->nivel_id }}"
                                            data-estado_id="{{ $c->estado_id }}"
                                            data-carrera_base_id="{{ $c->carrera_base_id }}"
                                            data-es_tronco_comun="{{ $c->es_tronco_comun }}">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        {{-- Formulario Eliminar con SweetAlert --}}
                                        <form action="{{ route('admin.carreras.destroy', $c->id) }}" method="POST"
                                            class="ml-1" id="formEliminar{{ $c->id }}">
                                            @csrf @method('DELETE')
                                            <button type="button" class="btn btn-xs btn-danger"
                                                onclick="confirmarEliminacion({{ $c->id }})"
                                                title="Enviar a papelera">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL ÚNICO DE EDICIÓN --}}
    <div class="modal fade" id="ModalUpdate" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="form-update" method="POST">
                    @csrf @method('PUT')
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">Editar carrera</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body py-2">
                        <!-- Fila 1: Sigla y Resolución -->
                        <div class="form-row mb-2">
                            <div class="col-6">
                                <label class="small mb-0">Sigla</label>
                                <input type="text" name="sigla" id="m_sigla" class="form-control form-control-sm"
                                    required>
                            </div>
                            <div class="col-6">
                                <label class="small mb-0">Resolución</label>
                                <input type="text" name="resolucion" id="m_resolucion"
                                    class="form-control form-control-sm">
                            </div>
                        </div>

                        <!-- Nombre -->
                        <div class="form-group mb-2">
                            <label class="small mb-0">Nombre</label>
                            <input type="text" name="nombre" id="m_nombre" class="form-control form-control-sm"
                                required>
                        </div>

                        <!-- Fila 2: Duración y Título -->
                        <div class="form-row mb-2">
                            <div class="col-6">
                                <label class="small mb-0">Duración</label>
                                <input type="number" name="duracion" id="m_duracion" class="form-control form-control-sm"
                                    required>
                            </div>
                            <div class="col-6">
                                <label class="small mb-0">Título</label>
                                <input type="text" name="titulo" id="m_titulo" class="form-control form-control-sm"
                                    required>
                            </div>
                        </div>

                        <!-- Fila 3: Nivel y Estado -->
                        <div class="form-row mb-2">
                            <div class="col-6">
                                <label class="small mb-0">Nivel</label>
                                <select name="nivel_id" id="m_nivel_id" class="form-control form-control-sm" required>
                                    @foreach ($niveles as $n)
                                        <option value="{{ $n->id }}">{{ $n->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="small mb-0">Estado</label>
                                <select name="estado_id" id="m_estado_id" class="form-control form-control-sm" required>
                                    @foreach ($estados as $e)
                                        <option value="{{ $e->id }}">{{ $e->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Carrera Base -->
                        <div class="form-group mb-2">
                            <label class="small mb-0">Carrera Base</label>
                            <select name="carrera_base_id" id="m_carrera_base_id" class="form-control form-control-sm">
                                <option value="">Ninguna</option>
                                @foreach ($carreras as $c)
                                    <option value="{{ $c->id }}">{{ $c->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Checkbox -->
                        <div class="custom-control custom-checkbox mt-2">
                            <input type="checkbox" name="es_tronco_comun" id="m_es_tronco_comun"
                                class="custom-control-input" value="1">
                            <label class="custom-control-label small" for="m_es_tronco_comun">Es carrera de Tronco
                                Común</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Actualizar carrera</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('admin.carreras.modal_create')
@stop

@section('css')
    <style>
        .table-compact td,
        .table-compact th {
            padding: 0.3rem !important;
            vertical-align: middle !important;
            font-size: 0.85rem;
        }

        .table-compact thead th {
            background-color: #f4f6f9;
            font-size: 0.75rem;
            text-transform: uppercase;
        }

        .btn-xs {
            padding: 0.15rem 0.4rem !important;
            font-size: 0.75rem !important;
        }

        .card-body {
            padding: 0.75rem !important;
        }
    </style>
@stop

@section('js')
    @include('admin.alertas')
    <script>
        $(document).ready(function() {
            // Inicialización de DataTables con botones
            $('#carreras-table').DataTable({
                "responsive": true,
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json"
                },
                "dom": 'Bfrtip',
                "buttons": [{
                        extend: 'excel',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'btn-success btn-sm'
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        className: 'btn-danger btn-sm'
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print"></i> Imprimir',
                        className: 'btn-info btn-sm'
                    },
                    {
                        extend: 'colvis',
                        text: '<i class="fas fa-columns"></i> Columnas',
                        className: 'btn btn-sm btn-secondary'
                    }
                ]
            });

            // Lógica para llenar el Modal Único
            // Lógica para llenar el Modal Único
            $(document).on('click', '.btn-edit', function() {
                let id = $(this).data('id');
                $('#form-update').attr('action', '{{ url('admin/carreras') }}/' + id);

                // Cargar los valores en los campos
                $('#m_sigla').val($(this).data('sigla'));
                $('#m_resolucion').val($(this).data('resolucion'));
                $('#m_nombre').val($(this).data('nombre'));
                $('#m_duracion').val($(this).data('duracion'));
                $('#m_titulo').val($(this).data('titulo'));
                $('#m_nivel_id').val($(this).data('nivel_id'));
                $('#m_estado_id').val($(this).data('estado_id'));

                // Asignación correcta de la Carrera Base
                let carreraBaseId = $(this).data('carrera_base_id');
                $('#m_carrera_base_id').val(carreraBaseId ? carreraBaseId : '');

                // Marcar checkbox
                $('#m_es_tronco_comun').prop('checked', $(this).data('es_tronco_comun') == 1);
            });
        });

        // Función de SweetAlert2 para enviar a papelera
        function confirmarEliminacion(id) {
            Swal.fire({
                title: '¿Enviar a papelera?',
                text: "La carrera será movida a la papelera de reciclaje.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, enviar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('formEliminar' + id).submit();
                }
            });
        }

        // Función de SweetAlert2 para restaurar registro
        function confirmarRestauracion(id) {
            Swal.fire({
                title: '¿Desea restaurar esta carrera?',
                text: "La carrera volverá a estar activa en el sistema.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#17a2b8',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, restaurar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('formRestaurar' + id).submit();
                }
            });
        }
    </script>
@stop
