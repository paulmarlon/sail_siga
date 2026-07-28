@extends('adminlte::page')

@section('title', 'Papelera de Ofertas Académicas')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Papelera de <b>Ofertas Académicas</b></h1>
        <a href="{{ route('admin.oferta-academica.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver al Listado
        </a>
    </div>
@stop

@section('content')
    <!-- Alertas de éxito -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card card-warning card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-trash-alt mr-1"></i> Registros Eliminados (Soft Deletes)</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="tabla-papelera" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Carrera / Pensum</th>
                            <th>Materia</th>
                            <th>Periodo</th>
                            <th>Turno</th>
                            <th>Paralelo</th>
                            <th class="text-center" style="width: 130px">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ofertas as $oferta)
                            <tr>
                                <td>{{ $oferta->id }}</td>
                                <td>{{ $oferta->pensum->carrera->nombre ?? 'N/A' }}</td>
                                <td><strong>{{ $oferta->pensum->materia->nombre ?? 'N/A' }}</strong></td>
                                <td>{{ $oferta->periodo->nombre ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge badge-info">{{ $oferta->turno->nombre ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-secondary">{{ $oferta->paralelo->nombre ?? 'N/A' }}</span>
                                </td>
                                <td class="text-center">
                                    <form action="{{ route('admin.oferta-academica.restaurar', $oferta->id) }}"
                                        method="POST" class="d-inline form-restaurar">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-success btn-sm" title="Restaurar registro">
                                            <i class="fas fa-undo"></i> Restaurar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="mt-3 d-flex justify-content-end">
                {{ $ofertas->links() }}
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            $('#tabla-papelera').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
                },
                "responsive": true,
                "autoWidth": false,
            });

            $('.form-restaurar').submit(function(e) {
                e.preventDefault();
                Swal.fire({
                    title: '¿Deseas restaurar esta oferta?',
                    text: "El registro volverá a estar activo en el sistema.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, restaurar',
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
