@extends('adminlte::page')

@section('title', 'Gestión de Oferta Académica')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center py-1">
        <h1 class="h4 mb-0">Gestión de <b>Oferta Académica</b></h1>
        <div>
            <!-- Botón para la Papelera -->
            <a href="{{ route('admin.oferta-academica.papelera') }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-trash"></i> Papelera
            </a>
            <!-- Botón para Crear Nueva Oferta -->
            <a href="{{ route('admin.oferta-academica.create') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus"></i> Nueva Oferta
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="card card-primary card-outline">
        <div class="card-header py-2">
            <h3 class="card-title font-weight-bold" style="font-size: 0.95rem;"><i class="fas fa-table mr-1"></i> Listado de
                Ofertas Académicas</h3>
        </div>
        <div class="card-body p-2">

            {{-- SECCIÓN DE FILTROS SUPERIORES POR SELECT --}}
            <div class="row mx-0 mb-3 p-2 bg-light rounded border">
                <div class="col-md-4 mb-2 mb-md-0">
                    <label class="small font-weight-bold text-secondary mb-1">Filtrar por Periodo:</label>
                    <div id="filtro-periodo-container"></div>
                </div>
                <div class="col-md-4 mb-2 mb-md-0">
                    <label class="small font-weight-bold text-secondary mb-1">Filtrar por Turno:</label>
                    <div id="filtro-turno-container"></div>
                </div>
                <div class="col-md-4">
                    <label class="small font-weight-bold text-secondary mb-1">Filtrar por Paralelo:</label>
                    <div id="filtro-paralelo-container"></div>
                </div>
            </div>

            <div class="table-responsive">
                <table id="tabla-ofertas" class="table table-bordered table-striped table-sm text-nowrap"
                    style="font-size: 0.85rem;">
                    <thead>
                        <tr class="text-center">
                            <th style="width: 40px;">ID</th>
                            <th>Carrera / Pensum</th>
                            <th>Materia</th>
                            <th>Periodo</th>
                            <th>Turno</th>
                            <th>Paralelo</th>
                            <th style="width: 60px;">Cupo</th>
                            <th style="width: 90px;">Estado</th>
                            <th style="width: 100px">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ofertas as $oferta)
                            <tr>
                                <td class="text-center font-weight-bold">{{ $oferta->id }}</td>
                                <td>{{ $oferta->pensum->materia->sigla ?? 'N/A' }}</td>
                                <td><strong>{{ $oferta->pensum->materia->nombre ?? 'N/A' }}</strong></td>
                                <td class="text-center">
                                    {{ $oferta->periodo->nombre ?? 'N/A' }} - {{ $oferta->periodo->gestion->nombre ?? '' }}
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-info">{{ $oferta->turno->nombre ?? 'N/A' }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-secondary">{{ $oferta->paralelo->nombre ?? 'N/A' }}</span>
                                </td>
                                <td class="text-center">{{ $oferta->cupo_maximo }}</td>
                                <td class="text-center">
                                    <span class="badge badge-success">{{ $oferta->estado->nombre ?? 'N/A' }}</span>
                                </td>
                                <td class="text-center py-1">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.oferta-academica.edit', $oferta) }}"
                                            class="btn btn-default btn-xs px-2" title="Editar">
                                            <i class="fas fa-edit text-success"></i>
                                        </a>
                                        <form action="{{ route('admin.oferta-academica.destroy', $oferta) }}"
                                            method="POST" class="d-inline form-eliminar">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-default btn-xs px-2"
                                                title="Enviar a papelera">
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
    </div>
@endsection
@section('js')
    <script>
        $(document).ready(function() {
            var table = $('#tabla-ofertas').DataTable({
                "responsive": true,
                "lengthChange": true,
                "autoWidth": false,
                "order": [
                    [0, "desc"]
                ],
                "language": {
                    "url": "https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json"
                },
                "dom": '<"row mx-0 border-bottom py-2"<"col-md-5"B><"col-md-3"l><"col-md-4"f>>rt<"row mx-0 pt-2 align-items-center"<"col-md-6"i><"col-md-6 d-flex justify-content-end"p>>',
                "buttons": [{
                        extend: 'copy',
                        text: '<i class="far fa-copy"></i>',
                        className: 'btn btn-secondary btn-sm btn-flat',
                        titleAttr: 'Copiar'
                    },
                    {
                        extend: 'excelHtml5',
                        text: '<i class="far fa-file-excel"></i>',
                        className: 'btn btn-success btn-sm btn-flat',
                        titleAttr: 'Exportar a Excel'
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="far fa-file-pdf"></i>',
                        className: 'btn btn-danger btn-sm btn-flat',
                        titleAttr: 'Exportar a PDF'
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print"></i>',
                        className: 'btn btn-info btn-sm btn-flat',
                        titleAttr: 'Imprimir'
                    },
                    {
                        extend: 'colvis',
                        text: '<i class="fas fa-columns"></i>',
                        className: 'btn btn-dark btn-sm btn-flat',
                        titleAttr: 'Visibilidad de columnas'
                    }
                ],
                "initComplete": function() {
                    // Forzar tamaño compacto de paginación
                    $('.dataTables_paginate ul.pagination').addClass('pagination-sm');

                    // Crear los selectores dinámicos basados en el contenido de las columnas
                    this.api().columns([3, 4, 5]).every(function(index) {
                        var column = this;
                        var containerId = '';

                        if (index === 3) containerId = '#filtro-periodo-container';
                        if (index === 4) containerId = '#filtro-turno-container';
                        if (index === 5) containerId = '#filtro-paralelo-container';

                        var select = $(
                                '<select class="form-control form-control-sm"><option value="">-- Todos --</option></select>'
                            )
                            .appendTo($(containerId))
                            .on('change', function() {
                                var val = $.fn.dataTable.util.escapeRegex($(this).val());
                                // Búsqueda exacta para el periodo y gestión seleccionados
                                column.search(val ? '^' + val + '$' : '', true, false)
                                    .draw();
                            });

                        // Extraer opciones únicas ordenadas
                        var uniqueData = [];
                        column.data().unique().sort().each(function(d) {
                            var val = $('<div>').html(d).text().trim();
                            if (val && !uniqueData.includes(val)) {
                                uniqueData.push(val);
                                select.append('<option value="' + val + '">' + val +
                                    '</option>');
                            }
                        });
                    });
                }
            });

            // Notificación Toastr si existe un mensaje de éxito
            @if (session('success'))
                toastr.success("{{ session('success') }}");
            @endif

            // Confirmación con SweetAlert para enviar a papelera
            $('.form-eliminar').submit(function(e) {
                e.preventDefault();
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "La oferta académica será enviada a la papelera.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, enviar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.submit();
                    }
                })
            });
        });
    </script>
@endsection
