@extends('adminlte::page')

@section('title', 'Matriculación de Materias')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fas fa-book-open text-primary mr-2"></i> Gestión de Matriculación de Materias</h1>
            <p class="text-muted mb-0">Control académico de asignación de oferta estudiantil.</p>
        </div>
        <div>
            <a href="{{ route('admin.matriculacion-materias.papelera') }}" class="btn btn-secondary btn-sm mr-2">
                <i class="fas fa-trash-restore mr-1"></i> Papelera
            </a>
            <a href="{{ route('admin.matriculacion-materias.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus-circle mr-1"></i> Nueva Matriculación
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

    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header">
            <h3 class="card-title font-weight-bold">Listado de Materias Matriculadas</h3>
        </div>
        <div class="card-body">
            <!-- Filtros superiores opcionales para contenedores dinámicos -->
            <div class="row mb-3 bg-light p-2 rounded mx-0">
                <div class="col-md-3 mb-2 mb-md-0" id="filtro-periodo-container">
                    <label class="small font-weight-bold text-secondary mb-1">Periodo:</label>
                </div>
                <div class="col-md-3 mb-2 mb-md-0" id="filtro-estado-container">
                    <label class="small font-weight-bold text-secondary mb-1">Estado:</label>
                </div>
            </div>

            <table id="tabla-matriculaciones" class="table table-bordered table-striped table-hover dt-responsive nowrap"
                style="width:100%">
                <thead class="thead-dark">
                    <tr>
                        <th class="notexport" style="width: 10px;">ID</th>
                        <th>Estudiante (CI / Nombre)</th>
                        <th>Materia (Sigla - Nombre)</th>
                        <th>Periodo</th>
                        <th>Turno / Paralelo</th>
                        <th>Estado</th>
                        <th class="notexport" style="text-align: center; width: 100px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($matriculaciones as $mat)
                        <tr>
                            <td>{{ $mat->id }}</td>
                            <td>
                                <span class="font-weight-bold">{{ $mat->estudiante->persona->nombres }}
                                    {{ $mat->estudiante->persona->ap_paterno }}</span><br>
                                <small class="text-muted">CI: {{ $mat->estudiante->persona->ci }} | RU:
                                    {{ $mat->estudiante->registro_universitario }}</small>
                            </td>
                            <td>
                                <span class="badge badge-info">{{ $mat->oferta->pensum->materia->sigla ?? 'N/A' }}</span>
                                {{ $mat->oferta->pensum->materia->nombre ?? 'Sin materia' }}
                            </td>
                            <td>{{ $mat->oferta->periodo->nombre ?? 'N/A' }}</td>
                            <td>
                                <span class="badge badge-secondary">{{ $mat->oferta->turno->nombre ?? 'S/T' }}</span> -
                                <span class="badge badge-light border">Paralelo:
                                    {{ $mat->oferta->paralelo->nombre ?? 'S/P' }}</span>
                            </td>
                            <td>
                                @php
                                    $slugEstado = $mat->estado->slug ?? 'default';
                                    $badgeClass = match ($slugEstado) {
                                        'matriculado', 'activo' => 'badge-success',
                                        'retirado', 'descontinuado' => 'badge-danger',
                                        'congelado' => 'badge-warning',
                                        default => 'badge-secondary',
                                    };
                                @endphp
                                <span
                                    class="badge {{ $badgeClass }} px-2 py-1">{{ $mat->estado->nombre ?? 'Desconocido' }}</span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <!-- Ver detalle -->
                                    <a href="{{ route('admin.matriculacion-materias.show', $mat->id) }}"
                                        class="btn btn-default btn-sm px-2" title="Ver Detalles">
                                        <i class="fas fa-eye text-primary"></i>
                                    </a>
                                    <!-- Editar -->
                                    <a href="{{ route('admin.matriculacion-materias.edit', $mat->id) }}"
                                        class="btn btn-default btn-sm px-2" title="Editar">
                                        <i class="fas fa-edit text-success"></i>
                                    </a>
                                    <!-- Soft Delete (Papelera) -->
                                    <form action="{{ route('admin.matriculacion-materias.destroy', $mat->id) }}"
                                        method="POST" class="d-inline form-eliminar">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-default btn-sm px-2"
                                            title="Enviar a Papelera">
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
        $(function() {
            var table = $('#tabla-matriculaciones').DataTable({
                responsive: true,
                autoWidth: false,
                pageLength: 10,
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
                    var api = this.api();

                    // Mapeo de columnas para filtros rápidos superiores (Columna 3: Periodo, Columna 5: Estado)
                    var filtrosConfig = [{
                            index: 3,
                            container: '#filtro-periodo-container'
                        },
                        {
                            index: 5,
                            container: '#filtro-estado-container'
                        }
                    ];

                    filtrosConfig.forEach(function(item) {
                        var column = api.column(item.index);
                        var select = $(
                                '<select class="form-control form-control-sm"><option value="">-- Todos --</option></select>'
                                )
                            .appendTo($(item.container))
                            .on('change', function() {
                                var val = $(this).val();
                                localStorage.setItem('dt_filter_mat_' + item.index, val);
                                var escapedVal = $.fn.dataTable.util.escapeRegex(val);
                                column.search(escapedVal ? '^' + escapedVal + '$' : '',
                                    true, false).draw();
                            });

                        column.data().unique().sort().each(function(d) {
                            var tempDiv = document.createElement("div");
                            tempDiv.innerHTML = d;
                            var val = tempDiv.textContent || tempDiv.innerText || "";
                            val = val.trim();
                            if (val && !$(select).find("option[value='" + val + "']")
                                .length) {
                                select.append('<option value="' + val + '">' + val +
                                    '</option>');
                            }
                        });

                        var savedVal = localStorage.getItem('dt_filter_mat_' + item.index);
                        if (savedVal) {
                            select.val(savedVal);
                            column.search($.fn.dataTable.util.escapeRegex(savedVal) ? '^' + $.fn
                                .dataTable.util.escapeRegex(savedVal) + '$' : '', true,
                                false).draw();
                        }
                    });
                }
            });

            // SweetAlert para confirmación de Soft Delete
            $('.form-eliminar').submit(function(e) {
                e.preventDefault();
                var form = this;
                Swal.fire({
                    title: '¿Enviar a la papelera?',
                    text: "La matriculación de esta materia será suspendida temporalmente.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, enviar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.value) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@stop
