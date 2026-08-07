@extends('adminlte::page')

@section('title', 'Papelera de Inscripciones')

@section('plugins.Datatables', true)
@section('plugins.Sweetalert2', true)

@section('css')
    <style>
        #tabla-papelera th,
        #tabla-papelera td {
            padding: 0.45rem 0.6rem !important;
            vertical-align: middle !important;
            font-size: 0.85rem;
        }
    </style>
@stop

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1><i class="fas fa-fw fa-trash-restore mr-2"></i> Papelera - Inscripción a Carreras</h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('admin.inscripcion-carreras.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left mr-1"></i> Volver al Listado
                </a>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        @include('admin.alertas')
        <div class="card card-outline card-danger shadow-sm">
            <div class="card-header py-2">
                <h3 class="card-title font-weight-bold" style="font-size: 0.95rem;">Registros Eliminados (Papelera)</h3>
            </div>
            <div class="card-body p-2">
                <div class="table-responsive">
                    <table id="tabla-papelera" class="table table-bordered table-striped table-sm text-nowrap"
                        style="width:100%; font-size: 0.85rem;">
                        <thead>
                            <tr class="text-center">
                                <th style="width: 40px;">ID</th>
                                <th>Estudiante (CI / Nombre)</th>
                                <th>Carrera</th>
                                <th>Periodo</th>
                                <th>Fecha Eliminación</th>
                                <th class="text-center notexport" style="width: 100px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($inscripciones as $inscripcion)
                                <tr>
                                    <td class="text-center font-weight-bold">{{ $inscripcion->id }}</td>
                                    <td>
                                        <strong>{{ $inscripcion->estudiante->persona->ap_paterno ?? '' }}
                                            {{ $inscripcion->estudiante->persona->ap_materno ?? '' }}
                                            {{ $inscripcion->estudiante->persona->nombres ?? '' }}</strong>
                                        <br>
                                        <small class="text-muted">CI:
                                            {{ $inscripcion->estudiante->persona->ci ?? 'N/D' }}</small>
                                    </td>
                                    <td>
                                        {{ $inscripcion->carrera->nombre ?? 'N/D' }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-info">{{ $inscripcion->periodo->nombre ?? 'N/D' }}</span>
                                    </td>
                                    <td class="text-center text-danger">
                                        {{ $inscripcion->deleted_at ? $inscripcion->deleted_at->format('d/m/Y H:i') : '-' }}
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            {{-- Botón Restaurar --}}
                                            <form
                                                action="{{ route('admin.inscripcion-carreras.restaurar', $inscripcion->id) }}"
                                                method="POST" class="d-inline form-restaurar">
                                                @csrf
                                                @method('PUT')
                                                <button type="button"
                                                    class="btn btn-default btn-sm px-2 btn-accion-restaurar"
                                                    title="Restaurar registro">
                                                    <i class="fas fa-trash-restore text-success"></i>
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
        </div>
    </div>
@stop

@section('js')
    <script>
        $(function() {
            $('#tabla-papelera').DataTable({
                responsive: true,
                autoWidth: false,
                pageLength: 10,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                }
            });

            // Confirmación con SweetAlert para restaurar
            $(document).on('click', '.btn-accion-restaurar', function(e) {
                e.preventDefault();
                var form = $(this).closest('form');

                Swal.fire({
                    title: '¿Deseas restaurar este registro?',
                    text: "La inscripción volverá al listado activo.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, restaurar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@stop
