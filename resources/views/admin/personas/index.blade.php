@extends('adminlte::page')
@section('plugins.Datatables', true)
@section('plugins.Sweetalert2', true)
@section('plugins.Inputmask', true)
@section('title', 'Lista de Personas')

@section('content')
    <div class="container-fluid pt-3">
        {{-- Barra de acción --}}
        <div class="row mb-3">
            <div class="col-12 text-right">
                {{-- NUEVO BOTÓN PARA PAPELERA --}}
                <a href="{{ route('admin.personas.papelera') }}" class="btn btn-secondary shadow-sm">
                    <i class="fas fa-trash-restore mr-1"></i> PAPELERA
                </a>
                <a href="{{ route('admin.personas.create') }}" class="btn btn-primary shadow-sm">
                    <i class="fas fa-plus mr-1"></i> REGISTRAR NUEVA PERSONA
                </a>
            </div>
        </div>

        {{-- Tabla principal --}}
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title font-weight-bold">LISTA DE PERSONAS</h3>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="personas-table" class="table table-sm table-hover table-striped text-nowrap"
                        style="font-size: 0.85rem;">
                        <thead>
                            <tr class="text-secondary text-uppercase">
                                <th>FOTO</th>
                                <th>CI</th>
                                <th>NOMBRE COMPLETO</th>
                                <th>CEL</th>
                                <th>DOMICILIO</th>
                                <th>ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($personas as $p)
                                <tr>
                                    <td class="align-middle">
                                        <img src="{{ $p->foto_path ? asset('storage/' . $p->foto_path) : asset('vendor/adminlte/dist/img/avatar.png') }}"
                                            class="img-circle" style="width: 30px; height: 30px; object-fit: cover;">
                                    </td>
                                    <td class="align-middle font-weight-bold">{{ $p->ci }}</td>
                                    <td class="align-middle">{{ $p->nombres }} {{ $p->ap_paterno }} {{ $p->ap_materno }}
                                    </td>
                                    <td class="align-middle">{{ $p->celular ?? '-' }}</td>
                                    <td class="align-middle">
                                        @if ($p->domicilio)
                                            <small class="text-muted text-truncate"
                                                style="max-width: 150px; display: block;">
                                                {{ $p->domicilio->ciudad }}, {{ $p->domicilio->avenida }}
                                            </small>
                                        @else
                                            <span class="badge badge-warning" style="font-size: 0.7rem;">Sin
                                                domicilio</span>
                                        @endif
                                    </td>
                                    <td class="align-middle">
                                        <div class="btn-group">
                                            <a href="#" class="btn btn-xs btn-info"><i class="fas fa-eye"></i></a>
                                            <a href="{{ route('admin.personas.edit', $p->id) }}"
                                                class="btn btn-xs btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.personas.destroy', $p->id) }}" method="POST"
                                                style="display:inline;"
                                                onsubmit="return confirm('¿Está seguro de enviar a la papelera?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i>
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
            <div class="card-footer">

            </div>
        </div>
    </div>
@stop


@section('css')
    <style>
        /* Compactar tabla */
        .table-sm td,
        .table-sm th {
            padding: 0.3rem !important;
        }

        /* Ajustar tamaño de botones y texto */
        .btn-xs {
            padding: 0.1rem 0.4rem !important;
            font-size: 0.75rem !important;
        }

        /* Mejorar la cabecera */
        #personas-table thead th {
            font-size: 0.75rem;
            background-color: #f4f6f9;
        }

        /* Reducir márgenes de la tarjeta */
        .card-body {
            padding: 0.5rem !important;
        }

        /* Compactar los botones de DataTables */
        .dt-buttons .btn {
            padding: 0.2rem 0.5rem !important;
            /* Menos relleno */
            font-size: 0.7rem !important;
            /* Letra más pequeña */
            line-height: 1 !important;
            /* Ajustar altura de línea */
        }

        /* Opcional: reducir margen entre botones */
        .dt-buttons {
            gap: 2px;
        }
    </style>
@stop
{{-- Inclusión de alertas --}}
@section('js')
    @include('admin.alertas')
    <script>
        $(document).ready(function() {
            $('#personas-table').DataTable({
                "responsive": true,
                "lengthChange": true,
                "autoWidth": false,
                "order": [
                    [1, "desc"]
                ],
                "language": {
                    "url": "https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json"
                },
                // Esta configuración define la estructura de botones con colores
                "dom": '<"row mx-0 border-bottom py-2"<"col-sm-5"B><"col-sm-3"l><"col-sm-4"f>>rt<"row mx-0 pt-2"<"col-sm-6"i><"col-sm-6"p>>',
                "buttons": [{
                        extend: 'copy',
                        text: '<i class="far fa-copy"></i> COPIAR',
                        className: 'btn btn-secondary btn-flat'
                    },
                    {
                        extend: 'excelHtml5',
                        text: '<i class="far fa-file-excel"></i> EXCEL',
                        className: 'btn btn-success btn-flat'
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="far fa-file-pdf"></i> PDF',
                        className: 'btn btn-danger btn-flat'
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print"></i> IMPRIMIR',
                        className: 'btn btn-info btn-flat'
                    },
                    {
                        extend: 'colvis',
                        text: '<i class="fas fa-columns"></i>',
                        className: 'btn btn-dark btn-flat'
                    }
                ]
            });
        });
    </script>
@stop
