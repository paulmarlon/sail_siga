@extends('adminlte::page')

@section('title', 'Acceso Denegado')

@section('content_header')
    <h1><b>Acceso No Autorizado</b></h1>
    <hr>
@stop

@section('content')
    <div class="error-page" style="margin-top: 50px; text-align: center;">
        <h2 class="headline text-warning" style="font-size: 100px; font-weight: 300;"><i
                class="fas fa-exclamation-triangle text-warning"></i> 403</h2>

        <div class="error-content" style="display: inline-block; max-width: 500px;">
            <h3><i class="fas fa-shield-alt text-danger">Oops! Acceso denegado.</i></h3>

            <p>
                Lo sentimos mucho, pero <b>no cuentas con los permisos necesarios</b> para acceder a este módulo o realizar
                esta acción en el sistema.
            </p>
            <p class="text-muted">
                Si consideras que esto es un error, por favor comunícate con el administrador general para que te asigne los
                accesos correspondientes.
            </p>

            <div class="mt-4">
                <a href="{{ url('/') }}" class="btn btn-primary">
                    <i class="fas fa-home"></i> Ir al Panel Principal
                </a>
                <a href="javascript:history.back()" class="btn btn-default ml-2">
                    <i class="fas fa-arrow-left"></i> Volver atrás
                </a>
            </div>
        </div>
        <!-- /.error-content -->
    </div>
@stop

@section('css')
    <style>
        .error-page {
            padding: 20px 0;
        }

        .error-content h3 {
            margin-top: 10px;
            font-size: 24px;
        }
    </style>
@stop

@section('js')
@stop
