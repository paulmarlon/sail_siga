@extends('adminlte::page')

@section('content_header')
    <h1><b>Listado de turnos académicos</b></h1>
    <hr>
@stop

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">Turnos registrados</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#ModalCreate">
                            <i class="fas fa-plus"></i> Crear nuevo
                        </button>
                        <a href="{{ route('admin.turnos.index') }}" class="btn btn-default btn-sm">Ver activos</a>
                        <a href="{{ route('admin.turnos.papelera') }}" class="btn btn-warning btn-sm">Ver papelera</a>

                        <!-- Modal Crear -->
                        <div class="modal fade" id="ModalCreate" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('admin.turnos.store') }}" method="POST">
                                        @csrf
                                        <div class="modal-header bg-primary text-white">
                                            <h5 class="modal-title">Registro de nuevo turno</h5>
                                            <button type="button" class="close text-white"
                                                data-dismiss="modal">&times;</button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label>Nombre del turno (*)</label>
                                                <input type="text" class="form-control" name="nombre_create"
                                                    value="{{ old('nombre_create') }}" required>
                                                @error('nombre_create')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-dismiss="modal">Cerrar</button>
                                            <button type="submit" class="btn btn-primary">Guardar</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <table class="table table-bordered table-striped table-hover table-sm">
                        <thead>
                            <tr>
                                <th style="width: 10px">Nro.</th>
                                <th>Nombre</th>
                                <th style="width: 100px">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($turnos as $turno)
                                <tr>
                                    <td>{{ $turno->id }}</td>
                                    <td>{{ $turno->nombre }}</td>
                                    <td class="d-flex justify-content-center">
                                        @if ($turno->trashed())
                                            {{-- Botón Restaurar --}}
                                            <form action="{{ route('admin.turnos.restaurar', $turno->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-info">
                                                    <i class="fas fa-trash-restore"></i>
                                                </button>
                                            </form>
                                        @else
                                            <button type="button" class="btn btn-sm btn-success mr-1" data-toggle="modal"
                                                data-target="#ModalUpdate{{ $turno->id }}">
                                                <i class="fas fa-pencil-alt"></i>
                                            </button>

                                            <!-- Modal Editar -->
                                            <div class="modal fade" id="ModalUpdate{{ $turno->id }}" tabindex="-1"
                                                aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <form action="{{ route('admin.turnos.update', $turno->id) }}"
                                                            method="POST">
                                                            @csrf @method('PUT')
                                                            <div class="modal-header bg-success text-white">
                                                                <h5 class="modal-title">Editar Turno</h5>
                                                                <button type="button" class="close text-white"
                                                                    data-dismiss="modal">&times;</button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="form-group">
                                                                    <label>Nombre del turno</label>
                                                                    <input type="text" name="nombre_update"
                                                                        class="form-control"
                                                                        value="{{ old('nombre_update', $turno->nombre) }}"
                                                                        required>
                                                                    @error('nombre_update')
                                                                        <small class="text-danger">{{ $message }}</small>
                                                                    @enderror
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary"
                                                                    data-dismiss="modal">Cerrar</button>
                                                                <button type="submit"
                                                                    class="btn btn-success">Actualizar</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

                                            <form action="{{ route('admin.turnos.destroy', $turno->id) }}" method="POST"
                                                id="miFormulario{{ $turno->id }}">
                                                @csrf @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-danger"
                                                    onclick="confirmarEliminacion({{ $turno->id }})">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        function confirmarEliminacion(id) {
            Swal.fire({
                title: '¿Está seguro?',
                text: "Esta acción enviará el turno a la papelera.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('miFormulario' + id).submit();
                }
            });
        }
    </script>

    @if ($errors->any())
        <script>
            $(document).ready(function() {
                @if (session('modal_id'))
                    $('#ModalUpdate{{ session('modal_id') }}').modal('show');
                @else
                    $('#ModalCreate').modal('show');
                @endif
            });
        </script>
    @endif

    @include('admin.alertas')
@stop
