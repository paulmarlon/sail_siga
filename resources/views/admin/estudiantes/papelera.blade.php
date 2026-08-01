@extends('adminlte::page')

@section('title', 'Papelera de Estudiantes')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1><i class="fas fa-trash text-danger"></i> Papelera de Estudiantes</h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('admin.estudiantes.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver a la Lista
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

            <table id="tabla-papelera" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th style="width: 10px">#</th>
                        <th>C.I.</th>
                        <th>Apellidos y Nombres</th>
                        <th>R.U.</th>
                        <th>Estado Original</th>
                        <th>Fecha de Eliminación</th>
                        <th style="width: 100px">Acciones</th>
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
                            <td>
                                <span class="badge"
                                    style="background-color: {{ $estudiante->estado->color_hex ?? '#6c757d' }}; color: white;">
                                    {{ $estudiante->estado->nombre ?? 'N/A' }}
                                </span>
                            </td>
                            <td>{{ $estudiante->deleted_at }}</td>
                            <td class="text-center">
                                <form action="{{ route('admin.estudiantes.restaurar', $estudiante->id) }}" method="POST"
                                    class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm" title="Restaurar estudiante">
                                        <i class="fas fa-trash-restore"></i> Restaurar
                                    </button>
                                </form>
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
            $('#tabla-papelera').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
                }
            });
        });
    </script>
@stop
