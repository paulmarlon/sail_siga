@extends('adminlte::page')
@section('plugins.Datatables', true)
@section('plugins.Sweetalert2', true)
@section('plugins.Inputmask', true)
@section('title', 'Papelera Personas')

@section('content')
    <div class="container-fluid pt-3">
        {{-- Encabezado con estilo de alerta --}}
        <div class="row mb-3">
            <div class="col-12">
                <div class="card bg-gradient-dark shadow-sm">
                    <div class="card-body py-2 d-flex align-items-center">
                        <h4 class="m-0 font-weight-bold">
                            <i class="fas fa-trash-restore mr-2"></i> PAPELERA DE PERSONAS
                        </h4>

                        <!-- ml-auto empuja el botón a la extrema derecha -->
                        <a href="{{ route('admin.personas.index') }}" class="btn btn-sm btn-outline-light ml-auto">
                            <i class="fas fa-arrow-left mr-1"></i> VOLVER A LISTA
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabla de registros eliminados --}}
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="papelera-table" class="table table-hover table-striped mb-0" style="font-size: 0.9rem;">
                        <thead class="bg-light text-secondary">
                            <tr>
                                <th>NOMBRE COMPLETO</th>
                                <th>CI</th>
                                <th>FECHA DE ELIMINACIÓN</th>
                                <th class="text-center">ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($personas as $p)
                                <tr>
                                    <td class="align-middle font-weight-bold">
                                        {{ $p->nombres }} {{ $p->ap_paterno }} {{ $p->ap_materno }}
                                    </td>
                                    <td class="align-middle">{{ $p->ci }}</td>
                                    <td class="align-middle">
                                        <span class="text-danger">
                                            <i class="far fa-clock mr-1"></i> {{ $p->deleted_at->format('d/m/Y H:i') }}
                                        </span>
                                    </td>
                                    <td class="align-middle text-center">
                                        {{-- Formulario con ID dinámico --}}
                                        <form action="{{ route('admin.personas.restaurar', $p->id) }}" method="POST"
                                            id="formRestaurar{{ $p->id }}">
                                            @csrf
                                            <button type="button" class="btn btn-xs btn-success shadow-sm"
                                                onclick="confirmarRestauracion({{ $p->id }})">
                                                <i class="fas fa-undo mr-1"></i> RESTAURAR
                                            </button>
                                        </form>
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

@section('css')
    <style>
        .bg-gradient-dark {
            background: linear-gradient(45deg, #343a40, #495057);
            color: white;
        }

        .table td {
            vertical-align: middle !important;
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Inicializar DataTables
            $('#papelera-table').DataTable({
                "responsive": true,
                "autoWidth": false,
                "language": {
                    "url": "https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json"
                },
                "order": [
                    [2, "desc"]
                ]
            });
        });

        // Función de SweetAlert2 para la restauración
        function confirmarRestauracion(id) {
            Swal.fire({
                title: '¿Desea restaurar a esta persona?',
                text: "El registro volverá a estar activo en el sistema.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, restaurar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('formRestaurar' + id).submit();
                }
            });
        }
    </script>

    @include('admin.alertas')
@stop
