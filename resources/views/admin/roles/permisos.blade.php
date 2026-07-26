@extends('adminlte::page')

@section('content_header')
    <h1><b>Permisos para el rol - {{ $rol->name }}</b></h1>
    <hr>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Permisos registrados del sistema</h3>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <form action="{{ route('admin.roles.update_permisos', $rol->id) }}" method="POST">
                        @csrf

                        <div class="row">
                            @foreach ($permisos as $modulo => $grupoPermisos)
                                <div class="col-md-3">
                                    <h4><b>{{ $modulo }}</b></h4>
                                    <hr style="margin-top: 5px; margin-bottom: 10px;">
                                    @foreach ($grupoPermisos as $permiso)
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" name="permisos[]"
                                                value="{{ $permiso->id }}"
                                                {{ $rol->hasPermissionTo($permiso->name) ? 'checked' : '' }}>
                                            <label class="form-check-label">
                                                {{ $permiso->name }}
                                            </label>
                                        </div>
                                    @endforeach
                                    <br>
                                </div>
                            @endforeach
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    @auth
                                        <a href="{{ url('/admin/roles') }}" class="btn btn-default">
                                            <i class="fas fa-arrow-left"></i> Cancelar
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Guardar
                                        </button>
                                    @endauth
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->
        </div>
    </div>
@stop

@section('css')
    {{-- Estilos personalizados si requieres --}}
@stop

@section('js')
@stop
