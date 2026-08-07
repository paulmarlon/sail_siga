@extends('adminlte::page')

@section('title', 'Inscripción a Carreras')

@section('plugins.Datatables', true)
@section('plugins.Sweetalert2', true)

@section('css')
    <style>
        #tabla-inscripciones th,
        #tabla-inscripciones td {
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
                <h1><i class="fas fa-fw fa-file-signature mr-2"></i> Inscripción a Carreras</h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('admin.inscripcion-carreras.papelera') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-trash-restore mr-1"></i> Papelera
                </a>
                <a href="{{ route('admin.inscripcion-carreras.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus mr-1"></i> Nueva Inscripción
                </a>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        @include('admin.alertas')
        <div class="card card-outline card-primary shadow-sm">
            <div class="card-header py-2">
                <h3 class="card-title font-weight-bold" style="font-size: 0.95rem;">Listado de Estudiantes Inscritos por
                    Periodo</h3>
            </div>
            <div class="card-body p-2">

                {{-- SECCIÓN DE FILTROS SUPERIORES POR SELECT --}}
                <div class="row mx-0 mb-3 p-2 bg-light rounded border">
                    <div class="col-md-3 mb-2 mb-md-0">
                        <label class="small font-weight-bold text-secondary mb-1">Filtrar por Carrera:</label>
                        <div id="filtro-carrera-container"></div>
                    </div>
                    <div class="col-md-3 mb-2 mb-md-0">
                        <label class="small font-weight-bold text-secondary mb-1">Filtrar por Periodo:</label>
                        <div id="filtro-periodo-container"></div>
                    </div>
                    <div class="col-md-3 mb-2 mb-md-0">
                        <label class="small font-weight-bold text-secondary mb-1">Filtrar por Especialidad:</label>
                        <div id="filtro-especialidad-container"></div>
                    </div>
                    <div class="col-md-3">
                        <label class="small font-weight-bold text-secondary mb-1">Filtrar por Estado:</label>
                        <div id="filtro-estado-container"></div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="tabla-inscripciones" class="table table-bordered table-striped table-sm text-nowrap"
                        style="width:100%; font-size: 0.85rem;">
                        <thead>
                            <tr class="text-center">
                                <th style="width: 40px;">ID</th> <!-- Columna 0 -->
                                <th>Estudiante (CI / Nombre)</th> <!-- Columna 1 -->
                                <th>Carrera</th> <!-- Columna 2 -->
                                <th>Periodo</th> <!-- Columna 3 -->
                                <th>Fecha</th> <!-- Columna 4 -->
                                <th>Especialidad</th> <!-- Columna 5 -->
                                <th>Estado</th> <!-- Columna 6 -->
                                <th class="text-center notexport" style="width: 100px;">Acciones</th> <!-- Columna 7 -->
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($inscripciones as $inscripcion)
                                <tr>
                                    <td class="text-center font-weight-bold">{{ $inscripcion->id }}</td>
                                    <td>
                                        <strong>{{ $inscripcion->estudiante->persona->ap_paterno }}
                                            {{ $inscripcion->estudiante->persona->ap_materno }}
                                            {{ $inscripcion->estudiante->persona->nombres }}</strong>
                                        <br>
                                        <small class="text-muted">CI: {{ $inscripcion->estudiante->persona->ci }} | Reg:
                                            {{ $inscripcion->estudiante->registro_universitario }}</small>
                                    </td>
                                    <td>
                                        {{ $inscripcion->carrera->nombre }}
                                        <br>
                                        <small class="text-muted">Sigla: {{ $inscripcion->carrera->sigla }}</small>
                                    </td>
                                    <td class="text-center"><span
                                            class="badge badge-info">{{ $inscripcion->periodo->nombre }}</span></td>
                                    <td class="text-center">
                                        {{ $inscripcion->fecha_inscripcion ? $inscripcion->fecha_inscripcion->format('d/m/Y') : '-' }}
                                    </td>
                                    <td class="text-center">
                                        @if ($inscripcion->es_especialidad_activa)
                                            <span class="badge badge-success">Especialidad Activa</span>
                                        @else
                                            <span class="badge badge-secondary">Tronco Común</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge"
                                            style="background-color: {{ $inscripcion->estado->color_hex ?? '#6c757d' }}; color: #fff;">
                                            {{ $inscripcion->estado->nombre ?? 'N/D' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            {{-- Ver Detalle --}}
                                            <a href="{{ route('admin.inscripcion-carreras.show', $inscripcion->id) }}"
                                                class="btn btn-default btn-xs px-2" title="Ver Detalle">
                                                <i class="fas fa-eye text-info"></i>
                                            </a>

                                            {{-- Editar --}}
                                            <a href="{{ route('admin.inscripcion-carreras.edit', $inscripcion->id) }}"
                                                class="btn btn-default btn-xs px-2" title="Editar">
                                                <i class="fas fa-edit text-success"></i>
                                            </a>

                                            {{-- Botón para Dar de Baja (Retiro) --}}
                                            <form
                                                action="{{ route('admin.inscripcion-carreras.procesar-retiro', $inscripcion->id) }}"
                                                method="POST" class="d-inline form-retiro">
                                                @csrf
                                                @method('PUT')
                                                <button type="button" class="btn btn-default btn-sm px-2 btn-dar-baja"
                                                    title="Dar de baja / Retirar" data-id="{{ $inscripcion->id }}">
                                                    <i class="fas fa-ban text-warning"></i>
                                                </button>
                                            </form>

                                            {{-- Botón para Eliminar Lógicamente --}}
                                            <button type="button"
                                                class="btn btn-default btn-sm px-2 btn-borrar-inscripcion"
                                                data-id="{{ $inscripcion->id }}"
                                                title="Enviar a papelera (Eliminación lógica)">
                                                <i class="fas fa-trash text-danger"></i>
                                            </button>
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

    {{-- Formulario oculto global para la eliminación lógica --}}
    <form id="form-eliminar-dinamico" action="" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
@stop

@section('js')
    <script>
        $(function() {
            var table = $('#tabla-inscripciones').DataTable({
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

                    // Índices reales basados en el orden de las columnas de la tabla visible:
                    // 2 = Carrera, 3 = Periodo, 5 = Especialidad, 6 = Estado
                    var filtrosConfig = [{
                            index: 2,
                            container: '#filtro-carrera-container'
                        },
                        {
                            index: 3,
                            container: '#filtro-periodo-container'
                        },
                        {
                            index: 5,
                            container: '#filtro-especialidad-container'
                        },
                        {
                            index: 6,
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
                                localStorage.setItem('dt_filter_insc_' + item.index, val);

                                // Se aplica expresión regular exacta para evitar falsos positivos en textos similares
                                column.search(val ? '^' + $.fn.dataTable.util.escapeRegex(
                                    val) + '$' : '', true, false).draw();
                            });

                        var uniqueData = [];
                        column.data().unique().sort().each(function(d) {
                            var tempDiv = document.createElement("div");
                            tempDiv.innerHTML = d;
                            // Tomar solo la primera línea de texto limpia (ignora las siglas o subtítulos bajo el nombre)
                            var lines = (tempDiv.textContent || tempDiv.innerText || "")
                                .trim().split('\n');
                            var val = lines[0].trim();

                            if (val && !uniqueData.includes(val)) {
                                uniqueData.push(val);
                                select.append('<option value="' + val + '">' + val +
                                    '</option>');
                            }
                        });

                        var savedVal = localStorage.getItem('dt_filter_insc_' + item.index);
                        if (savedVal) {
                            select.val(savedVal);
                            column.search(savedVal ? '^' + $.fn.dataTable.util.escapeRegex(
                                savedVal) + '$' : '', true, false).draw();
                        }
                    });
                }
            });

            // Manejador interactivo de SweetAlert para el retiro
            $(document).on('click', '.btn-dar-baja', function(e) {
                e.preventDefault();
                var form = $(this).closest('form');

                Swal.fire({
                    title: 'Seleccione el Motivo de Baja',
                    text: "Esta acción cambiará el estado institucional del estudiante.",
                    icon: 'warning',
                    input: 'select',
                    inputOptions: {
                        '4': 'Descontinuada',
                        '5': 'Retiro Voluntario',
                        '6': 'Baja por Insuficiencia',
                        '7': 'Baja Disciplinaria',
                        '8': 'Baja por Salud'
                    },
                    inputPlaceholder: '-- Seleccione un motivo --',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, procesar baja',
                    cancelButtonText: 'Cancelar',
                    inputValidator: (value) => {
                        if (!value) {
                            return '¡Debes seleccionar un motivo obligatorio!';
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Limpiamos el filtro de estado guardado para evitar incongruencias visuales al actualizar
                        localStorage.removeItem('dt_filter_insc_6');

                        $('<input>').attr({
                            type: 'hidden',
                            name: 'estado_id',
                            value: result.value
                        }).appendTo(form);

                        form.submit();
                    }
                });
            });

            // Manejador seguro para la eliminación lógica (envío a papelera)
            $(document).on('click', '.btn-borrar-inscripcion', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                var url = "{{ route('admin.inscripcion-carreras.destroy', ':id') }}";
                url = url.replace(':id', id);

                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "La inscripción se enviará a la papelera manteniendo el historial.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, enviar a papelera',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        var form = $('#form-eliminar-dinamico');
                        form.attr('action', url);
                        form.submit();
                    }
                });
            });
        });
    </script>
@stop
