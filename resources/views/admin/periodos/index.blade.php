@extends('adminlte::page')

@section('content_header')
    <h1><b>Listado de periodos académicos</b></h1>
    <hr>
@stop

@section('content')
    <div class="row">
        <div class="col-md-10">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">Periodos registrados</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#ModalCreate">
                            <i class="fas fa-plus"></i> Crear nuevo
                        </button>
                        <a href="{{ route('admin.periodos.index') }}" class="btn btn-default btn-sm">Ver activos</a>
                        <a href="{{ route('admin.periodos.papelera') }}" class="btn btn-warning btn-sm">Ver papelera</a>
                        <!-- Modal Crear -->
                        <div class="modal fade" id="ModalCreate" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('admin.periodos.store') }}" method="POST">
                                        @csrf
                                        <div class="modal-header bg-primary text-white">
                                            <h5 class="modal-title">Registro de nuevo periodo</h5>
                                            <button type="button" class="close text-white"
                                                data-dismiss="modal">&times;</button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label>Nombre del periodo (*)</label>
                                                <input type="text" class="form-control" name="nombre_create"
                                                    value="{{ old('nombre_create') }}" required>
                                                @error('nombre_create')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <label>Gestión (*)</label>
                                                <select name="gestion_id" class="form-control" required>
                                                    <option value="">Seleccione una gestión</option>
                                                    @foreach ($gestiones as $gestion)
                                                        <option value="{{ $gestion->id }}"
                                                            {{ old('gestion_id') == $gestion->id ? 'selected' : '' }}>
                                                            {{ $gestion->nombre }}</option>
                                                    @endforeach
                                                </select>
                                                @error('gestion_id')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>Fecha Inicio (*)</label>
                                                        <input type="date" class="form-control" name="fecha_inicio"
                                                            value="{{ old('fecha_inicio') }}" required>
                                                        @error('fecha_inicio')
                                                            <small class="text-danger">{{ $message }}</small>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>Fecha Fin (*)</label>
                                                        <input type="date" class="form-control" name="fecha_fin"
                                                            value="{{ old('fecha_fin') }}" required>
                                                        @error('fecha_fin')
                                                            <small class="text-danger">{{ $message }}</small>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>Estado (*)</label>
                                                        <select name="estado_id" class="form-control" required>
                                                            <option value="">Seleccione un estado</option>
                                                            @foreach ($estados as $estado)
                                                                <option value="{{ $estado->id }}"
                                                                    {{ old('estado_id') == $estado->id ? 'selected' : '' }}>
                                                                    {{ $estado->nombre }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('estado_id')
                                                            <small class="text-danger">{{ $message }}</small>
                                                        @enderror
                                                    </div>
                                                </div>
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
                                <th>Nombre Periodo</th>
                                <th>Gestión</th>
                                <th>Fecha Inicio</th>
                                <th>Fecha Fin</th>
                                <th>Estado</th>
                                <th style="width: 120px">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($periodos as $periodo)
                                <tr>
                                    <td>{{ $periodo->id }}</td>
                                    <td>{{ $periodo->nombre }}</td>
                                    <td>{{ $periodo->gestion->nombre ?? 'N/A' }}</td>
                                    <td>{{ $periodo->fecha_inicio }}</td>
                                    <td>{{ $periodo->fecha_fin }}</td>
                                    <td>
                                        @if ($periodo->estado)
                                            <span class="badge"
                                                style="background-color: {{ $periodo->estado->color_hex }}; color: #fff;">
                                                {{ $periodo->estado->nombre }}
                                            </span>
                                        @else
                                            <span class="badge badge-secondary">Sin estado</span>
                                        @endif
                                    </td>
                                    <td class="d-flex justify-content-center">
                                        @if ($periodo->trashed())
                                            {{-- Botón Restaurar --}}
                                            <form action="{{ route('admin.periodos.restaurar', $periodo->id) }}"
                                                method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-info">
                                                    <i class="fas fa-trash-restore"></i>
                                                </button>
                                            </form>
                                        @else
                                            {{-- Botón Editar --}}
                                            <button type="button" class="btn btn-sm btn-success mr-1" data-toggle="modal"
                                                data-target="#ModalUpdate{{ $periodo->id }}">
                                                <i class="fas fa-pencil-alt"></i>
                                            </button>

                                            <!-- Modal Editar -->
                                            <div class="modal fade" id="ModalUpdate{{ $periodo->id }}" tabindex="-1"
                                                aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <form action="{{ route('admin.periodos.update', $periodo->id) }}"
                                                            method="POST">
                                                            @csrf @method('PUT')
                                                            <div class="modal-header bg-success text-white">
                                                                <h5 class="modal-title">Editar Periodo</h5>
                                                                <button type="button" class="close text-white"
                                                                    data-dismiss="modal">&times;</button>
                                                            </div>
                                                            <div class="modal-body text-left">
                                                                <div class="form-group">
                                                                    <label>Nombre del periodo</label>
                                                                    <input type="text" name="nombre_update"
                                                                        class="form-control"
                                                                        value="{{ old('nombre_update', $periodo->nombre) }}"
                                                                        required>
                                                                    @error('nombre_update')
                                                                        <small class="text-danger">{{ $message }}</small>
                                                                    @enderror
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>Gestión</label>
                                                                    <select name="gestion_id" class="form-control"
                                                                        required>
                                                                        @foreach ($gestiones as $gestion)
                                                                            <option value="{{ $gestion->id }}"
                                                                                {{ old('gestion_id', $periodo->gestion_id) == $gestion->id ? 'selected' : '' }}>
                                                                                {{ $gestion->nombre }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                    @error('gestion_id')
                                                                        <small class="text-danger">{{ $message }}</small>
                                                                    @enderror
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-md-4">
                                                                        <div class="form-group">
                                                                            <label>Fecha Inicio</label>
                                                                            <input type="date" class="form-control"
                                                                                name="fecha_inicio"
                                                                                value="{{ old('fecha_inicio', $periodo->fecha_inicio) }}"
                                                                                required>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <div class="form-group">
                                                                            <label>Fecha Fin</label>
                                                                            <input type="date" class="form-control"
                                                                                name="fecha_fin"
                                                                                value="{{ old('fecha_fin', $periodo->fecha_fin) }}"
                                                                                required>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <div class="form-group">
                                                                            <label>Estado</label>
                                                                            <select name="estado_id" class="form-control"
                                                                                required>
                                                                                <option value="">Seleccione un estado
                                                                                </option>
                                                                                @foreach ($estados as $estado)
                                                                                    <option value="{{ $estado->id }}"
                                                                                        {{ old('estado_id', $periodo->estado_id) == $estado->id ? 'selected' : '' }}>
                                                                                        {{ $estado->nombre }}
                                                                                    </option>
                                                                                @endforeach
                                                                            </select>
                                                                            @error('estado_id')
                                                                                <small
                                                                                    class="text-danger">{{ $message }}</small>
                                                                            @enderror
                                                                        </div>
                                                                    </div>
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

                                            {{-- Formulario y Botón de Eliminar --}}
                                            <form action="{{ route('admin.periodos.destroy', $periodo->id) }}"
                                                method="POST" id="miFormulario{{ $periodo->id }}">
                                                @csrf @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-danger"
                                                    onclick="confirmarEliminacion({{ $periodo->id }})">
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
                text: "Esta acción enviará el periodo a la papelera.",
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
