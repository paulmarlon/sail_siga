@extends('adminlte::page')

@section('content_header')
    <h1><b>Listado de roles</b></h1>
    <hr>
@stop

@section('content')

    {{-- Notificación Toast si el controlador manda un mensaje flash --}}
    @if (session('mensaje'))
        <script>
            Swal.fire({
                position: 'top-end',
                icon: "{{ session('icono', 'success') }}",
                title: "{{ session('mensaje') }}",
                showConfirmButton: false,
                timer: 3000,
                toast: true
            });
        </script>
    @endif

    <div class="row">
        <div class="col-md-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">Roles registrados</h3>
                    <div class="card-tools">
                        <a href="{{ url('/admin/roles/create') }}" class="btn btn-primary">Crear nuevo rol</a>
                    </div>
                </div>
                <div class="card-body">
                    <table id="example" class="table table-bordered table-striped table-hover table-sm">
                        <thead>
                            <tr>
                                <th style="width: 10px">Nro</th>
                                <th>Nombre del rol</th>
                                <th style="width: 250px">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($roles as $role)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $role->name }}</td>
                                    <td>
                                        <div class="row d-flex justify-content-center">
                                            <a href="{{ url('/admin/roles/' . $role->id . '/permisos') }}"
                                                class="btn btn-warning btn-sm mx-1"><i class="fas fa-check"></i>
                                                Permisos</a>

                                            <a href="{{ url('/admin/roles/' . $role->id . '/edit') }}"
                                                class="btn btn-success btn-sm mx-1"><i class="fas fa-pencil-alt"></i>
                                                Editar</a>

                                            <form action="{{ url('/admin/roles/' . $role->id) }}" method="POST"
                                                id="miFormulario{{ $role->id }}" class="mx-1">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-danger btn-sm"
                                                    onclick="preguntar{{ $role->id }}(event)">
                                                    <i class="fas fa-trash"></i> Eliminar
                                                </button>
                                            </form>
                                        </div>

                                        <script>
                                            function preguntar{{ $role->id }}(event) {
                                                event.preventDefault();
                                                Swal.fire({
                                                    title: '¿Desea eliminar este registro?',
                                                    text: '',
                                                    icon: 'question',
                                                    showDenyButton: true,
                                                    confirmButtonText: 'Eliminar',
                                                    confirmButtonColor: '#a5161d',
                                                    denyButtonColor: '#270a0a',
                                                    denyButtonText: 'Cancelar',
                                                }).then((result) => {
                                                    if (result.isConfirmed) {
                                                        document.getElementById('miFormulario{{ $role->id }}').submit();
                                                    }
                                                });
                                            }
                                        </script>
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
    {{-- Estilos personalizados si requieres --}}
@stop

@section('js')
    <script>
        $(function() {
            $("#example").DataTable({
                "responsive": true,
                "lengthChange": true,
                "autoWidth": false,
                "language": {
                    "emptyTable": "No hay información",
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ Roles",
                    "infoEmpty": "Mostrando 0 to 0 of 0 Roles",
                    "infoFiltered": "(Filtrado de _MAX_ total Roles)",
                    "lengthMenu": "Mostrar _MENU_ Roles",
                    "loadingRecords": "Cargando...",
                    "processing": "Procesando...",
                    "search": "Buscador:",
                    "zeroRecords": "Sin resultados encontrados",
                    "paginate": {
                        "first": "Primero",
                        "last": "Último",
                        "next": "Siguiente",
                        "previous": "Anterior"
                    }
                }
            });
        });
    </script>
    @include('admin.alertas')
@stop
