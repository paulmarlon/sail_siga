@extends('adminlte::page')
@section('content_header')
    <h1><b>Listado de gestiones académicas</b></h1>
    <hr>
    <a href="{{ route('admin.gestiones.create') }}" class="btn btn-primary">Crear nueva gestión</a>
    <a href="{{ route('admin.gestiones.index') }}" class="btn btn-default">Ver activos</a>
    <a href="{{ route('admin.gestiones.papelera') }}" class="btn btn-warning">Ver papelera</a>
    <hr>
@stop

@section('content')
    <div class="row">
        @foreach ($gestiones as $gestion)
            <div class="col-md-3 col-sm-6 col-12">
                <div class="info-box zoomP">
                    <img src="{{ url('img/calendario.png') }}" width="80px" alt="">
                    <div class="info-box-content">
                        <span class="info-box-text">Gestión</span>
                        <span class="info-box-number"
                            style="color:rgb(0, 149, 255);font-size:20px">{{ $gestion->nombre }}</span>
                        @if ($gestion->estado)
                            <span class="badge" style="background-color: {{ $gestion->estado->color_hex }}; color: #fff;">
                                {{ $gestion->estado->nombre }}
                            </span>
                        @else
                            <span class="badge badge-secondary">Sin estado</span>
                        @endif
                        <div class="row">
                            @if ($gestion->trashed())
                                <div class="col-12 mt-1">
                                    <form action="{{ route('admin.gestiones.restaurar', $gestion->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-info btn-block">
                                            <i class="fas fa-trash-restore"></i> Restaurar
                                        </button>
                                    </form>
                                </div>
                            @else
                                <div class="col-12 mt-1 d-flex justify-content-start">
                                    <a href="{{ route('admin.gestiones.edit', $gestion->id) }}"
                                        class="btn btn-sm btn-success mr-1">
                                        <i class="fas fa-pencil-alt"></i>
                                    </a>
                                    <form action="{{ route('admin.gestiones.destroy', $gestion->id) }}" method="POST"
                                        id="miFormulario{{ $gestion->id }}">
                                        @csrf @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-danger"
                                            onclick="confirmarEliminacion({{ $gestion->id }})">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@stop

@section('css')
@stop

@section('js')
    <script>
        function confirmarEliminacion(id) {
            Swal.fire({
                title: '¿Está seguro?',
                text: "Esta gestión pasará a la papelera.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, enviar a papelera'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('miFormulario' + id).submit();
                }
            });
        }
    </script>
    @include('admin.alertas')
@stop
