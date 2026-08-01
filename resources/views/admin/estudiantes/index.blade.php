@extends('adminlte::page') <!-- O tu layout principal de administración -->

@section('title', 'Gestión de Estudiantes')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1><i class="fas fa-user-graduate"></i> Lista de Estudiantes</h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('admin.estudiantes.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nuevo Estudiante
                </a>
                <a href="{{ route('admin.estudiantes.papelera') }}" class="btn btn-secondary">
                    <i class="fas fa-trash"></i> Papelera
                </a>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            @if (session('mensaje'))
                <div class="alert alert-{{ session('icon', 'success') }} alert-dismissible fade show" role="alert">
                    {{ session('mensaje') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <table id="tabla-estudiantes" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th style="width: 10px">#</th>
                        <th>C.I.</th>
                        <th>Apellidos y Nombres</th>
                        <th>R.U.</th>
                        <th>Celular</th>
                        <th>Padre / Tutor / Apoderado (PPFF)</th>
                        <th>Estado</th>
                        <th style="width: 120px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($estudiantes as $index => $estudiante)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $estudiante->persona->ci ?? 'S/C' }}</td>
                            <td>{{ $estudiante->persona->ap_paterno }} {{ $estudiante->persona->ap_materno }}
                                {{ $estudiante->persona->nombres }}</td>
                            <td>
                                <span
                                    class="badge badge-info">{{ $estudiante->registro_universitario ?? 'Sin Asignar' }}</span>
                            </td>
                            <td>{{ $estudiante->persona->celular ?? 'No registrado' }}</td>
                            <td>
                                @if ($estudiante->ppffs->count() > 0)
                                    <ul class="list-unstyled mb-0">
                                        @foreach ($estudiante->ppffs as $ppff)
                                            <li>
                                                <strong>{{ $ppff->pivot->parentesco }}:</strong>
                                                {{-- CORREGIDO: $ppff ya es la persona directamente --}}
                                                {{ $ppff->ap_paterno }} {{ $ppff->ap_materno }} {{ $ppff->nombres }}
                                                @if ($ppff->pivot->es_tutor_principal)
                                                    <span class="badge badge-success"
                                                        style="font-size: 10px;">Principal</span>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <span class="text-muted font-italic">Sin tutores asignados</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge"
                                    style="background-color: {{ $estudiante->estado->color_hex ?? '#6c757d' }}; color: white;">
                                    {{ $estudiante->estado->nombre ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <a href="{{ route('admin.estudiantes.show', $estudiante->id) }}"
                                        class="btn btn-default btn-sm" title="Ver detalles">
                                        <i class="fas fa-eye text-primary"></i>
                                    </a>
                                    <a href="{{ route('admin.estudiantes.edit', $estudiante->id) }}"
                                        class="btn btn-default btn-sm" title="Editar">
                                        <i class="fas fa-edit text-success"></i>
                                    </a>
                                    <form action="{{ route('admin.estudiantes.destroy', $estudiante->id) }}" method="POST"
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
            $('#tabla-estudiantes').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
                }
            });
        });
    </script>
@stop
