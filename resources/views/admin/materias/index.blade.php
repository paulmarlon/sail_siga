@extends('adminlte::page')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1><b>Listado de Materias</b></h1>
        <div class="card-tools">

            {{-- Verifica que el nombre de la ruta coincida con tu archivo web.php --}}
            <a href="{{ route('admin.materias.index') }}" class="btn btn-success btn-sm">Ver Activos</a>
            <a href="{{ route('admin.materias.papelera') }}" class="btn btn-danger btn-sm">Ver Papelera</a>
            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#ModalCreate">
                <i class="fas fa-plus"></i> Nueva Materia
            </button>
        </div>
    </div>
@stop

@section('content')
    <div class="card shadow-sm border-0">
        <div class="card-body p-2">
            <table id="materias-table" class="table table-bordered table-striped table-compact">
                <thead>
                    <tr class="text-secondary text-uppercase">
                        <th>Sigla</th>
                        <th>Nombre</th>
                        <th>Horas</th>
                        <th>Tipo</th>
                        <th>Común</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($materias as $m)
                        <tr>
                            <td class="font-weight-bold">{{ $m->sigla }}</td>
                            <td>{{ $m->nombre }}</td>
                            <td>{{ $m->horas_academicas }}</td>
                            <td>{{ $m->tipo_materia }}</td>
                            <td>
                                @if ($m->es_comun)
                                    <span class="badge badge-success">SÍ</span>
                                @else
                                    <span class="badge badge-secondary">NO</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge"
                                    style="background-color: {{ $m->estado->color_hex ?? '#ccc' }}; color: #fff;">
                                    {{ $m->estado->nombre ?? 'N/A' }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    @if ($m->trashed())
                                        {{-- Botón para restaurar --}}
                                        <form action="{{ route('admin.materias.restaurar', $m->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-xs btn-info" title="Restaurar">
                                                <i class="fas fa-trash-restore"></i>
                                            </button>
                                        </form>
                                    @else
                                        {{-- Botones de edición y eliminar --}}
                                        <button class="btn btn-xs btn-success" data-toggle="modal"
                                            data-target="#ModalUpdate{{ $m->id }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="{{ route('admin.materias.destroy', $m->id) }}" method="POST"
                                            class="ml-1">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-danger"
                                                onclick="return confirm('¿Enviar a papelera?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        {{-- Incluimos el modal solo si no está en papelera --}}
                                        @include('admin.materias.modal_edit', ['materia' => $m])
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL CREATE (Simplificado y compacto) --}}
    @include('admin.materias.modal_create')
@stop

@section('css')
    <style>
        /* 1. Definición general de tabla compacta */
        .table-compact td,
        .table-compact th {
            padding: 0.3rem !important;
            vertical-align: middle !important;
            font-size: 0.85rem;
        }

        /* 2. Cabeceras consistentes */
        .table-compact thead th {
            background-color: #f4f6f9;
            font-size: 0.75rem;
            text-transform: uppercase;
        }

        /* 3. Botones y elementos de acción */
        .btn-xs {
            padding: 0.15rem 0.4rem !important;
            font-size: 0.75rem !important;
        }

        .card-body {
            padding: 0.75rem !important;
        }

        /* 4. DataTables Buttons (Configuración escalable) */
        .dt-buttons .btn {
            padding: 0.25rem 0.5rem !important;
            font-size: 0.75rem !important;
            line-height: 1 !important;
        }

        .dt-buttons {
            display: flex;
            gap: 3px;
        }
    </style>
@stop

@section('js')
    @include('admin.alertas')

    <script>
        $(document).ready(function() {
            // 1. Configuración optimizada de DataTables
            $('#materias-table').DataTable({
                "responsive": true,
                "autoWidth": false,
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json"
                },
                "dom": '<"row mx-0 border-bottom py-2"<"col-sm-5"B><"col-sm-3"l><"col-sm-4"f>>rt<"row mx-0 pt-2"<"col-sm-6"i><"col-sm-6"p>>',
                "buttons": [{
                        extend: 'copy',
                        className: 'btn btn-secondary btn-flat'
                    },
                    {
                        extend: 'excel',
                        className: 'btn btn-success btn-flat'
                    },
                    {
                        extend: 'pdf',
                        className: 'btn btn-danger btn-flat'
                    },
                    {
                        extend: 'print',
                        className: 'btn btn-info btn-flat'
                    }
                ]
            });

            // 2. Lógica inteligente de modales (Fix para edición vs creación)
            @if ($errors->any())
                @if (session('modal_id'))
                    // Si el controlador guardó el ID de la materia que falló al editar
                    $('#ModalUpdate{{ session('modal_id') }}').modal('show');
                @else
                    // Si falló el registro inicial
                    $('#ModalCreate').modal('show');
                @endif
            @endif
        });
    </script>
@stop
