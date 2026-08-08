@extends('adminlte::page')

@section('title', 'Papelera de Matriculaciones')

@section('css')
    <style>
        #tabla-papelera-matriculaciones th,
        #tabla-papelera-matriculaciones td {
            padding: 0.35rem 0.5rem !important;
            vertical-align: middle !important;
            font-size: 0.82rem;
        }
    </style>
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fas fa-trash-restore text-warning mr-2"></i> Papelera de Matriculaciones</h1>
            <p class="text-muted mb-0">Control de registros eliminados de matriculación de materias.</p>
        </div>
        <div>
            <a href="{{ route('admin.matriculacion-materias.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left mr-1"></i> Volver al Listado
            </a>
        </div>
    </div>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="icon fas fa-check"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="icon fas fa-ban"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card card-outline card-warning shadow-sm">
        <div class="card-header py-2">
            <h3 class="card-title font-weight-bold" style="font-size: 0.95rem;">
                Registros Eliminados ({{ isset($matriculacionesEliminadas) ? $matriculacionesEliminadas->count() : 0 }}
                registros)
            </h3>
        </div>
        <div class="card-body p-2">
            <table id="tabla-papelera-matriculaciones"
                class="table table-bordered table-striped table-hover dt-responsive nowrap text-nowrap" style="width:100%">
                <thead class="thead-dark">
                    <tr>
                        <th class="notexport" style="width: 35px;">ID</th>
                        <th>Estudiante (CI / RU)</th>
                        <th>Materia / Oferta / Periodo</th>
                        <th>Fecha de Eliminación</th>
                        <th class="notexport text-center" style="width: 100px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @if (isset($matriculacionesEliminadas))
                        @foreach ($matriculacionesEliminadas as $mat)
                            @php
                                $periodoNombre = $mat->oferta->periodo->nombre ?? 'N/A';
                                $materiaNombre = $mat->oferta->pensum->materia->nombre ?? 'N/A';
                            @endphp
                            <tr>
                                <td class="font-weight-bold text-center">{{ $mat->id }}</td>
                                <td>
                                    <span class="font-weight-bold text-dark">
                                        {{ $mat->estudiante->persona->ap_paterno ?? '' }}
                                        {{ $mat->estudiante->persona->ap_materno ?? '' }}
                                        {{ $mat->estudiante->persona->nombres ?? '' }}
                                    </span><br>
                                    <small class="text-muted">
                                        CI: {{ $mat->estudiante->persona->ci ?? 'S/C' }} | RU:
                                        {{ $mat->estudiante->registro_universitario ?? 'S/RU' }}
                                    </small>
                                </td>
                                <td>
                                    <span class="font-weight-bold text-secondary">{{ $materiaNombre }}</span><br>
                                    <small class="text-muted"><i class="fas fa-calendar-alt mr-1"></i>Periodo:
                                        {{ $periodoNombre }}</small>
                                </td>
                                <td>{{ $mat->deleted_at ? $mat->deleted_at->format('d/m/Y H:i') : 'N/A' }}</td>
                                <td class="text-center">
                                    <form action="{{ route('admin.matriculacion-materias.restaurar', $mat->id) }}"
                                        method="POST" class="d-inline">
                                        @csrf
                                        @method('POST')
                                        <button type="submit" class="btn btn-success btn-xs px-2"
                                            title="Restaurar registro">
                                            <i class="fas fa-trash-restore"></i> Restaurar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(function() {
            var table = $('#tabla-papelera-matriculaciones').DataTable({
                responsive: true,
                autoWidth: false,
                pageLength: 10,
                order: [],
                columnDefs: [{
                    targets: 0,
                    render: function(data, type, row, meta) {
                        return meta.row + 1 + meta.settings._iDisplayStart;
                    }
                }],
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "Todos"]
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                },
                dom: '<"row mx-0 border-bottom py-2"<"col-md-4"B><"col-md-3"l><"col-md-5"f>>rt<"row mx-0 pt-2 align-items-center"<"col-md-6"i><"col-md-6 d-flex justify-content-end"p>>',
                buttons: [{
                        extend: 'copy',
                        text: '<i class="fas fa-copy"></i>',
                        className: 'btn btn-secondary btn-sm btn-flat',
                        titleAttr: 'Copiar',
                        exportOptions: {
                            columns: ':not(.notexport)'
                        }
                    },
                    {
                        extend: 'excel',
                        text: '<i class="fas fa-file-excel"></i>',
                        className: 'btn btn-success btn-sm btn-flat',
                        titleAttr: 'Excel',
                        exportOptions: {
                            columns: ':not(.notexport)'
                        }
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="fas fa-file-pdf"></i>',
                        className: 'btn btn-danger btn-sm btn-flat',
                        titleAttr: 'PDF',
                        exportOptions: {
                            columns: ':not(.notexport)'
                        }
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print"></i>',
                        className: 'btn btn-info btn-sm btn-flat',
                        titleAttr: 'Imprimir',
                        exportOptions: {
                            columns: ':not(.notexport)'
                        }
                    },
                    {
                        extend: 'colvis',
                        text: '<i class="fas fa-columns"></i>',
                        className: 'btn btn-dark btn-sm btn-flat',
                        titleAttr: 'Columnas'
                    }
                ],
                initComplete: function() {
                    $('.dataTables_paginate ul.pagination').addClass('pagination-sm');
                }
            });
        });
    </script>
@stop
