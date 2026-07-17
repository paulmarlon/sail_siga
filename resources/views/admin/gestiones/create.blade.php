@extends('adminlte::page')
@section('content_header')
    <h1><b>Crear nueva gestión académica</b></h1>
    <hr>
@stop

@section('content')
    <div class="row">

        <div class="col-md-4">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Llene los datos del formulario</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.gestiones.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="nombre">Nombre</label>
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-university"></i></span>
                                        </div>
                                        <input type="number" class="form-control" id="nombre" name="nombre"
                                            placeholder="Escriba aquí.." min="2025" max="2050"
                                            value="{{ old('nombre') }}" required>
                                    </div>
                                    @error('nombre')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <a href="{{ route('admin.gestiones.index') }}" class="btn btn-secondary"><i
                                            class="fas fa-arrow-left"></i> Cancelar</a>
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i>
                                        Guardar</button>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
@section('css')
@stop
@section('js')
    <script>
        console.log('Hi!');
    </script>
@stop
