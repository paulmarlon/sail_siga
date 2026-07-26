@extends('adminlte::page')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1><b>Listado de Grados</b></h1>
        <div class="card-tools">
            <a href="{{ route('admin.grados.index') }}" class="btn btn-success btn-sm">Ver Activos</a>
            <a href="{{ route('admin.grados.papelera') }}" class="btn btn-danger btn-sm">Ver Papelera</a>
            <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#ModalCreate">
                <i class="fas fa-plus"></i> Nuevo Grado
            </button>
        </div>
    </div>
@stop

@section('content')
    <div class="card shadow-sm border-0">
        <div class="card-body p-2">
            <table id="grados-table" class="table table-bordered table-striped table-compact">
                <thead>
                    <tr class="text-secondary">
                        <th>NOMBRE</th>
                        <th>ORDEN</th>
                        <th>CICLO</th>
                        <th>NIVEL</th>
                        <th>ESTADO</th>
                        <th>ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($grados as $g)
                        <tr>
                            <td>{{ $g->nombre }}</td>
                            <td>{{ $g->orden }}</td>
                            <td>{{ $g->ciclo == 1 ? 'Tronco Común' : 'Especialidad' }}</td>
                            <td>{{ $g->nivel->nombre ?? 'N/A' }}</td>
                            <td><span class="badge"
                                    style="background-color: {{ $g->estado->color_hex ?? '#ccc' }}; color: #fff;">{{ $g->estado->nombre ?? 'N/A' }}</span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    @if ($g->trashed())
                                        {{-- Formulario de Restaurar con SweetAlert --}}
                                        <form action="{{ route('admin.grados.restaurar', $g->id) }}" method="POST"
                                            id="formRestaurar{{ $g->id }}">
                                            @csrf
                                            <button type="button" class="btn btn-xs btn-info"
                                                onclick="confirmarRestauracion({{ $g->id }})" title="Restaurar">
                                                <i class="fas fa-trash-restore"></i>
                                            </button>
                                        </form>
                                    @else
                                        <button class="btn btn-xs btn-success" data-toggle="modal"
                                            data-target="#ModalUpdate{{ $g->id }}"><i
                                                class="fas fa-edit"></i></button>

                                        {{-- Formulario de Eliminar con SweetAlert --}}
                                        <form action="{{ route('admin.grados.destroy', $g->id) }}" method="POST"
                                            class="ml-1" id="formEliminar{{ $g->id }}">
                                            @csrf @method('DELETE')
                                            <button type="button" class="btn btn-xs btn-danger"
                                                onclick="confirmarEliminacion({{ $g->id }})"
                                                title="Enviar a papelera">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        @include('admin.grados.modal_edit', ['grado' => $g])
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @include('admin.grados.modal_create')
@stop

@section('js')
    @include('admin.alertas')
    <script>
        $(document).ready(function() {
            $('#grados-table').DataTable({
                "responsive": true,
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json"
                }
            });

            // Lógica para abrir el modal automáticamente si hay errores de validación
            @if ($errors->any())
                @if (session('modal_id'))
                    $('#ModalUpdate{{ session('modal_id') }}').modal('show');
                @else
                    $('#ModalCreate').modal('show');
                @endif
            @endif
        });

        // Función de SweetAlert2 para enviar a papelera
        function confirmarEliminacion(id) {
            Swal.fire({
                title: '¿Enviar a papelera?',
                text: "El grado será movido a la papelera.",
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
                title: '¿Desea restaurar este grado?',
                text: "El grado volverá a estar activo en el sistema.",
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
