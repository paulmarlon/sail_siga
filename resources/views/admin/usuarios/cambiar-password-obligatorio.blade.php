@extends('adminlte::page')

@section('title', 'Cambio Obligatorio de Contraseña')

@section('content_header')
    <div class="container-fluid">
        <h1>Cambio Obligatorio de Contraseña</h1>
    </div>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6 offset-md-3">

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Seguridad de la cuenta</h3>
                    </div>

                    <form method="POST" action="{{ route('admin.password.change.update') }}">
                        @csrf

                        <div class="card-body">
                            <div class="callout callout-warning">
                                <p class="mb-0">Por seguridad, debes cambiar tu contraseña temporal antes de continuar
                                    utilizando el sistema.</p>
                            </div>

                            <!-- Contraseña Actual / Temporal -->
                            <div class="form-group">
                                <label for="current_password">Contraseña Temporal / Actual</label>
                                <input type="password" name="current_password" id="current_password"
                                    class="form-control @error('current_password') is-invalid @enderror"
                                    placeholder="Ingrese su contraseña actual" required autocomplete="current-password">
                                @error('current_password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <!-- Nueva Contraseña -->
                            <div class="form-group">
                                <label for="password">Nueva Contraseña</label>
                                <input type="password" name="password" id="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    placeholder="Mínimo 8 caracteres" required autocomplete="new-password">
                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <!-- Confirmar Contraseña -->
                            <div class="form-group">
                                <label for="password_confirmation">Confirmar Nueva Contraseña</label>
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                    class="form-control" placeholder="Repita la nueva contraseña" required
                                    autocomplete="new-password">
                            </div>
                        </div>

                        <div class="card-footer text-right">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i> Guardar Nueva Contraseña
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
